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
        Schema::create('meeting_invites', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('meeting_id');
            $table->string('email');
            $table->string('token_hash');
            $table->dateTimeTz('expires_at');
            $table->dateTimeTz('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->cascadeOnDelete();
            $table->index(['meeting_id', 'email']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_invites');
    }
};
