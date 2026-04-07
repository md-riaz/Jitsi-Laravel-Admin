<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('organizations')
            ->where('require_jwt', false)
            ->update([
                'require_jwt' => true,
                'jwt_expiry_minutes' => 120,
            ]);
    }

    public function down(): void
    {
        DB::table('organizations')
            ->where('require_jwt', true)
            ->update([
                'require_jwt' => false,
            ]);
    }
};
