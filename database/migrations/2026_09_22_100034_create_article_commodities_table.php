<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_commodities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('commodity_id')->constrained()->cascadeOnDelete();
            $table->unique(['article_id', 'commodity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_commodities');
    }
};
