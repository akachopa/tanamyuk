<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_cycles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('garden_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('commodity_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('variety_id')->nullable()->constrained('commodity_varieties')->nullOnDelete();
            $table->foreignUuid('template_id')->nullable()->constrained('cultivation_templates')->nullOnDelete();
            $table->string('template_version')->nullable();
            $table->foreignUuid('cultivation_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->date('start_date')->nullable();
            $table->date('target_harvest_date')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('quantity_unit')->nullable();
            $table->uuid('current_stage_id')->nullable()->index();
            $table->string('status')->default('draft');
            $table->string('cover_image_path')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_cycles');
    }
};
