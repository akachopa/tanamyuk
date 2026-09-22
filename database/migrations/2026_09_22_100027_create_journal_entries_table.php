<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('garden_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('planting_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('activity_type');
            $table->date('entry_date');
            $table->string('plant_condition')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->string('client_mutation_id')->nullable()->index();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
