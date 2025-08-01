<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTutupBukuStoredProcedures extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create sequence for document numbers
        DB::unprepared("
            IF NOT EXISTS (SELECT * FROM sys.sequences WHERE name = 'NoBuktiSequence')
            BEGIN
                CREATE SEQUENCE NoBuktiSequence
                    START WITH 1
                    INCREMENT BY 1;
            END
        ");

        // Install stored procedures
        $procedures = [
            'sp_ProsesAktiva',
            'sp_HitungUlangTransaksi',
            'sp_HitungUlangAktiva',
            'sp_ProsesLabaRugi',
            'sp_HitungUlangAktivaFK',
            'sp_CreateRLJournal',
            'sp_GetLabaIni'
        ];

        foreach ($procedures as $procedure) {
            $sql = file_get_contents(database_path("sql/{$procedure}.sql"));
            DB::unprepared($sql);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop stored procedures
        $procedures = [
            'sp_ProsesAktiva',
            'sp_HitungUlangTransaksi',
            'sp_HitungUlangAktiva',
            'sp_ProsesLabaRugi',
            'sp_HitungUlangAktivaFK',
            'sp_CreateRLJournal',
            'sp_GetLabaIni'
        ];

        foreach ($procedures as $procedure) {
            DB::unprepared("DROP PROCEDURE IF EXISTS [{$procedure}]");
        }

        // Drop sequence
        DB::unprepared("DROP SEQUENCE IF EXISTS NoBuktiSequence");
    }
} 