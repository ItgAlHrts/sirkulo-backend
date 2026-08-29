<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kritik_saran')) {
            Schema::create('kritik_saran', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('id_pengguna');
                $table->string('kategori');
                $table->text('pesan');
                $table->text('jawaban')->nullable();
                $table->uuid('id_mitra')->nullable();
                $table->timestamp('dijawab_pada')->nullable();
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();

                $table->foreign('id_pengguna')->references('id')->on('pengguna')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kritik_saran');
    }
};
