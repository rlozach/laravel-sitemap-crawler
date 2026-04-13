<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Services\Normalizers;

class GeneralNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'marque'                                => ['key' => 'brand',                   'transform' => 'string'],
            'modèle'                                => ['key' => 'model',                   'transform' => 'string'],
            'Génération'                            => ['key' => 'generation',              'transform' => 'string'],
            'Modification (moteur)'                 => ['key' => 'engine_variant',          'transform' => 'string'],
            'année de début la production'          => ['key' => 'date_start',              'transform' => 'year'],
            'Fin de la période de production'       => ['key' => 'date_end',                'transform' => 'year'],
            'Architecture du groupe motopropulseur' => ['key' => 'powertrain_architecture', 'transform' => 'string'],
            'Type de carrosserie'                   => ['key' => 'body_type',               'transform' => 'string'],
            'Nombre de places'                      => ['key' => 'seats',                   'transform' => 'int'],
            'Portes'                                => ['key' => 'doors',                   'transform' => 'int'],
        ];
    }
}
