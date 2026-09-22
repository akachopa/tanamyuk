<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreign('source_order_id')->references('id')->on('orders')->nullOnDelete();
        });

        Schema::table('planting_cycles', function (Blueprint $table) {
            $table->foreign('current_stage_id')->references('id')->on('cycle_stages')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropForeign(['source_order_id']);
        });

        Schema::table('planting_cycles', function (Blueprint $table) {
            $table->dropForeign(['current_stage_id']);
        });
    }
};
