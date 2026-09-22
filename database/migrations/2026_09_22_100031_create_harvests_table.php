<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('harvests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('planting_cycle_id')->constrained()->cascadeOnDelete();
            $table->date('harvest_date');
            $table->decimal('quantity', 12, 2);
            $table->string('unit');
            $table->string('grade')->nullable();
            $table->string('usage_type')->nullable();
            $table->decimal('sale_value', 12, 2)->nullable();
            $table->decimal('waste_quantity', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('client_mutation_id')->nullable()->index();
            $table->unsignedBigInteger('server_version')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('harvests');
    }
};
