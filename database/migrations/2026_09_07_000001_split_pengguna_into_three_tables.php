<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buat Tabel 'admin' (Khusus Pengurus Kantor Desa / BUMDes)
        if (!Schema::hasTable('admin')) {
            Schema::create('admin', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama');
                $table->string('email')->unique();
                $table->string('kata_sandi');
                $table->string('telepon')->nullable();
                $table->string('peran')->default('ADMIN');
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
            });
        }

        // 2. Buat Tabel 'petugas_mitra' (Khusus Petugas Pos Loket Timbangan Dusun/RW)
        if (!Schema::hasTable('petugas_mitra')) {
            Schema::create('petugas_mitra', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama');
                $table->string('email')->unique();
                $table->string('kata_sandi');
                $table->string('telepon');
                $table->string('alamat')->nullable();
                $table->bigInteger('saldo')->default(0);
                $table->string('foto_url')->nullable();
                $table->string('peran')->default('MITRA');
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
            });
        }

        // 3. Buat Tabel 'nasabah' (Khusus Warga/Nasabah Penabung Sampah)
        if (!Schema::hasTable('nasabah')) {
            Schema::create('nasabah', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('nama');
                $table->string('email')->unique();
                $table->string('kata_sandi');
                $table->string('telepon');
                $table->string('alamat')->nullable();
                $table->bigInteger('saldo')->default(0);
                $table->integer('poin')->default(0);
                $table->string('foto_url')->nullable();
                $table->string('peran')->default('NASABAH');
                $table->string('otp_reset')->nullable();
                $table->timestamp('kadaluarsa_otp')->nullable();
                $table->timestamp('dibuat_pada')->nullable();
                $table->timestamp('diperbarui_pada')->nullable();
            });
        }

        // 4. Migrasi Data dari tabel 'pengguna' jika ada
        if (Schema::hasTable('pengguna')) {
            // Salin Admin
            DB::statement("
                INSERT IGNORE INTO admin (id, nama, email, kata_sandi, telepon, peran, dibuat_pada, diperbarui_pada)
                SELECT id, nama, email, kata_sandi, telepon, 'ADMIN', dibuat_pada, diperbarui_pada
                FROM pengguna
                WHERE peran = 'ADMIN'
            ");

            // Salin Petugas Mitra
            DB::statement("
                INSERT IGNORE INTO petugas_mitra (id, nama, email, kata_sandi, telepon, alamat, saldo, foto_url, peran, dibuat_pada, diperbarui_pada)
                SELECT id, nama, email, kata_sandi, telepon, alamat, saldo, foto_url, 'MITRA', dibuat_pada, diperbarui_pada
                FROM pengguna
                WHERE peran = 'MITRA'
            ");

            // Salin Nasabah
            DB::statement("
                INSERT IGNORE INTO nasabah (id, nama, email, kata_sandi, telepon, alamat, saldo, poin, foto_url, peran, otp_reset, kadaluarsa_otp, dibuat_pada, diperbarui_pada)
                SELECT id, nama, email, kata_sandi, telepon, alamat, saldo, poin, foto_url, 'NASABAH', otp_reset, kadaluarsa_otp, dibuat_pada, diperbarui_pada
                FROM pengguna
                WHERE peran = 'NASABAH' OR peran IS NULL OR peran = ''
            ");
        }

        // 5. Pastikan default akun ada jika database kosong
        if (DB::table('admin')->count() === 0) {
            DB::table('admin')->insert([
                'id'              => (string) \Illuminate\Support\Str::uuid(),
                'nama'            => 'Administrator Desa',
                'email'           => 'admin@sirkulo.id',
                'kata_sandi'      => \Illuminate\Support\Facades\Hash::make('admindesa2026'),
                'telepon'         => '081234567890',
                'peran'           => 'ADMIN',
                'dibuat_pada'     => now(),
                'diperbarui_pada' => now(),
            ]);
        }

        // 6. Lepas foreign key lama ke 'pengguna' pada tabel-tabel terkait agar tidak error constraint
        $this->dropForeignKeyIfExists('mitra', 'mitra_id_pengguna_foreign');
        $this->dropForeignKeyIfExists('transaksi', 'transaksi_id_pengguna_foreign');
        $this->dropForeignKeyIfExists('notifikasi', 'notifikasi_id_pengguna_foreign');
        $this->dropForeignKeyIfExists('kritik_saran', 'kritik_saran_id_pengguna_foreign');
        $this->dropForeignKeyIfExists('sesi', 'sesi_id_pengguna_foreign');

        // 7. Bersihkan tabel personal_access_tokens agar token lama tidak bentrok model
        if (Schema::hasTable('personal_access_tokens')) {
            try {
                DB::table('personal_access_tokens')->truncate();
            } catch (\Throwable $e) {
                // Ignore jika truncate diblokir
                DB::table('personal_access_tokens')->delete();
            }
        }
    }

    private function dropForeignKeyIfExists(string $table, string $foreignKeyName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        } catch (\Throwable $e) {
            // Foreign key mungkin sudah tidak ada atau bernama lain
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
        Schema::dropIfExists('petugas_mitra');
        Schema::dropIfExists('nasabah');
    }
};
