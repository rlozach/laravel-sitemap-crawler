<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Parsers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DomCrawler\Crawler;

class SitemapParser
{
    private Client $client;

    /** @var array<string> */
    private array $visited = [];

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client([
            'timeout' => 30,
            'headers' => ['User-Agent' => 'SitemapCrawler/1.0'],
        ]);
    }

    /**
     * Parse one or more sitemap URLs and return all page URLs found.
     *
     * @param  array<string>  $sitemapUrls
     * @return array<string>
     */
    public function parse(array $sitemapUrls): array
    {
        $urls = [];

        foreach ($sitemapUrls as $sitemapUrl) {
            $urls = array_merge($urls, $this->parseSitemap($sitemapUrl));
        }

        return array_values(array_unique($urls));
    }

    /**
     * @return array<string>
     */
    private function parseSitemap(string $url): array
    {
        if (in_array($url, $this->visited, strict: true)) {
            return [];
        }

        $this->visited[] = $url;

        try {
            $response = $this->client->get($url);
            $xml = (string) $response->getBody();
        } catch (RequestException) {
            return [];
        }

        return $this->extractUrlsFromXml($xml);
    }

    /**
     * @return array<string>
     */
    private function extractUrlsFromXml(string $xml): array
    {
        $dom = new Crawler($xml);
        $urls = [];

        // Sitemap index — recurse into child sitemaps
        $sitemapNodes = $dom->filterXPath('//*[local-name()="sitemap"]/*[local-name()="loc"]');
        if ($sitemapNodes->count() > 0) {
            $sitemapNodes->each(function (Crawler $node) use (&$urls): void {
                $childUrl = trim($node->text());
                if ($this->isValidUrl($childUrl)) {
                    $urls = array_merge($urls, $this->parseSitemap($childUrl));
                }
            });

            return $urls;
        }

        // Regular sitemap — collect <loc> page URLs
        $dom->filterXPath('//*[local-name()="url"]/*[local-name()="loc"]')->each(function (Crawler $node) use (&$urls): void {
            $pageUrl = trim($node->text());
            if ($this->isValidUrl($pageUrl)) {
                $urls[] = $pageUrl;
            }
        });

        return $urls;
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], strict: true);
    }
}
