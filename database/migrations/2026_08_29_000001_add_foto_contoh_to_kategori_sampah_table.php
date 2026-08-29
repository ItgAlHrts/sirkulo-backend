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
        if (Schema::hasTable('kategori_sampah') && !Schema::hasColumn('kategori_sampah', 'foto_contoh')) {
            Schema::table('kategori_sampah', function (Blueprint $table) {
                $table->json('foto_contoh')->nullable()->after('ikon');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kategori_sampah') && Schema::hasColumn('kategori_sampah', 'foto_contoh')) {
            Schema::table('kategori_sampah', function (Blueprint $table) {
                $table->dropColumn('foto_contoh');
            });
        }
    }
};
