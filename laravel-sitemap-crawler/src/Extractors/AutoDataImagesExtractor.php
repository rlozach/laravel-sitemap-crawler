<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Symfony\Component\DomCrawler\Crawler;

class AutoDataImagesExtractor implements Extractor
{
    private const BASE_URL = 'https://www.auto-data.net';

    /**
     * Extract all vehicle images from the .float336.left.top column.
     * Thumbnails (_thumb) are converted to full-size URLs.
     *
     * @return array<string, mixed>
     */
    public function extract(Crawler $dom, string $url): array
    {
        $images = [];

        $dom->filter('.float336.left.top img')->each(function (Crawler $img) use (&$images): void {
            $src = $img->attr('src');

            if ($src === null || $src === '') {
                return;
            }

            $fullUrl = $this->toAbsoluteUrl($src);
            $fullUrl = $this->removeThumbSuffix($fullUrl);

            if (! in_array($fullUrl, $images, strict: true)) {
                $images[] = $fullUrl;
            }
        });

        return ['images' => $images];
    }

    private function toAbsoluteUrl(string $src): string
    {
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }

        return self::BASE_URL.'/'.ltrim($src, '/');
    }

    private function removeThumbSuffix(string $url): string
    {
        return str_replace('_thumb.', '.', $url);
    }
}
