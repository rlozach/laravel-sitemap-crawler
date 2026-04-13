<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crawl_results', function (Blueprint $table): void {
            $table->id();
            $table->string('url')->index();
            $table->string('sitemap_url')->nullable()->index();
            $table->unsignedSmallInteger('status_code')->default(0);
            $table->json('extracted_data')->nullable();
            $table->longText('html')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crawl_results');
    }
};
