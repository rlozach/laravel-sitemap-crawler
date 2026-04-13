<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class PerformanceNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Consommation de carburant - cycle urbain'              => ['key' => 'fuel_consumption_city',     'transform' => 'float'],
            'Consommation de carburant - cycle extra-urbain'        => ['key' => 'fuel_consumption_highway',  'transform' => 'float'],
            'Consommation de carburant - cycle mixte'               => ['key' => 'fuel_consumption_mixed',    'transform' => 'float'],
            'Émissions de CO2'                                      => ['key' => 'co2_emissions',             'transform' => 'int'],
            'Type de carburant'                                     => ['key' => 'fuel_type',                 'transform' => 'string'],
            'Accélération 0 - 100 km/h'                            => ['key' => 'acceleration_0_100',        'transform' => 'float'],
            'Accélération 0 - 62 mph'                              => ['key' => 'acceleration_0_62mph',      'transform' => 'float'],
            'Accélération 0 - 60 mph (Calculé par Auto-Data.net)'  => ['key' => 'acceleration_0_60mph',      'transform' => 'float'],
            'vitesse maximale'                                      => ['key' => 'top_speed_kmh',             'transform' => 'int'],
            'Rapport poids/puissance'                               => ['key' => 'weight_power_ratio',        'transform' => 'float'],
            'Rapport poids/Couple'                                  => ['key' => 'weight_torque_ratio',       'transform' => 'float'],
        ];
    }
}
