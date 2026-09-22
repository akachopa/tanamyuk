<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('cultivation_templates')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('start_day_offset')->default(0);
            $table->integer('end_day_offset')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_stages');
    }
};
