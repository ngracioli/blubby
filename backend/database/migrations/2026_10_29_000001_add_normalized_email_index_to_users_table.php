<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $identity = "lower(btrim(email, E' \\t\\n\\r\\013\\014'))";
        $duplicates = DB::table('users')->selectRaw($identity)->groupByRaw($identity)->havingRaw('count(*) > 1')->exists();
        $nonAscii = DB::table('users')->whereRaw('octet_length(email) <> char_length(email)')->exists();

        if ($duplicates || $nonAscii) {
            throw new RuntimeException('A migração exige revisão de identidades de e-mail legadas, sem alteração automática de contas.');
        }

        DB::statement("CREATE UNIQUE INDEX users_email_normalized_unique ON users ({$identity})");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_email_normalized_unique');
    }
};
