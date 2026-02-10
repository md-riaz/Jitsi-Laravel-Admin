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
            $table->dateTimeTz('start_at')->nullable()->change();
            $table->dateTimeTz('end_at')->nullable()->change();
            $table->string('timezone')->default('UTC')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dateTimeTz('start_at')->nullable(false)->change();
            $table->dateTimeTz('end_at')->nullable(false)->change();
            $table->string('timezone')->change();
        });
    }
};
