<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cycle_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('planting_cycle_id')->constrained()->cascadeOnDelete();
            $table->uuid('source_template_stage_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cycle_stages');
    }
};
