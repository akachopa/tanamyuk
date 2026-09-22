<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commodities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('category_id')->constrained('commodity_categories')->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('scientific_name')->nullable();
            $table->text('short_description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->decimal('min_space_m2', 8, 2)->nullable();
            $table->unsignedTinyInteger('sunlight_min_hours')->nullable();
            $table->unsignedTinyInteger('sunlight_max_hours')->nullable();
            $table->string('water_need_level')->nullable();
            $table->unsignedInteger('maintenance_minutes_per_day')->nullable();
            $table->unsignedInteger('harvest_min_days')->nullable();
            $table->unsignedInteger('harvest_max_days')->nullable();
            $table->boolean('repeat_harvest')->default(false);
            $table->string('default_harvest_unit')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};
