<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membersihkan tabel-tabel legacy sisa migrasi awal yang sudah tidak digunakan.
     */
    public function up(): void
    {
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('token_reset_kata_sandi');
        Schema::dropIfExists('sesi');
        Schema::dropIfExists('log_whatsapp');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
