<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        date_default_timezone_set('Asia/Jakarta');
        \Carbon\Carbon::setLocale('id');
        config(['app.timezone' => 'Asia/Jakarta']);

        // Auto-ensure struktur tabel transaksi memiliki kolom 'keterangan'
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('transaksi') && !\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'keterangan')) {
                \Illuminate\Support\Facades\Schema::table('transaksi', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('keterangan')->nullable()->after('nomor_referensi');
                });
            }

            // Auto-ensure kolom harga_pengepul di tabel kategori_sampah
            if (\Illuminate\Support\Facades\Schema::hasTable('kategori_sampah') && !\Illuminate\Support\Facades\Schema::hasColumn('kategori_sampah', 'harga_pengepul')) {
                \Illuminate\Support\Facades\Schema::table('kategori_sampah', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->bigInteger('harga_pengepul')->nullable()->after('harga_per_kg');
                });
            }
        } catch (\Exception $e) {
            // ignore
        }
    }
}
