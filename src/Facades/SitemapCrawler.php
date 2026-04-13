<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Roloza\SitemapCrawler\SitemapCrawler fromSitemaps(array $urls)
 * @method static \Roloza\SitemapCrawler\SitemapCrawler withExtractors(array $extractors)
 * @method static \Roloza\SitemapCrawler\SitemapCrawler filterUrls(string $pattern)
 * @method static \Roloza\SitemapCrawler\SitemapCrawler concurrency(int $concurrency)
 * @method static \Roloza\SitemapCrawler\SitemapCrawler timeout(int $seconds)
 * @method static \Illuminate\Support\Collection crawl()
 *
 * @see \Roloza\SitemapCrawler\SitemapCrawler
 */
class SitemapCrawler extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Roloza\SitemapCrawler\SitemapCrawler::class;
    }
}
