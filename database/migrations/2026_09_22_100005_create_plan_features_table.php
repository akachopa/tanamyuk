<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('feature_id')->constrained()->cascadeOnDelete();
            $table->boolean('value_boolean')->nullable();
            $table->decimal('value_number', 12, 2)->nullable();
            $table->string('value_text')->nullable();
            $table->unique(['plan_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
