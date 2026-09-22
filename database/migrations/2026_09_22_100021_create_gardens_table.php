<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gardens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location_type')->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();
            $table->decimal('length_m', 10, 2)->nullable();
            $table->decimal('width_m', 10, 2)->nullable();
            $table->decimal('sunlight_hours', 5, 2)->nullable();
            $table->string('shade_level')->nullable();
            $table->string('water_source')->nullable();
            $table->string('drainage_level')->nullable();
            $table->text('notes')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('client_created_at')->nullable();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gardens');
    }
};
