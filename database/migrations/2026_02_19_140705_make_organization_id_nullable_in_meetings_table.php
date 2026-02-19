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
            // Drop the existing foreign key constraint first
            $table->dropForeign(['organization_id']);

            // Make organization_id nullable
            $table->uuid('organization_id')->nullable()->change();

            // Re-add the foreign key constraint with nullable support
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            // Drop the nullable foreign key
            $table->dropForeign(['organization_id']);

            // Make organization_id required again
            $table->uuid('organization_id')->nullable(false)->change();

            // Re-add the original foreign key constraint
            $table->foreign('organization_id')
                ->references('id')
                ->on('organizations')
                ->cascadeOnDelete();
        });
    }
};
