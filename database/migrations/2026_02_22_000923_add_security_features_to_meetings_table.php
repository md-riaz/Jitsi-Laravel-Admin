<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Password protection
            $table->string('password')->nullable()->after('status');

            // Lobby settings
            $table->boolean('lobby_enabled')->default(true)->after('password');

            // Guest policy
            $table->boolean('allow_guests')->default(true)->after('lobby_enabled');

            // Participant limit
            $table->unsignedInteger('max_participants')->nullable()->after('allow_guests');

            // IP restriction
            $table->text('allowed_ips')->nullable()->after('max_participants');
            $table->boolean('ip_restriction_enabled')->default(false)->after('allowed_ips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'lobby_enabled',
                'allow_guests',
                'max_participants',
                'allowed_ips',
                'ip_restriction_enabled',
            ]);
        });
    }
};
