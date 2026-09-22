<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('password');
            $table->string('avatar_path')->nullable()->after('phone');
            $table->string('timezone')->default('Asia/Jakarta')->after('avatar_path');
            $table->string('experience_level')->nullable()->after('timezone');
            $table->unsignedInteger('daily_available_minutes')->nullable()->after('experience_level');
            $table->string('primary_goal')->nullable()->after('daily_available_minutes');
            $table->string('status')->default('active')->after('primary_goal');
            $table->timestamp('last_login_at')->nullable()->after('status');
            $table->boolean('is_admin')->default(false)->after('last_login_at');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'phone',
                'avatar_path',
                'timezone',
                'experience_level',
                'daily_available_minutes',
                'primary_goal',
                'status',
                'last_login_at',
                'is_admin',
            ]);
        });
    }
};
