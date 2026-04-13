<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\ElectricNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataElectricExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly ElectricNormalizer $normalizer = new ElectricNormalizer(),
    ) {}

    /** @return array<string, mixed> */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'electric' => $this->normalizer->normalize(
                $this->parser->parseSection($dom, '_electric')
            ),
        ];
    }
}
