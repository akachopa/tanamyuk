<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultivation_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('commodity_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('variety_id')->nullable()->constrained('commodity_varieties')->nullOnDelete();
            $table->foreignUuid('cultivation_method_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('version')->default('1');
            $table->unsignedInteger('duration_days')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->text('description')->nullable();
            $table->json('requirements')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultivation_templates');
    }
};
