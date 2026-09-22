<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_reference')->nullable()->index();
            $table->string('merchant_reference')->nullable()->index();
            $table->foreignUuid('payment_channel_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pay_code')->nullable();
            $table->string('qr_url')->nullable();
            $table->string('checkout_url')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('fee', 12, 2)->default(0);
            $table->string('status');
            $table->timestamp('provider_expired_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
