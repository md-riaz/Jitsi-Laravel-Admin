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
        Schema::create('meetings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('room_name')->unique();
            $table->dateTimeTz('start_at');
            $table->dateTimeTz('end_at');
            $table->string('timezone');
            $table->unsignedInteger('join_early_minutes')->default(10);
            $table->unsignedInteger('join_late_minutes')->default(60);
            $table->enum('visibility', ['invite_only', 'link_anyone', 'org_only'])->default('invite_only');
            $table->enum('status', ['scheduled', 'live', 'ended', 'canceled'])->default('scheduled');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->index(['organization_id', 'start_at']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
