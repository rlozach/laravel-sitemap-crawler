<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler;

use Roloza\SitemapCrawler\Commands\CrawlSitemapCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SitemapCrawlerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('sitemap-crawler')
            ->hasConfigFile()
            ->hasMigrations(['create_crawl_results_table'])
            ->hasCommand(CrawlSitemapCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->bind(SitemapCrawler::class, fn () => new SitemapCrawler());
    }
}
