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
        Schema::table('meetings', function (Blueprint $table): void {
            $table->unsignedInteger('active_participant_count')->default(0)->after('status');
            $table->dateTimeTz('actual_started_at')->nullable()->after('active_participant_count');
            $table->dateTimeTz('actual_ended_at')->nullable()->after('actual_started_at');
            $table->dateTimeTz('last_activity_at')->nullable()->after('actual_ended_at');
            $table->string('ended_reason')->nullable()->after('last_activity_at');

            $table->index(['status', 'active_participant_count']);
            $table->index('actual_ended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table): void {
            $table->dropIndex(['status', 'active_participant_count']);
            $table->dropIndex(['actual_ended_at']);
            $table->dropColumn([
                'active_participant_count',
                'actual_started_at',
                'actual_ended_at',
                'last_activity_at',
                'ended_reason',
            ]);
        });
    }
};
