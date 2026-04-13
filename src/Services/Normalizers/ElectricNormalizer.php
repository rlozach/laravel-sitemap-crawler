<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class ElectricNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Capacité brute de la batterie'                                      => ['key' => 'battery_capacity_kwh', 'transform' => 'float'],
            'Technologie de stockage de la batterie'                             => ['key' => 'battery_technology',   'transform' => 'string'],
            'Emplacement de la batterie'                                          => ['key' => 'battery_location',     'transform' => 'string'],
            "L'autonomie en mode électrique (Autonomie sur batterie) (WLTP)"     => ['key' => 'range_wltp_km',        'transform' => 'int'],
            'Ports de chargement'                                                 => ['key' => 'charge_ports',         'transform' => 'string'],
            'Puissance de Machine électrique'                                     => ['key' => 'motor_power_hp',       'transform' => 'int'],
            'Couple maxi de Machine électrique'                                   => ['key' => 'motor_torque_nm',      'transform' => 'int'],
            'Emplacement moteur électrique'                                       => ['key' => 'motor_location',       'transform' => 'string'],
            'Type de moteur électrique'                                           => ['key' => 'motor_type',           'transform' => 'string'],
            'Puissance du système'                                                => ['key' => 'system_power_hp',      'transform' => 'int'],
            'Couple système'                                                      => ['key' => 'system_torque_nm',     'transform' => 'int'],
        ];
    }
}
