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
        Schema::create('meeting_events', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->string('type');
            $table->json('payload')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->index(['meeting_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_events');
    }
};
