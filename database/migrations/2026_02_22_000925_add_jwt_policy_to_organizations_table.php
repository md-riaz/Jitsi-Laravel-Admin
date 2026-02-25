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
        Schema::table('organizations', function (Blueprint $table) {
            // JWT policy for organization meetings
            $table->boolean('require_jwt')->default(false)->after('slug');
            $table->unsignedInteger('jwt_expiry_minutes')->default(120)->after('require_jwt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['require_jwt', 'jwt_expiry_minutes']);
        });
    }
};
