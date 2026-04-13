<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Contracts;

use Symfony\Component\DomCrawler\Crawler;

interface Extractor
{
    /**
     * Extract data from the DOM of a crawled page.
     *
     * @return array<string, mixed>
     */
    public function extract(Crawler $dom, string $url): array;
}
