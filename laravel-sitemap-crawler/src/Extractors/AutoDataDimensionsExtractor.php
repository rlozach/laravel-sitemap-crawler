<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\DimensionsNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataDimensionsExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly DimensionsNormalizer $normalizer = new DimensionsNormalizer(),
    ) {}

    /** @return array<string, mixed> */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'dimensions' => $this->normalizer->normalize(
                $this->parser->parseSection($dom, '_dimensions')
            ),
        ];
    }
}
