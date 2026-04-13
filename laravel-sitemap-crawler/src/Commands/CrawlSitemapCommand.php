<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Commands;

use Illuminate\Console\Command;
use Roloza\SitemapCrawler\SitemapCrawler;

class CrawlSitemapCommand extends Command
{
    protected $signature = 'sitemap:crawl
        {sitemap* : One or more sitemap URLs to crawl}
        {--filter= : Only crawl URLs containing this pattern}
        {--concurrency=5 : Number of concurrent requests}
        {--timeout=30 : HTTP request timeout in seconds}';

    protected $description = 'Crawl URLs from one or more sitemaps and extract DOM data';

    public function handle(SitemapCrawler $crawler): int
    {
        $sitemaps = (array) $this->argument('sitemap');
        $concurrency = (int) $this->option('concurrency');
        $timeout = (int) $this->option('timeout');
        $filter = $this->option('filter');

        $this->info(sprintf('Parsing %d sitemap(s)...', count($sitemaps)));

        $pending = $crawler
            ->fromSitemaps($sitemaps)
            ->concurrency($concurrency)
            ->timeout($timeout);

        if ($filter !== null) {
            $pending->filterUrls($filter);
        }

        $results = $pending->crawl();

        $this->info(sprintf('Crawled %d page(s).', $results->count()));

        $successful = $results->where('status_code', '>=', 200)->where('status_code', '<', 300)->count();
        $failed = $results->count() - $successful;

        $this->table(
            ['Total', 'Successful', 'Failed'],
            [[$results->count(), $successful, $failed]],
        );

        return self::SUCCESS;
    }
}
