<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class DrivetrainNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Architecture de transmission'                    => ['key' => 'drive_description',  'transform' => 'string'],
            'Transmission'                                    => ['key' => 'drive_type',          'transform' => 'string'],
            'Nombre de vitesses et type de boîte de vitesse' => ['key' => 'gearbox',             'transform' => 'string'],
            'Freins avant'                                    => ['key' => 'front_brakes',        'transform' => 'string'],
            'Freins arrière'                                  => ['key' => 'rear_brakes',         'transform' => 'string'],
            'Systèmes d\'assistance'                          => ['key' => 'assistance_systems',  'transform' => 'string'],
            'Direction'                                       => ['key' => 'steering',            'transform' => 'string'],
            'Direction assistée'                              => ['key' => 'power_steering',      'transform' => 'string'],
            'Taille des pneus'                                => ['key' => 'tire_size',           'transform' => 'string'],
            'jantes de taille'                                => ['key' => 'wheel_size_inch',     'transform' => 'int'],
            'Suspension avant'                                => ['key' => 'front_suspension',    'transform' => 'string'],
            'Suspension arrière'                              => ['key' => 'rear_suspension',     'transform' => 'string'],
        ];
    }
}
