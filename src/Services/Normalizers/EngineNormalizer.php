<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class EngineNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Puissance max.'                     => ['key' => 'max_power_hp',        'transform' => 'int'],
            'Puissance par litre'                => ['key' => 'power_per_liter',     'transform' => 'float'],
            'Couple max.'                        => ['key' => 'max_torque_nm',       'transform' => 'int'],
            'Disposition du moteur'              => ['key' => 'engine_position',     'transform' => 'string'],
            'Modèle de moteur/Code moteur'       => ['key' => 'engine_code',         'transform' => 'string'],
            'Cylindrée'                          => ['key' => 'displacement_cc',     'transform' => 'int'],
            'Nombre de cylindres'                => ['key' => 'cylinders',           'transform' => 'int'],
            'Architecture des moteurs à pistons' => ['key' => 'cylinder_layout',    'transform' => 'string'],
            'Alésage'                            => ['key' => 'bore_mm',             'transform' => 'float'],
            'Course'                             => ['key' => 'stroke_mm',           'transform' => 'float'],
            'taux de compression'                => ['key' => 'compression_ratio',   'transform' => 'string'],
            'Nombre de soupapes par cylindre'    => ['key' => 'valves_per_cylinder', 'transform' => 'int'],
            'Système d\'injection de carburant'  => ['key' => 'fuel_injection',      'transform' => 'string'],
            'Suralimentation'                    => ['key' => 'turbo',               'transform' => 'string'],
            'Capacité d\'huile moteur'           => ['key' => 'oil_capacity_l',      'transform' => 'float'],
            'Spécification de l\'huile moteur'   => ['key' => 'oil_spec',            'transform' => 'string'],
            'liquide de refroidissement'         => ['key' => 'coolant_capacity_l',  'transform' => 'float'],
        ];
    }
}
