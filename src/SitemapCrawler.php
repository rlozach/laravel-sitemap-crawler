<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Collection;
use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Models\CrawlResult;
use Roloza\SitemapCrawler\Parsers\SitemapParser;
use Symfony\Component\DomCrawler\Crawler;

class SitemapCrawler
{
    /** @var array<string> */
    private array $sitemapUrls = [];

    /** @var array<Extractor> */
    private array $extractors = [];

    private ?string $urlPattern = null;

    private int $concurrency;

    private int $timeout;

    private string $userAgent;

    public function __construct()
    {
        $this->concurrency = (int) config('sitemap-crawler.concurrency', 5);
        $this->timeout = (int) config('sitemap-crawler.timeout', 30);
        $this->userAgent = (string) config('sitemap-crawler.user_agent', 'SitemapCrawler/1.0');

        foreach ((array) config('sitemap-crawler.extractors', []) as $extractorClass) {
            if (is_string($extractorClass) && class_exists($extractorClass)) {
                $this->extractors[] = app($extractorClass);
            }
        }
    }

    /**
     * Set the sitemap URLs to crawl.
     *
     * @param  array<string>  $urls
     */
    public function fromSitemaps(array $urls): static
    {
        $this->sitemapUrls = $urls;

        return $this;
    }

    /**
     * Override the extractors for this run.
     *
     * @param  array<Extractor>  $extractors
     */
    public function withExtractors(array $extractors): static
    {
        $this->extractors = $extractors;

        return $this;
    }

    public function concurrency(int $concurrency): static
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Only crawl URLs whose path contains the given pattern.
     */
    public function filterUrls(string $pattern): static
    {
        $this->urlPattern = $pattern;

        return $this;
    }

    /**
     * Parse sitemaps, crawl each URL concurrently and store results.
     * URLs crawled within the recrawl_after_days window are re-extracted
     * from their stored HTML without making a new HTTP request.
     *
     * @return Collection<int, CrawlResult>
     */
    public function crawl(): Collection
    {
        $parser = new SitemapParser();
        $pageUrls = $parser->parse($this->sitemapUrls);

        if ($this->urlPattern !== null) {
            $pageUrls = array_values(
                array_filter($pageUrls, fn (string $url) => str_contains($url, $this->urlPattern))
            );
        }

        if (empty($pageUrls)) {
            return collect();
        }

        $sitemapUrl = count($this->sitemapUrls) === 1 ? $this->sitemapUrls[0] : null;
        $results = [];

        [$urlsToFetch, $results] = $this->resolveFromCache($pageUrls, $results);

        if (! empty($urlsToFetch)) {
            $results = $this->fetchUrls($urlsToFetch, $sitemapUrl, $results);
        }

        return collect($results);
    }

    /**
     * Re-extract data from cached HTML for fresh records.
     * Returns URLs that still need to be fetched.
     *
     * @param  array<string>  $pageUrls
     * @param  array<CrawlResult>  $results
     * @return array{array<string>, array<CrawlResult>}
     */
    private function resolveFromCache(array $pageUrls, array $results): array
    {
        $recrawlAfterDays = (int) config('sitemap-crawler.recrawl_after_days', 7);

        if ($recrawlAfterDays === 0) {
            return [$pageUrls, $results];
        }

        $cutoff = Carbon::now()->subDays($recrawlAfterDays);

        $freshRecords = CrawlResult::whereIn('url', $pageUrls)
            ->where('crawled_at', '>=', $cutoff)
            ->whereNotNull('html')
            ->get()
            ->keyBy('url');

        $urlsToFetch = [];

        foreach ($pageUrls as $url) {
            $record = $freshRecords->get($url);

            if ($record === null) {
                $urlsToFetch[] = $url;
                continue;
            }

            $dom = new Crawler($record->html, $url);
            $extracted = [];
            foreach ($this->extractors as $extractor) {
                $extracted = array_merge($extracted, $extractor->extract($dom, $url));
            }

            $record->update(['extracted_data' => $extracted ?: null]);
            $results[] = $record->fresh();
        }

        return [$urlsToFetch, $results];
    }

    /**
     * Fetch URLs via concurrent HTTP requests and store results.
     *
     * @param  array<string>  $urls
     * @param  array<CrawlResult>  $results
     * @return array<CrawlResult>
     */
    private function fetchUrls(array $urls, ?string $sitemapUrl, array $results): array
    {
        $client = new Client([
            'timeout' => $this->timeout,
            'headers' => ['User-Agent' => $this->userAgent],
            'allow_redirects' => true,
        ]);

        $requests = function (array $list) {
            foreach ($list as $url) {
                yield new Request('GET', $url);
            }
        };

        $pool = new Pool($client, $requests($urls), [
            'concurrency' => $this->concurrency,
            'fulfilled' => function ($response, int $index) use ($urls, $sitemapUrl, &$results): void {
                $url = $urls[$index];
                $body = $response->getBody();
                $body->rewind();
                $html = (string) $body;
                $dom = new Crawler($html, $url);

                $extracted = [];
                foreach ($this->extractors as $extractor) {
                    $extracted = array_merge($extracted, $extractor->extract($dom, $url));
                }

                $results[] = CrawlResult::updateOrCreate(
                    ['url' => $url],
                    [
                        'sitemap_url' => $sitemapUrl,
                        'status_code' => $response->getStatusCode(),
                        'extracted_data' => $extracted ?: null,
                        'html' => $html,
                        'crawled_at' => Carbon::now(),
                    ],
                );
            },
            'rejected' => function (RequestException $reason, int $index) use ($urls, $sitemapUrl, &$results): void {
                $url = $urls[$index];

                $results[] = CrawlResult::updateOrCreate(
                    ['url' => $url],
                    [
                        'sitemap_url' => $sitemapUrl,
                        'status_code' => $reason->getResponse()?->getStatusCode() ?? 0,
                        'extracted_data' => null,
                        'crawled_at' => Carbon::now(),
                    ],
                );
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }
}
