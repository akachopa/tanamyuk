<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plant_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('planting_cycle_id')->constrained()->cascadeOnDelete();
            $table->timestamp('discovered_at')->nullable();
            $table->string('symptom_category')->nullable();
            $table->string('affected_part')->nullable();
            $table->string('severity')->nullable();
            $table->text('description')->nullable();
            $table->text('action_taken')->nullable();
            $table->string('status')->default('open');
            $table->timestamp('resolved_at')->nullable();
            $table->string('client_mutation_id')->nullable()->index();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plant_issues');
    }
};
