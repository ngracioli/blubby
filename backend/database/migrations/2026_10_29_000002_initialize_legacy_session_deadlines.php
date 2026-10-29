<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('personal_access_tokens')->whereNull('expires_at')->update([
            'expires_at' => DB::raw("COALESCE(last_used_at, created_at) + INTERVAL '30 days'"),
        ]);
    }

    public function down(): void
    {
        // Mantém os prazos históricos para não restaurar credenciais sem expiração.
    }
};
