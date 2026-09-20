<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('log_whatsapp')) {
            Schema::create('log_whatsapp', function (Blueprint $table) {
                $table->id();
                $table->uuid('id_pengguna')->nullable()->index();
                $table->string('nomor_tujuan', 30)->index();
                $table->string('jenis_transaksi', 40)->default('INFO'); // SETORAN, PENARIKAN, UJI_COBA, dll
                $table->text('isi_pesan');
                $table->string('status', 25)->default('SIMULASI'); // BERHASIL, GAGAL, SIMULASI
                $table->text('respon_gateway')->nullable();
                $table->string('provider', 30)->default('SIMULASI');
                $table->timestamp('dibuat_pada')->nullable()->useCurrent();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('log_whatsapp');
    }
};
