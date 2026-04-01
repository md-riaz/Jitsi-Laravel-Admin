<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete()->after('slug');
        });

        $organizations = DB::table('organizations')
            ->whereNull('owner_id')
            ->get(['id']);

        foreach ($organizations as $organization) {
            $ownerId = DB::table('organization_user')
                ->where('organization_id', $organization->id)
                ->where('role', 'admin')
                ->orderBy('created_at')
                ->value('user_id');

            if ($ownerId) {
                DB::table('organizations')
                    ->where('id', $organization->id)
                    ->update(['owner_id' => $ownerId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_id');
        });
    }
};
