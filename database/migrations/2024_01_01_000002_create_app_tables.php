<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sampah', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->bigInteger('harga_per_kg');
            $table->string('ikon');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('mitra', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pengguna');      // hapus ->unique() agar satu akun bisa punya banyak pos
            $table->string('nama');
            $table->string('alamat');
            $table->double('lintang')->default(0);
            $table->double('bujur')->default(0);
            $table->string('jam_buka');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->foreign('id_pengguna')->references('id')->on('pengguna')->onDelete('cascade');
        });

        Schema::create('transaksi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pengguna');
            $table->uuid('id_mitra');
            $table->string('jenis'); // SETORAN atau PENARIKAN
            $table->string('status')->default('SELESAI');
            $table->bigInteger('jumlah_total');
            $table->integer('poin_didapat')->default(0);
            $table->string('nomor_referensi')->unique();
            $table->string('keterangan')->nullable(); // jenis_sampah, berat, dll
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->foreign('id_pengguna')->references('id')->on('pengguna')->onDelete('cascade');
            $table->foreign('id_mitra')->references('id')->on('mitra')->onDelete('cascade');
        });

        Schema::create('edukasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('judul');
            $table->string('kategori');
            $table->longText('konten');
            $table->string('url_gambar');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('notifikasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('id_pengguna');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('jenis');
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
            $table->foreign('id_pengguna')->references('id')->on('pengguna')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('edukasi');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('mitra');
        Schema::dropIfExists('kategori_sampah');
    }
};
