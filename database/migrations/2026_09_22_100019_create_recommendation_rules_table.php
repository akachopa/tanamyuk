<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('input_field');
            $table->string('operator');
            $table->string('comparison_value')->nullable();
            $table->foreignUuid('commodity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained('commodity_categories')->nullOnDelete();
            $table->integer('score_delta')->default(0);
            $table->text('reason_template')->nullable();
            $table->unsignedInteger('priority')->default(0);
            $table->string('status')->default('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_rules');
    }
};
