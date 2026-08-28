<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengguna', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('email')->unique();
            $table->string('foto_url')->nullable();
            $table->string('kata_sandi');
            $table->string('telepon');
            $table->string('alamat')->nullable();
            $table->bigInteger('saldo')->default(0);
            $table->integer('poin')->default(0);
            $table->string('peran')->default('NASABAH');
            $table->string('otp_reset')->nullable();
            $table->timestamp('kadaluarsa_otp')->nullable();
            $table->timestamp('dibuat_pada')->nullable();
            $table->timestamp('diperbarui_pada')->nullable();
        });

        Schema::create('token_reset_kata_sandi', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('dibuat_pada')->nullable();
        });

        Schema::create('sesi', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignUuid('id_pengguna')->nullable()->index();
            $table->string('alamat_ip', 45)->nullable();
            $table->text('agen_pengguna')->nullable();
            $table->longText('muatan');
            $table->integer('aktivitas_terakhir')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna');
        Schema::dropIfExists('token_reset_kata_sandi');
        Schema::dropIfExists('sesi');
    }
};
