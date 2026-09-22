<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('garden_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('planting_cycle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignUuid('expense_category_id')->constrained()->restrictOnDelete();
            $table->date('expense_date');
            $table->string('description')->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('total', 12, 2);
            $table->string('receipt_image_path')->nullable();
            $table->string('client_mutation_id')->nullable()->index();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
