<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class VolumeNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'poids'                        => ['key' => 'weight_kg',           'transform' => 'int'],
            'Poids maximum'                => ['key' => 'max_weight_kg',       'transform' => 'int'],
            'Charge maximum'               => ['key' => 'max_load_kg',         'transform' => 'int'],
            'Volume mini du coffre'        => ['key' => 'trunk_min_l',         'transform' => 'int'],
            'Volume maxi du coffre'        => ['key' => 'trunk_max_l',         'transform' => 'int'],
            'Réservoir à carburant'        => ['key' => 'fuel_tank_l',         'transform' => 'int'],
            'Poids remorquable non freiné' => ['key' => 'towing_unbraked_kg',  'transform' => 'int'],
        ];
    }
}
