<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\VolumeNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataVolumeExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly VolumeNormalizer $normalizer = new VolumeNormalizer(),
    ) {}

    /** @return array<string, mixed> */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'volume' => $this->normalizer->normalize(
                $this->parser->parseSection($dom, '_volume')
            ),
        ];
    }
}
