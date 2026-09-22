<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('planting_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('cycle_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('source_template_task_id')->nullable()->index();
            $table->string('task_type');
            $table->string('title');
            $table->text('instruction')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('due_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('postponed_to')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
