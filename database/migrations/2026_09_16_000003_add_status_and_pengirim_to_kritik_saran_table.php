<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kritik_saran')) {
            Schema::table('kritik_saran', function (Blueprint $table) {
                if (!Schema::hasColumn('kritik_saran', 'status')) {
                    $table->string('status', 30)->default('MENUNGGU')->after('jawaban');
                }
                if (!Schema::hasColumn('kritik_saran', 'pengirim')) {
                    $table->string('pengirim', 30)->default('NASABAH')->after('kategori');
                }
            });

            // Update status ke 'DIJAWAB' untuk data yang sudah memiliki jawaban
            \Illuminate\Support\Facades\DB::table('kritik_saran')
                ->whereNotNull('jawaban')
                ->where('jawaban', '!=', '')
                ->update(['status' => 'DIJAWAB']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kritik_saran')) {
            Schema::table('kritik_saran', function (Blueprint $table) {
                if (Schema::hasColumn('kritik_saran', 'status')) {
                    $table->dropColumn('status');
                }
                if (Schema::hasColumn('kritik_saran', 'pengirim')) {
                    $table->dropColumn('pengirim');
                }
            });
        }
    }
};
