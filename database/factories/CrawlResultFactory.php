<?php

declare(strict_types=1);

namespace Roloza\SitemapCrawler\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Roloza\SitemapCrawler\Models\CrawlResult;

class CrawlResultFactory extends Factory
{
    protected $model = CrawlResult::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'url' => $this->faker->url(),
            'sitemap_url' => $this->faker->url().'/sitemap.xml',
            'status_code' => 200,
            'extracted_data' => [
                'title' => $this->faker->sentence(),
                'description' => $this->faker->text(160),
                'h1' => $this->faker->sentence(),
                'canonical' => null,
            ],
            'crawled_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(['status_code' => 404, 'extracted_data' => null]);
    }
}
