<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('template_id')->constrained('cultivation_templates')->cascadeOnDelete();
            $table->foreignUuid('stage_id')->nullable()->constrained('template_stages')->nullOnDelete();
            $table->string('task_type');
            $table->string('title');
            $table->text('instruction')->nullable();
            $table->integer('first_day_offset')->default(0);
            $table->unsignedInteger('repeat_interval_days')->nullable();
            $table->integer('repeat_until_day_offset')->nullable();
            $table->unsignedInteger('estimated_minutes')->nullable();
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_tasks');
    }
};
