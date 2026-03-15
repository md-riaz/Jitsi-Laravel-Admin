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
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->timestamp('subscription_starts_at')->nullable();
            $table->timestamp('subscription_ends_at')->nullable();
            $table->string('subscription_status')->default('active'); // active, trialing, suspended, expired
            $table->string('logo_path')->nullable();
            $table->string('primary_color', 20)->nullable();    // e.g. #6366f1
            $table->string('secondary_color', 20)->nullable();  // e.g. #a5b4fc
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_plan_id');
            $table->dropColumn([
                'subscription_starts_at',
                'subscription_ends_at',
                'subscription_status',
                'logo_path',
                'primary_color',
                'secondary_color',
            ]);
        });
    }
};
