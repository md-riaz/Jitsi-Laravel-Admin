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
        Schema::create('recurrence_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('meeting_id')->constrained('meetings')->cascadeOnDelete();

            // Recurrence pattern
            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly'])->default('weekly');
            $table->unsignedInteger('interval')->default(1); // Every N days/weeks/months/years

            // Recurrence end condition (either count OR until_date, not both)
            $table->unsignedInteger('count')->nullable(); // Number of occurrences
            $table->dateTimeTz('until_date')->nullable(); // End date

            // Advanced rules
            $table->string('by_day')->nullable(); // For weekly: MO,TU,WE,TH,FR,SA,SU
            $table->string('by_month_day')->nullable(); // For monthly: 1,15,30
            $table->json('exceptions')->nullable(); // Dates to exclude

            $table->timestamps();

            $table->index('meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrence_rules');
    }
};
