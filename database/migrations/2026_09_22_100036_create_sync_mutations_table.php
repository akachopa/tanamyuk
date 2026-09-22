<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_mutations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('device_id')->nullable()->index();
            $table->string('client_mutation_id')->index();
            $table->string('entity_type');
            $table->uuid('entity_id')->nullable()->index();
            $table->string('operation');
            $table->string('payload_hash')->nullable();
            $table->string('processing_status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unique(['user_id', 'client_mutation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_mutations');
    }
};
