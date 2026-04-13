<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\EngineNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataEngineExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly EngineNormalizer $normalizer = new EngineNormalizer(),
    ) {}

    /** @return array<string, mixed> */
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'engine' => $this->normalizer->normalize(
                $this->parser->parseSection($dom, '_engine')
            ),
        ];
    }
}
