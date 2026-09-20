<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pos_kategori_sampah')) {
            Schema::create('pos_kategori_sampah', function (Blueprint $table) {
                $table->id();
                $table->char('id_mitra', 36);
                $table->char('id_kategori', 36);
                $table->timestamps();

                $table->unique(['id_mitra', 'id_kategori']);
                $table->foreign('id_mitra')->references('id')->on('mitra')->onDelete('cascade');
                $table->foreign('id_kategori')->references('id')->on('kategori_sampah')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_kategori_sampah');
    }
};
