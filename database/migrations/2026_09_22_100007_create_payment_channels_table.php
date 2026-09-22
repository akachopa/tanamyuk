<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->string('provider_code');
            $table->string('name');
            $table->string('category')->nullable();
            $table->decimal('fee_flat', 12, 2)->default(0);
            $table->decimal('fee_percent', 8, 4)->default(0);
            $table->decimal('min_amount', 12, 2)->nullable();
            $table->decimal('max_amount', 12, 2)->nullable();
            $table->string('icon_url')->nullable();
            $table->boolean('is_enabled_by_provider')->default(true);
            $table->boolean('is_enabled_by_admin')->default(true);
            $table->json('provider_payload')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->unique(['provider', 'provider_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_channels');
    }
};
