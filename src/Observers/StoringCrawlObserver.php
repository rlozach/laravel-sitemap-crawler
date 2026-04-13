<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Observers;

use Carbon\Carbon;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Models\CrawlResult;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Symfony\Component\DomCrawler\Crawler;

class StoringCrawlObserver extends CrawlObserver
{
    /** @var array<Extractor> */
    private array $extractors;

    private ?string $sitemapUrl;

    /**
     * @param  array<Extractor>  $extractors
     */
    public function __construct(array $extractors = [], ?string $sitemapUrl = null)
    {
        $this->extractors = $extractors;
        $this->sitemapUrl = $sitemapUrl;
    }

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        $html = (string) $response->getBody();
        $dom = new Crawler($html, (string) $url);

        $extracted = [];
        foreach ($this->extractors as $extractor) {
            $extracted = array_merge($extracted, $extractor->extract($dom, (string) $url));
        }

        CrawlResult::create([
            'url' => (string) $url,
            'sitemap_url' => $this->sitemapUrl,
            'status_code' => $response->getStatusCode(),
            'extracted_data' => $extracted ?: null,
            'crawled_at' => Carbon::now(),
        ]);
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null,
        ?string $linkText = null,
    ): void {
        CrawlResult::create([
            'url' => (string) $url,
            'sitemap_url' => $this->sitemapUrl,
            'status_code' => $requestException->getResponse()?->getStatusCode() ?? 0,
            'extracted_data' => null,
            'crawled_at' => Carbon::now(),
        ]);
    }
}
