<?php

declare(strict_types=1);

arch('no debug statements in source')
    ->expect('Roloza\SitemapCrawler')
    ->not->toUse(['dd', 'dump', 'var_dump', 'print_r', 'ray']);

arch('strict types declared everywhere')
    ->expect('Roloza\SitemapCrawler')
    ->toUseStrictTypes();

arch('extractors implement the Extractor contract')
    ->expect('Roloza\SitemapCrawler\Extractors')
    ->toImplement('Roloza\SitemapCrawler\Contracts\Extractor');
