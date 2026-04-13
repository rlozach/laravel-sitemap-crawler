<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services;

use DOMElement;
use DOMNode;
use DOMText;
use Symfony\Component\DomCrawler\Crawler;

class TableSectionParser
{
    /**
     * Extract key-value pairs from the table section identified by $sectionId.
     *
     * The section starts at the <tr> containing <strong id="$sectionId"> and ends
     * before the next <tr class="no"> header row.
     *
     * Values from <span class="val2"> (secondary unit conversions) are excluded.
     * Locked fields (img.datalock) return null.
     *
     * @return array<string, string|null>
     */
    public function parseSection(Crawler $dom, string $sectionId): array
    {
        $inSection = false;
        $rows = [];

        $dom->filter('table tr')->each(function (Crawler $tr) use (&$inSection, &$rows, $sectionId): void {
            // Detect section header matching our target id
            if ($tr->filter("strong[id='{$sectionId}']")->count() > 0) {
                $inSection = true;

                return;
            }

            // Any other section header — stop collecting
            if ($this->isSectionHeader($tr)) {
                $inSection = false;

                return;
            }

            if (! $inSection) {
                return;
            }

            $th = $tr->filter('th');
            $td = $tr->filter('td');

            if ($th->count() === 0 || $td->count() === 0) {
                return;
            }

            $key = trim($th->text());
            $value = $this->extractValue($td);

            if ($key !== '') {
                $rows[$key] = $value;
            }
        });

        return $rows;
    }

    /**
     * Parse all sections of the table, keyed by their section id.
     *
     * @return array<string, array<string, string|null>>
     */
    public function parseAllSections(Crawler $dom): array
    {
        $currentSection = null;
        $sections = [];

        $dom->filter('table tr')->each(function (Crawler $tr) use (&$currentSection, &$sections): void {
            $strong = $tr->filter('strong[id]');

            if ($strong->count() > 0) {
                $currentSection = $strong->attr('id');
                $sections[$currentSection] = [];

                return;
            }

            if ($currentSection === null || $this->isSectionHeader($tr)) {
                return;
            }

            $th = $tr->filter('th');
            $td = $tr->filter('td');

            if ($th->count() === 0 || $td->count() === 0) {
                return;
            }

            $key = trim($th->text());
            $value = $this->extractValue($td);

            if ($key !== '') {
                $sections[$currentSection][$key] = $value;
            }
        });

        return $sections;
    }

    private function isSectionHeader(Crawler $tr): bool
    {
        $class = $tr->attr('class') ?? '';

        return str_contains($class, 'no');
    }

    private function extractValue(Crawler $td): ?string
    {
        // Locked content
        if ($td->filter('img.datalock')->count() > 0) {
            return null;
        }

        $node = $td->getNode(0);

        if (! $node instanceof DOMElement) {
            return null;
        }

        $text = trim($this->collectText($node));

        return $text !== '' ? $text : null;
    }

    /**
     * Recursively collect text content, skipping <span class="val2"> nodes.
     */
    private function collectText(DOMNode $node): string
    {
        $text = '';

        foreach ($node->childNodes as $child) {
            if ($child instanceof DOMText) {
                $text .= $child->textContent;
                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            // Skip secondary unit conversions
            if ($child->nodeName === 'span' && str_contains($child->getAttribute('class'), 'val2')) {
                continue;
            }

            $text .= $this->collectText($child);
        }

        return $text;
    }
}
