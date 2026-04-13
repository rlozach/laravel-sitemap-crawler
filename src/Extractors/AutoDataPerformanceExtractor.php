<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\PerformanceNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataPerformanceExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly PerformanceNormalizer $normalizer = new PerformanceNormalizer(),
    ) {}

    /** @return array<string, mixed> */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'performance' => $this->normalizer->normalize(
                $this->parser->parseSection($dom, '_performance')
            ),
        ];
    }
}
