<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('mitra') && Schema::hasColumn('mitra', 'id_pengguna')) {
            try {
                DB::statement("ALTER TABLE `mitra` MODIFY `id_pengguna` CHAR(36) NULL");
            } catch (\Throwable $e) {
                // Ignore jika database selain MySQL atau sudah nullable
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tetap biarkan nullable untuk fleksibilitas sistem
    }
};
