<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commodity_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commodity_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('cultivation_method_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('suitability_score')->nullable();
            $table->text('notes')->nullable();
            $table->unique(['commodity_id', 'cultivation_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commodity_methods');
    }
};
