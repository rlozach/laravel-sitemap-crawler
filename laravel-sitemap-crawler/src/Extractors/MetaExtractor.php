<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Symfony\Component\DomCrawler\Crawler;

class MetaExtractor implements Extractor
{
    /**
     * Extract title, meta description and first H1 from the page DOM.
     *
     * @return array<string, mixed>
     */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'title' => $dom->filter('title')->count()
                ? trim($dom->filter('title')->text())
                : null,

            'description' => $dom->filter('meta[name="description"]')->count()
                ? $dom->filter('meta[name="description"]')->attr('content')
                : null,

            'h1' => $dom->filter('h1')->count()
                ? trim($dom->filter('h1')->first()->text())
                : null,

            'canonical' => $dom->filter('link[rel="canonical"]')->count()
                ? $dom->filter('link[rel="canonical"]')->attr('href')
                : null,
        ];
    }
}
