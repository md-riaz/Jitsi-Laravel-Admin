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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0.00);
            $table->string('billing_cycle')->default('monthly'); // monthly, yearly, one-time
            $table->integer('max_users')->nullable();            // null = unlimited
            $table->integer('max_meeting_duration')->nullable(); // in minutes, null = unlimited
            $table->integer('recording_storage_gb')->nullable(); // GB, null = unlimited
            $table->integer('concurrent_meetings')->nullable();  // null = unlimited
            $table->boolean('is_active')->default(true);
            $table->integer('trial_days')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
