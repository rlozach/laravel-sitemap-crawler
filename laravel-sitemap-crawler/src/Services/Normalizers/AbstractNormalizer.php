<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

use Roloza\SitemapCrawler\Contracts\Normalizer;

abstract class AbstractNormalizer implements Normalizer
{
    /**
     * Define the field mappings: French label → ['key' => string, 'transform' => string].
     * Supported transforms: 'string', 'int', 'float', 'year'.
     *
     * @return array<string, array{key: string, transform: string}>
     */
    abstract protected function fields(): array;

    /**
     * @param  array<string, string|null>  $raw
     * @return array<string, mixed>
     */
    public function normalize(array $raw): array
    {
        $normalized = [];

        foreach ($this->fields() as $label => $definition) {
            $value = $raw[$label] ?? null;
            $normalized[$definition['key']] = $this->transform($value, $definition['transform']);
        }

        return $normalized;
    }

    protected function transform(?string $value, string $type): mixed
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return match ($type) {
            'year'   => $this->extractYear($value),
            'int'    => $this->extractInt($value),
            'float'  => $this->extractFloat($value),
            default  => trim($value),
        };
    }

    /** Extracts a 4-digit year: "Février, 2024 année" → 2024 */
    protected function extractYear(string $value): ?int
    {
        return preg_match('/(\d{4})/', $value, $m) ? (int) $m[1] : null;
    }

    /** Extracts the first integer: "193 km/h" → 193 */
    protected function extractInt(string $value): ?int
    {
        return preg_match('/(\d+)/', $value, $m) ? (int) $m[1] : null;
    }

    /** Extracts the first float: "7.8 l/100 km" → 7.8, "90.4 mm" → 90.4 */
    protected function extractFloat(string $value): ?float
    {
        return preg_match('/([\d]+(?:[.,]\d+)?)/', $value, $m)
            ? (float) str_replace(',', '.', $m[1])
            : null;
    }
}
