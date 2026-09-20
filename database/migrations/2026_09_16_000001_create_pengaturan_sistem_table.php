<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pengaturan_sistem')) {
            Schema::create('pengaturan_sistem', function (Blueprint $table) {
                $table->id();
                $table->string('kunci')->unique();
                $table->text('nilai')->nullable();
                $table->string('keterangan')->nullable();
                $table->timestamps();
            });

            // Seed default settings
            $defaultSettings = [
                [
                    'kunci' => 'wa_gateway_status',
                    'nilai' => 'AKTIF',
                    'keterangan' => 'Status aktif pengiriman WhatsApp Gateway (AKTIF / NONAKTIF)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kunci' => 'wa_provider',
                    'nilai' => 'SIMULASI',
                    'keterangan' => 'Provider gateway: FONNTE, WABLAS, atau SIMULASI',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kunci' => 'wa_api_token',
                    'nilai' => '',
                    'keterangan' => 'Token API dari provider WhatsApp gateway',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kunci' => 'wa_sender_number',
                    'nilai' => '',
                    'keterangan' => 'Nomor WhatsApp pengirim / device bot',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kunci' => 'wa_template_setoran',
                    'nilai' => "🌿 *STRUK RESMI SETORAN SAMPAH* 🌿\n*SIRKULO — Bank Sampah Desa*\n------------------------------------------------\nHalo Bpk/Ibu *{nama}*,\nTerima kasih telah menyetorkan sampah Anda!\n\n📄 *No. Referensi*: {nomor_referensi}\n📅 *Waktu*: {waktu}\n📍 *Unit Loket*: {pos}\n📦 *Jenis Sampah*: {jenis_sampah}\n⚖️ *Berat*: {berat_kg} kg\n💵 *Nilai Sampah*: Rp {nominal}\n💳 *Metode*: {metode}\n⭐ *Poin Didapat*: +{poin_didapat} Poin\n\n💰 *Total Saldo Tabungan*: *Rp {saldo_baru}*\n⭐ *Total Poin*: *{poin_baru} Poin*\n------------------------------------------------\n_Mari bersama pilah sampah untuk desa yang bersih, lestari, dan berdaya ekonomi!_\n_Sistem Informasi SIRKULO Desa_",
                    'keterangan' => 'Template pesan notifikasi WhatsApp untuk transaksi setoran sampah',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'kunci' => 'wa_template_penarikan',
                    'nilai' => "💸 *STRUK PENCAIRAN SALDO TABUNGAN* 💸\n*SIRKULO — Bank Sampah Desa*\n------------------------------------------------\nHalo Bpk/Ibu *{nama}*,\nPenarikan saldo tabungan Anda berhasil diserahkan.\n\n📄 *No. Referensi*: {nomor_referensi}\n📅 *Waktu*: {waktu}\n📍 *Loket Pencairan*: {pos}\n💵 *Nominal Cair*: *Rp {nominal}*\n💳 *Metode*: Tunai Kasir Loket\n\n💰 *Sisa Saldo Tabungan*: *Rp {saldo_baru}*\n⭐ *Sisa Poin Anda*: *{poin_baru} Poin*\n------------------------------------------------\n_Bukti transaksi ini diterbitkan otomatis oleh sistem resmi SIRKULO Desa._",
                    'keterangan' => 'Template pesan notifikasi WhatsApp untuk transaksi penarikan saldo tunai',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('pengaturan_sistem')->insert($defaultSettings);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_sistem');
    }
};
