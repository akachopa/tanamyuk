<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('anonymous_session_id')->nullable()->index();
            $table->string('event_name');
            $table->json('properties')->nullable();
            $table->timestamp('occurred_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_events');
    }
};
