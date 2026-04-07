<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $meetings = DB::table('meetings')
            ->select('meetings.id', 'users.organization_id')
            ->join('users', 'users.id', '=', 'meetings.created_by')
            ->whereNull('meetings.organization_id')
            ->whereNotNull('users.organization_id')
            ->get();

        foreach ($meetings as $meeting) {
            DB::table('meetings')
                ->where('id', $meeting->id)
                ->update([
                    'organization_id' => $meeting->organization_id,
                ]);
        }
    }

    public function down(): void
    {
        $meetings = DB::table('meetings')
            ->select('meetings.id')
            ->join('users', 'users.id', '=', 'meetings.created_by')
            ->whereColumn('meetings.organization_id', 'users.organization_id')
            ->get();

        foreach ($meetings as $meeting) {
            DB::table('meetings')
                ->where('id', $meeting->id)
                ->update([
                    'organization_id' => null,
                ]);
        }
    }
};
