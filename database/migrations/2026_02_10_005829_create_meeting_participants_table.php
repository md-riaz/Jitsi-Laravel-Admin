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
        Schema::create('meeting_participants', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('display_name')->nullable();
            $table->enum('role', ['host', 'cohost', 'participant'])->default('participant');
            $table->enum('invite_status', ['invited', 'accepted', 'declined', 'bounced'])->default('invited');
            $table->dateTimeTz('joined_at')->nullable();
            $table->dateTimeTz('left_at')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->unique(['meeting_id', 'email']);
            $table->index(['meeting_id', 'role']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_participants');
    }
};
