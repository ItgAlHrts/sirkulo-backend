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
        // No-op karena tabel mitra sudah disesuaikan pada migrasi selanjutnya
    }

    public function down(): void
    {
        // No-op
    }
};
