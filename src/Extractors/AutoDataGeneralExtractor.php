<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Roloza\SitemapCrawler\Services\Normalizers\GeneralNormalizer;
use Roloza\SitemapCrawler\Services\TableSectionParser;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataGeneralExtractor implements Extractor
{
    public function __construct(
        private readonly TableSectionParser $parser = new TableSectionParser(),
        private readonly GeneralNormalizer $normalizer = new GeneralNormalizer(),
    ) {}

    /**
     * Extract and normalize the "Informations générales" section from an auto-data.net vehicle page.
     *
     * @return array<string, mixed>
     */
    public function extract(Crawler $dom, string $url): array
    {
        $raw = $this->parser->parseSection($dom, '_general');

        return [
            'general' => $this->normalizer->normalize($raw),
        ];
    }
}
