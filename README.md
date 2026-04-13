# Laravel Sitemap Crawler

Parse un ou plusieurs sitemaps XML, crawle chaque URL de façon concurrente et extrait des données structurées depuis le DOM des pages. Les résultats sont stockés en base de données.

---

## Sommaire

- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
  - [Commande Artisan](#commande-artisan)
  - [Facade](#facade)
- [Extracteurs](#extracteurs)
  - [MetaExtractor](#metaextractor)
  - [Extracteurs Auto-Data.net](#extracteurs-auto-datanet)
  - [Créer un extracteur personnalisé](#créer-un-extracteur-personnalisé)
- [Normalizers](#normalizers)
  - [Normalizers Auto-Data.net](#normalizers-auto-datanet)
  - [Créer un normalizer personnalisé](#créer-un-normalizer-personnalisé)
- [Service TableSectionParser](#service-tablesectionparser)
- [Modèle CrawlResult](#modèle-crawlresult)
- [Comportement de cache](#comportement-de-cache)

---

## Prérequis

- PHP 8.2+
- Laravel 10 / 11 / 12 / 13

---

## Installation

```bash
composer require roloza/laravel-sitemap-crawler
```

Publier la configuration :

```bash
php artisan vendor:publish --tag=sitemap-crawler-config
```

Publier et exécuter les migrations :

```bash
php artisan vendor:publish --tag=sitemap-crawler-migrations
php artisan migrate
```

---

## Configuration

`config/sitemap-crawler.php`

```php
return [
    // User-Agent envoyé avec chaque requête HTTP
    'user_agent' => 'SitemapCrawler/1.0',

    // Nombre de requêtes HTTP parallèles
    'concurrency' => 5,

    // Timeout en secondes par requête
    'timeout' => 30,

    // Nombre de jours avant de re-télécharger une page déjà crawlée.
    // Dans cette fenêtre, les extracteurs sont re-exécutés sur le HTML
    // déjà stocké en base — aucune requête HTTP n'est émise.
    // Mettre à 0 pour toujours re-télécharger.
    'recrawl_after_days' => 7,

    // Liste des extracteurs à appliquer sur chaque page crawlée.
    'extractors' => [
        \Roloza\SitemapCrawler\Extractors\MetaExtractor::class,
    ],
];
```

---

## Utilisation

### Commande Artisan

```bash
# Crawl simple
php artisan sitemap:crawl https://example.com/sitemap.xml

# Plusieurs sitemaps
php artisan sitemap:crawl https://example.com/sitemap.xml https://other.com/sitemap.xml

# Filtrer les URLs contenant un pattern
php artisan sitemap:crawl https://www.auto-data.net/sitemap/c.php?a=6 --filter=/fr/

# Options avancées
php artisan sitemap:crawl https://example.com/sitemap.xml \
    --filter=/blog/ \
    --concurrency=10 \
    --timeout=60
```

| Option | Défaut | Description |
|---|---|---|
| `sitemap*` | — | Une ou plusieurs URLs de sitemap |
| `--filter` | — | N'inclure que les URLs contenant ce pattern |
| `--concurrency` | `5` | Requêtes HTTP parallèles |
| `--timeout` | `30` | Timeout par requête (secondes) |

---

### Facade

```php
use Roloza\SitemapCrawler\Facades\SitemapCrawler;

// Crawl basique
$results = SitemapCrawler::fromSitemaps(['https://example.com/sitemap.xml'])
    ->crawl();

// Avec filtre d'URL
$results = SitemapCrawler::fromSitemaps(['https://www.auto-data.net/sitemap/c.php?a=6'])
    ->filterUrls('/fr/')
    ->crawl();

// Avec extracteurs personnalisés pour ce run uniquement
$results = SitemapCrawler::fromSitemaps(['https://example.com/sitemap.xml'])
    ->withExtractors([new MyCustomExtractor()])
    ->crawl();

// Paramètres avancés
$results = SitemapCrawler::fromSitemaps(['https://example.com/sitemap.xml'])
    ->concurrency(10)
    ->timeout(60)
    ->crawl();
```

`crawl()` retourne une `Collection` de `CrawlResult`. Chaque résultat est persisté en base de données.

**Méthodes disponibles :**

| Méthode | Description |
|---|---|
| `fromSitemaps(array $urls)` | Définit les sitemaps à parser |
| `filterUrls(string $pattern)` | Filtre les URLs par pattern (`str_contains`) |
| `withExtractors(array $extractors)` | Surcharge les extracteurs pour ce run |
| `concurrency(int $n)` | Nombre de requêtes parallèles |
| `timeout(int $seconds)` | Timeout HTTP |
| `crawl()` | Lance le crawl, retourne `Collection<CrawlResult>` |

---

## Extracteurs

Un extracteur reçoit le DOM d'une page et retourne un tableau de données. Les résultats de tous les extracteurs sont fusionnés dans `CrawlResult::extracted_data`.

### MetaExtractor

Extracteur par défaut. Extrait les métadonnées HTML standard.

```php
'extractors' => [
    \Roloza\SitemapCrawler\Extractors\MetaExtractor::class,
],
```

**Données extraites :**

```json
{
  "title": "Mon titre de page",
  "description": "Description meta de la page",
  "h1": "Titre principal",
  "canonical": "https://example.com/page"
}
```

---

### Extracteurs Auto-Data.net

Extracteurs spécialisés pour les fiches techniques du site [auto-data.net](https://www.auto-data.net). Chaque extracteur parse une section du tableau de spécifications et normalise les valeurs (typage, suppression des unités).

```php
'extractors' => [
    \Roloza\SitemapCrawler\Extractors\AutoDataGeneralExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataPerformanceExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataEngineExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataVolumeExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataDimensionsExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataDrivetrainExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataElectricExtractor::class,
    \Roloza\SitemapCrawler\Extractors\AutoDataImagesExtractor::class,
],
```

#### AutoDataGeneralExtractor → clé `general`

| Clé | Type | Exemple |
|---|---|---|
| `brand` | `string` | `"Alfa Romeo"` |
| `model` | `string` | `"147"` |
| `generation` | `string` | `"147 (facelift 2004) 5-doors"` |
| `engine_variant` | `string` | `"1.9 JTD (120 CH)"` |
| `date_start` | `int\|null` | `2005` |
| `date_end` | `int\|null` | `2010` |
| `powertrain_architecture` | `string` | `"moteur à combustion interne"` |
| `body_type` | `string` | `"Hatchback"` |
| `seats` | `int\|null` | `5` |
| `doors` | `int\|null` | `5` |

#### AutoDataPerformanceExtractor → clé `performance`

| Clé | Type | Exemple |
|---|---|---|
| `fuel_consumption_city` | `float\|null` | `7.8` |
| `fuel_consumption_highway` | `float\|null` | `4.7` |
| `fuel_consumption_mixed` | `float\|null` | `5.8` |
| `co2_emissions` | `int\|null` | `153` |
| `fuel_type` | `string` | `"Diesel"` |
| `acceleration_0_100` | `float\|null` | `9.6` |
| `acceleration_0_62mph` | `float\|null` | `9.6` |
| `acceleration_0_60mph` | `float\|null` | `9.1` |
| `top_speed_kmh` | `int\|null` | `193` |
| `weight_power_ratio` | `float\|null` | `10.8` |
| `weight_torque_ratio` | `float\|null` | `4.6` |

#### AutoDataEngineExtractor → clé `engine`

| Clé | Type | Exemple |
|---|---|---|
| `max_power_hp` | `int\|null` | `120` |
| `power_per_liter` | `float\|null` | `62.8` |
| `max_torque_nm` | `int\|null` | `280` |
| `engine_position` | `string\|null` | `"Avant, transversal"` |
| `engine_code` | `string\|null` | `"937 A3.000"` |
| `displacement_cc` | `int\|null` | `1910` |
| `cylinders` | `int\|null` | `4` |
| `cylinder_layout` | `string\|null` | `"ligne"` |
| `bore_mm` | `float\|null` | `82.0` |
| `stroke_mm` | `float\|null` | `90.4` |
| `compression_ratio` | `string\|null` | `"18:1"` |
| `valves_per_cylinder` | `int\|null` | `2` |
| `fuel_injection` | `string\|null` | `"Commonrail Diesel"` |
| `turbo` | `string\|null` | `"Turbocompresseur, Refroidisseur intermédiaire"` |
| `oil_capacity_l` | `float\|null` | `4.4` |
| `oil_spec` | `string\|null` | `null` (contenu verrouillé) |
| `coolant_capacity_l` | `float\|null` | `7.2` |

#### AutoDataVolumeExtractor → clé `volume`

| Clé | Type | Exemple |
|---|---|---|
| `weight_kg` | `int\|null` | `1290` |
| `max_weight_kg` | `int\|null` | `1819` |
| `max_load_kg` | `int\|null` | `529` |
| `trunk_min_l` | `int\|null` | `292` |
| `trunk_max_l` | `int\|null` | `1042` |
| `fuel_tank_l` | `int\|null` | `60` |
| `towing_unbraked_kg` | `int\|null` | `500` |

#### AutoDataDimensionsExtractor → clé `dimensions`

| Clé | Type | Exemple |
|---|---|---|
| `length_mm` | `int\|null` | `4223` |
| `width_mm` | `int\|null` | `1729` |
| `height_mm` | `int\|null` | `1442` |
| `wheelbase_mm` | `int\|null` | `2546` |
| `front_track_mm` | `int\|null` | `1518` |
| `rear_track_mm` | `int\|null` | `1502` |
| `ground_clearance_mm` | `int\|null` | `120` |
| `front_overhang_mm` | `int\|null` | `749` |
| `rear_overhang_mm` | `int\|null` | `633` |

#### AutoDataDrivetrainExtractor → clé `drivetrain`

| Clé | Type | Exemple |
|---|---|---|
| `drive_description` | `string\|null` | `"Le moteur entraîne les roues avant."` |
| `drive_type` | `string\|null` | `"Traction avant"` |
| `gearbox` | `string\|null` | `"5 vitesses, boîte de vitesse manuelle"` |
| `front_brakes` | `string\|null` | `"Disques ventilés"` |
| `rear_brakes` | `string\|null` | `"disques ventilés"` |
| `assistance_systems` | `string\|null` | `"ABS"` |
| `steering` | `string\|null` | `"Crémaillère de direction"` |
| `power_steering` | `string\|null` | `"Direction assistée électrique"` |
| `tire_size` | `string\|null` | `"185/65 R15"` |
| `wheel_size_inch` | `int\|null` | `15` |
| `front_suspension` | `string\|null` | `"type McPherson indépendants"` |
| `rear_suspension` | `string\|null` | `"Suspension multibras indépendante"` |

#### AutoDataElectricExtractor → clé `electric`

| Clé | Type | Exemple |
|---|---|---|
| `battery_capacity_kwh` | `float\|null` | `52.0` |
| `battery_technology` | `string\|null` | `"Lithium Nickel Manganèse Cobalt Oxyde (Li-NMC)"` |
| `battery_location` | `string\|null` | `"Sous le plancher"` |
| `range_wltp_km` | `int\|null` | `400` |
| `charge_ports` | `string\|null` | `null` (contenu verrouillé) |
| `motor_power_hp` | `int\|null` | `150` |
| `motor_torque_nm` | `int\|null` | `245` |
| `motor_location` | `string\|null` | `"Essieu avant, transversal"` |
| `motor_type` | `string\|null` | `"Synchrone"` |
| `system_power_hp` | `int\|null` | `150` |
| `system_torque_nm` | `int\|null` | `245` |

#### AutoDataImagesExtractor → clé `images`

Extrait toutes les images de la colonne de gauche (`.float336.left.top`). Les miniatures (`_thumb`) sont converties en URL grand format.

```json
{
  "images": [
    "https://www.auto-data.net/images/f46/Renault-5-E-Tech_1.jpg",
    "https://www.auto-data.net/images/f123/Renault-5-E-Tech.jpg",
    "https://www.auto-data.net/images/f121/Renault-5-E-Tech.jpg"
  ]
}
```

---

### Créer un extracteur personnalisé

Implémenter l'interface `Roloza\SitemapCrawler\Contracts\Extractor` :

```php
<?php

namespace App\Extractors;

use Roloza\SitemapCrawler\Contracts\Extractor;
use Symfony\Component\DomCrawler\Crawler;

class PriceExtractor implements Extractor
{
    public function extract(Crawler $dom, string $url): array
    {
        return [
            'price' => $dom->filter('.price')->count()
                ? trim($dom->filter('.price')->text())
                : null,

            'currency' => $dom->filter('.currency')->count()
                ? $dom->filter('.currency')->attr('data-code')
                : null,
        ];
    }
}
```

Enregistrer dans la configuration :

```php
'extractors' => [
    \App\Extractors\PriceExtractor::class,
],
```

Ou passer directement à la Facade pour un run ponctuel :

```php
SitemapCrawler::fromSitemaps(['https://example.com/sitemap.xml'])
    ->withExtractors([new PriceExtractor()])
    ->crawl();
```

L'extracteur reçoit une instance de `Symfony\Component\DomCrawler\Crawler`. Consulter la [documentation Symfony DomCrawler](https://symfony.com/doc/current/components/dom_crawler.html) pour les sélecteurs CSS et XPath disponibles.

---

## Normalizers

Un normalizer transforme les données brutes extraites (labels en français, valeurs avec unités) en un tableau typé avec des clés anglaises normalisées.

### Normalizers Auto-Data.net

Chaque normalizer correspond à une section de tableau :

| Normalizer | Utilisé par |
|---|---|
| `GeneralNormalizer` | `AutoDataGeneralExtractor` |
| `PerformanceNormalizer` | `AutoDataPerformanceExtractor` |
| `EngineNormalizer` | `AutoDataEngineExtractor` |
| `VolumeNormalizer` | `AutoDataVolumeExtractor` |
| `DimensionsNormalizer` | `AutoDataDimensionsExtractor` |
| `DrivetrainNormalizer` | `AutoDataDrivetrainExtractor` |
| `ElectricNormalizer` | `AutoDataElectricExtractor` |

Utilisation directe :

```php
use Roloza\SitemapCrawler\Services\Normalizers\EngineNormalizer;

$raw = ['Cylindrée' => '1910 cm3', 'Nombre de cylindres' => '4'];
$normalized = (new EngineNormalizer())->normalize($raw);
// ['displacement_cc' => 1910, 'cylinders' => 4, ...]
```

---

### Créer un normalizer personnalisé

Étendre `AbstractNormalizer` et définir le tableau `fields()` :

```php
<?php

namespace App\Normalizers;

use Roloza\SitemapCrawler\Services\Normalizers\AbstractNormalizer;

class ProductNormalizer extends AbstractNormalizer
{
    protected function fields(): array
    {
        return [
            'Prix'           => ['key' => 'price',        'transform' => 'float'],
            'Stock'          => ['key' => 'stock',         'transform' => 'int'],
            'Référence'      => ['key' => 'sku',           'transform' => 'string'],
            'Date de sortie' => ['key' => 'release_year',  'transform' => 'year'],
        ];
    }
}
```

**Transforms disponibles :**

| Transform | Comportement | Exemple |
|---|---|---|
| `string` | `trim()` | `"  Hatchback  "` → `"Hatchback"` |
| `int` | Premier entier extrait | `"193 km/h"` → `193` |
| `float` | Premier nombre extrait | `"7.8 l/100 km"` → `7.8` |
| `year` | Année 4 chiffres extraite | `"Avril, 2023 année"` → `2023` |

Les champs absents dans les données brutes et les valeurs vides retournent `null`.

---

## Service TableSectionParser

Service bas niveau pour parser les tableaux HTML structurés en sections. Réutilisable indépendamment des extracteurs Auto-Data.

```php
use Roloza\SitemapCrawler\Services\TableSectionParser;

$parser = new TableSectionParser();

// Parser une section spécifique par son id HTML
$data = $parser->parseSection($dom, '_engine');
// ['Cylindrée' => '1910 cm3', 'Nombre de cylindres' => '4', ...]

// Parser toutes les sections en une passe
$allSections = $parser->parseAllSections($dom);
// ['_general' => [...], '_engine' => [...], '_performance' => [...], ...]
```

**Structure HTML attendue :**

```html
<table>
  <!-- En-tête de section : tr.no avec un strong[id] -->
  <tr class="no">
    <th colspan="2"><strong id="_engine">Moteur</strong></th>
  </tr>
  <!-- Lignes de données jusqu'au prochain en-tête -->
  <tr>
    <th>Cylindrée</th>
    <td>1910 cm³</td>
  </tr>
  <!-- ... -->
</table>
```

**Comportement :**
- Les `<span class="val2">` (unités secondaires : mph, lbs…) sont exclus de la valeur.
- Les champs verrouillés (`<img class="datalock">`) retournent `null`.
- Les namespaces XML dans les sitemaps sont ignorés via `local-name()`.

---

## Modèle CrawlResult

```php
use Roloza\SitemapCrawler\Models\CrawlResult;

// Tous les résultats
CrawlResult::all();

// Derniers résultats crawlés
CrawlResult::latest('crawled_at')->get();

// Filtrer par statut
CrawlResult::where('status_code', 200)->get();

// Accéder aux données extraites
$result = CrawlResult::first();
$result->url;                         // string
$result->sitemap_url;                 // string|null
$result->status_code;                 // int
$result->extracted_data;              // array (cast automatique depuis JSON)
$result->extracted_data['general'];   // array
$result->extracted_data['images'];    // array
$result->html;                        // string|null (HTML brut de la page)
$result->crawled_at;                  // Carbon
```

**Schéma de la table `crawl_results` :**

| Colonne | Type | Description |
|---|---|---|
| `id` | `bigint` | Clé primaire |
| `url` | `varchar` | URL crawlée (indexée) |
| `sitemap_url` | `varchar\|null` | Sitemap source (indexée) |
| `status_code` | `smallint` | Code HTTP (`0` si erreur réseau) |
| `extracted_data` | `json\|null` | Données extraites et normalisées |
| `html` | `longtext\|null` | HTML brut de la page |
| `crawled_at` | `timestamp\|null` | Date du crawl |
| `created_at` | `timestamp` | — |
| `updated_at` | `timestamp` | — |

---

## Comportement de cache

Le package évite de re-télécharger les pages récemment crawlées.

**Logique pour chaque URL du sitemap :**

```
URL en base + crawled_at < recrawl_after_days + html présent
  → Re-exécute les extracteurs sur le html stocké
  → Met à jour extracted_data
  → Aucune requête HTTP

URL absente, expirée, ou html null
  → Téléchargement HTTP
  → updateOrCreate en base
```

Forcer le re-téléchargement pour un run ponctuel :

```php
// Ignorer le cache en mettant recrawl_after_days à 0 dans config
// ou en vidant la table avant le crawl
CrawlResult::truncate();
SitemapCrawler::fromSitemaps([...])->crawl();
```
