<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendation_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assessment_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('commodity_id')->constrained()->cascadeOnDelete();
            $table->integer('score')->default(0);
            $table->json('reasons')->nullable();
            $table->unsignedInteger('rank')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendation_results');
    }
};
