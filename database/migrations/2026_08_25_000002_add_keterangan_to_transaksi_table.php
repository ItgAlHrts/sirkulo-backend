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
        if (Schema::hasTable('transaksi') && !Schema::hasColumn('transaksi', 'keterangan')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->string('keterangan')->nullable()->after('nomor_referensi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transaksi') && Schema::hasColumn('transaksi', 'keterangan')) {
            Schema::table('transaksi', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};
