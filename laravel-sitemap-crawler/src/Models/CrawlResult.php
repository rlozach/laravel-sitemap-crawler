<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Roloza\SitemapCrawler\Database\Factories\CrawlResultFactory;

class CrawlResult extends Model
{
    use HasFactory;

    protected static function newFactory(): Factory
    {
        return CrawlResultFactory::new();
    }

    protected $table = 'crawl_results';

    protected $fillable = [
        'url',
        'sitemap_url',
        'status_code',
        'extracted_data',
        'html',
        'crawled_at',
    ];

    protected $casts = [
        'extracted_data' => 'array',
        'crawled_at' => 'datetime',
        'status_code' => 'integer',
    ];
}
