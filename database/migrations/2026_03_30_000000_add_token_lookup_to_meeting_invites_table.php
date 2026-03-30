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
        Schema::table('meeting_invites', function (Blueprint $table) {
            $table->string('token_lookup')->nullable()->index()->after('token_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_invites', function (Blueprint $table) {
            $table->dropColumn('token_lookup');
        });
    }
};
