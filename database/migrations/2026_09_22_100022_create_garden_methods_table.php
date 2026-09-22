<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('garden_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('garden_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('cultivation_method_id')->constrained()->cascadeOnDelete();
            $table->unique(['garden_id', 'cultivation_method_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garden_methods');
    }
};
