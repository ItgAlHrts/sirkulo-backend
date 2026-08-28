<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hapus constraint unique pada id_pengguna di tabel mitra,
     * agar satu akun Mitra bisa memiliki lebih dari satu Pos Bank Sampah.
     */
    public function up(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            // Drop unique constraint dulu
            $table->dropUnique(['id_pengguna']);
        });
    }

    public function down(): void
    {
        Schema::table('mitra', function (Blueprint $table) {
            $table->unique('id_pengguna');
        });
    }
};
