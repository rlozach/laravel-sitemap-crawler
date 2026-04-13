<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Contracts;

interface Normalizer
{
    /**
     * Normalize a raw key-value array extracted from a page section.
     *
     * @param  array<string, string|null>  $raw
     * @return array<string, mixed>
     */
    public function normalize(array $raw): array;
}
