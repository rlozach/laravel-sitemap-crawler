<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class DimensionsNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Longueur'    => ['key' => 'length_mm',           'transform' => 'int'],
            'Largeur'     => ['key' => 'width_mm',            'transform' => 'int'],
            'Hauteur'     => ['key' => 'height_mm',           'transform' => 'int'],
            'Empattement' => ['key' => 'wheelbase_mm',        'transform' => 'int'],
            'Voies avant' => ['key' => 'front_track_mm',      'transform' => 'int'],
            'Voies arrière' => ['key' => 'rear_track_mm',     'transform' => 'int'],
            'Garde au sol'  => ['key' => 'ground_clearance_mm','transform' => 'int'],
            'faux avant'    => ['key' => 'front_overhang_mm', 'transform' => 'int'],
            'faux arrière'  => ['key' => 'rear_overhang_mm',  'transform' => 'int'],
        ];
    }
}
