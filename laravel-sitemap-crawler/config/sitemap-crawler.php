<?php

// config for roloza/laravel-sitemap-crawler
return [
    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    | The User-Agent header sent with each HTTP request.
    */
    'user_agent' => 'SitemapCrawler/1.0',

    /*
    |--------------------------------------------------------------------------
    | Concurrency
    |--------------------------------------------------------------------------
    | Number of parallel HTTP requests when crawling page URLs.
    */
    'concurrency' => 5,

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    | Maximum number of seconds to wait for each HTTP response.
    */
    'timeout' => 30,

    /*
    |--------------------------------------------------------------------------
    | Recrawl Delay
    |--------------------------------------------------------------------------
    | Number of days before a previously crawled URL is re-downloaded.
    | Within this window, extractors are re-run on the HTML already stored
    | in the database — no HTTP request is made.
    | Set to 0 to always re-download.
    */
    'recrawl_after_days' => 7,

    /*
    |--------------------------------------------------------------------------
    | Extractors
    |--------------------------------------------------------------------------
    | List of extractor classes that implement Roloza\SitemapCrawler\Contracts\Extractor.
    | Each extractor receives the Symfony DomCrawler instance and the page URL,
    | and returns an array of key/value pairs stored in crawl_results.extracted_data.
    */
    'extractors' => [
        \Roloza\SitemapCrawler\Extractors\MetaExtractor::class,
    ],
];
