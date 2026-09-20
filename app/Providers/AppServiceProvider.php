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

        // Paksa skema HTTPS jika diakses melalui ngrok, Cloudflare, tunnel, atau reverse proxy HTTPS
        if (
            request()->isSecure() ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
            (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on') ||
            str_contains(request()->getHost(), 'ngrok') ||
            str_contains(request()->getHost(), 'trycloudflare') ||
            app()->environment('production')
        ) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-ensure struktur tabel transaksi memiliki kolom lengkap untuk struk transaksi
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('transaksi')) {
                \Illuminate\Support\Facades\Schema::table('transaksi', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'keterangan')) {
                        $table->string('keterangan')->nullable()->after('nomor_referensi');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'jenis_sampah')) {
                        $table->string('jenis_sampah')->nullable()->after('keterangan');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'berat_kg')) {
                        $table->decimal('berat_kg', 8, 2)->nullable()->after('jenis_sampah');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'harga_per_kg')) {
                        $table->bigInteger('harga_per_kg')->nullable()->after('berat_kg');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'metode_pembayaran')) {
                        $table->string('metode_pembayaran', 50)->default('SALDO')->after('harga_per_kg');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'catatan')) {
                        $table->text('catatan')->nullable()->after('metode_pembayaran');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'saldo_sebelumnya')) {
                        $table->bigInteger('saldo_sebelumnya')->nullable()->after('catatan');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'saldo_baru')) {
                        $table->bigInteger('saldo_baru')->nullable()->after('saldo_sebelumnya');
                    }
                });
            }
        } catch (\Throwable $e) {}

        // Auto-ensure struktur tabel notifikasi agar sinkron antara mobile dan web
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('notifikasi')) {
                \Illuminate\Support\Facades\Schema::table('notifikasi', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('notifikasi', 'deskripsi')) {
                        $table->text('deskripsi')->nullable()->after('judul');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('notifikasi', 'isi')) {
                        $table->text('isi')->nullable()->after('deskripsi');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('notifikasi', 'sudah_dibaca')) {
                        $table->boolean('sudah_dibaca')->default(false)->after('jenis');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('notifikasi', 'jenis')) {
                        $table->string('jenis', 50)->default('INFO')->after('judul');
                    }
                });

                // Drop obsolete foreign key if present on old pengguna table
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `notifikasi` DROP FOREIGN KEY `notifikasi_id_pengguna_foreign`");
                } catch (\Throwable $e) {}

                // Sinkronkan isi dan deskripsi bila salah satu bernilai null
                try {
                    \Illuminate\Support\Facades\DB::statement("UPDATE `notifikasi` SET `isi` = `deskripsi` WHERE `isi` IS NULL AND `deskripsi` IS NOT NULL");
                    \Illuminate\Support\Facades\DB::statement("UPDATE `notifikasi` SET `deskripsi` = `isi` WHERE `deskripsi` IS NULL AND `isi` IS NOT NULL");
                } catch (\Throwable $e) {}
            } else {
                \Illuminate\Support\Facades\DB::statement("
                    CREATE TABLE IF NOT EXISTS `notifikasi` (
                        `id`              CHAR(36)     NOT NULL,
                        `id_pengguna`     CHAR(36)     NOT NULL,
                        `judul`           VARCHAR(255) NOT NULL,
                        `deskripsi`       TEXT         NULL,
                        `isi`             TEXT         NULL,
                        `jenis`           VARCHAR(50)  NOT NULL DEFAULT 'INFO',
                        `sudah_dibaca`    TINYINT(1)   NOT NULL DEFAULT 0,
                        `dibuat_pada`     TIMESTAMP    NULL,
                        `diperbarui_pada` TIMESTAMP    NULL,
                        PRIMARY KEY (`id`),
                        INDEX `idx_notif_pengguna` (`id_pengguna`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
        } catch (\Throwable $e) {}

        try {
            // Auto-ensure kolom url_video di tabel edukasi (untuk embed YouTube)
            if (\Illuminate\Support\Facades\Schema::hasTable('edukasi') && !\Illuminate\Support\Facades\Schema::hasColumn('edukasi', 'url_video')) {
                \Illuminate\Support\Facades\Schema::table('edukasi', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('url_video', 500)->nullable()->after('url_gambar');
                });
            }

            // Auto-ensure kolom harga_pengepul di tabel kategori_sampah
            if (\Illuminate\Support\Facades\Schema::hasTable('kategori_sampah') && !\Illuminate\Support\Facades\Schema::hasColumn('kategori_sampah', 'harga_pengepul')) {
                \Illuminate\Support\Facades\Schema::table('kategori_sampah', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->bigInteger('harga_pengepul')->nullable()->after('harga_per_kg');
                });
            }

            // Auto-ensure kolom foto_contoh di tabel kategori_sampah
            if (\Illuminate\Support\Facades\Schema::hasTable('kategori_sampah') && !\Illuminate\Support\Facades\Schema::hasColumn('kategori_sampah', 'foto_contoh')) {
                \Illuminate\Support\Facades\Schema::table('kategori_sampah', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->json('foto_contoh')->nullable()->after('ikon');
                });
            }

            // Auto-ensure kolom kode_user permanen di tabel nasabah
            if (\Illuminate\Support\Facades\Schema::hasTable('nasabah') && !\Illuminate\Support\Facades\Schema::hasColumn('nasabah', 'kode_user')) {
                \Illuminate\Support\Facades\Schema::table('nasabah', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('kode_user', 20)->nullable()->unique()->after('id');
                });
            }

            // Backfill: berikan kode_user permanen kepada nasabah yang belum punya
            // (nasabah lama sebelum fitur ini ditambahkan)
            if (\Illuminate\Support\Facades\Schema::hasTable('nasabah') && \Illuminate\Support\Facades\Schema::hasColumn('nasabah', 'kode_user')) {
                $tanpaKode = \Illuminate\Support\Facades\DB::table('nasabah')
                    ->whereNull('kode_user')
                    ->orWhere('kode_user', '')
                    ->orderBy('dibuat_pada', 'asc')
                    ->orderBy('id', 'asc')
                    ->get(['id']);

                if ($tanpaKode->isNotEmpty()) {
                    // Cari nomor tertinggi yang sudah terpakai
                    $maxKode = \Illuminate\Support\Facades\DB::table('nasabah')
                        ->whereNotNull('kode_user')
                        ->where('kode_user', 'like', 'SRKL%')
                        ->orderByRaw('CAST(SUBSTRING(kode_user, 5) AS UNSIGNED) DESC')
                        ->value('kode_user');

                    $nextNum = 1;
                    if ($maxKode && preg_match('/^SRKL(\d+)$/', $maxKode, $m)) {
                        $nextNum = (int) $m[1] + 1;
                    }

                    foreach ($tanpaKode as $row) {
                        $kode = 'SRKL' . sprintf('%03d', $nextNum++);
                        \Illuminate\Support\Facades\DB::table('nasabah')
                            ->where('id', $row->id)
                            ->update(['kode_user' => $kode]);
                    }
                }
            }

            // Auto-drop foreign key lama yang masih mengarah ke tabel 'pengguna'
            if (\Illuminate\Support\Facades\Schema::hasTable('mitra')) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `mitra` DROP FOREIGN KEY `mitra_id_pengguna_foreign`");
                } catch (\Throwable $e) {
                    // ignore jika sudah tidak ada
                }

                // Cari dan lepas semua foreign key lain yang masih merujuk ke tabel pengguna lama
                try {
                    $oldFks = \Illuminate\Support\Facades\DB::select("
                        SELECT CONSTRAINT_NAME, TABLE_NAME
                        FROM information_schema.KEY_COLUMN_USAGE
                        WHERE TABLE_SCHEMA = DATABASE()
                        AND REFERENCED_TABLE_NAME = 'pengguna'
                    ");
                    foreach ($oldFks as $fk) {
                        try {
                            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `{$fk->TABLE_NAME}` DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
                        } catch (\Throwable $e) {}
                    }
                } catch (\Throwable $e) {}

                // Auto-ensure kolom id_pengguna di tabel mitra bersifat nullable (agar pos & petugas bisa decoupled)
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `mitra` MODIFY `id_pengguna` CHAR(36) NULL");
                } catch (\Throwable $e) {}
            }

            // Auto-ensure tabel pivot pos_kategori_sampah untuk jenis sampah per pos
            if (\Illuminate\Support\Facades\Schema::hasTable('mitra') && \Illuminate\Support\Facades\Schema::hasTable('kategori_sampah')) {
                if (!\Illuminate\Support\Facades\Schema::hasTable('pos_kategori_sampah')) {
                    \Illuminate\Support\Facades\Schema::create('pos_kategori_sampah', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->id();
                        $table->char('id_mitra', 36);
                        $table->char('id_kategori', 36);
                        $table->timestamps();
                        $table->unique(['id_mitra', 'id_kategori']);
                    });
                }

                // Backfill hanya saat pertama kali tabel dibuat (jika kosong sama sekali)
                try {
                    $totalPivot = \Illuminate\Support\Facades\DB::table('pos_kategori_sampah')->count();
                    if ($totalPivot === 0) {
                        $allPos = \Illuminate\Support\Facades\DB::table('mitra')->pluck('id');
                        $allKategori = \Illuminate\Support\Facades\DB::table('kategori_sampah')->pluck('id');
                        if ($allKategori->isNotEmpty()) {
                            foreach ($allPos as $posId) {
                                $records = [];
                                $now = now();
                                foreach ($allKategori as $catId) {
                                    $records[] = [
                                        'id_mitra'    => $posId,
                                        'id_kategori' => $catId,
                                        'created_at'  => $now,
                                        'updated_at'  => $now,
                                    ];
                                }
                                \Illuminate\Support\Facades\DB::table('pos_kategori_sampah')->insert($records);
                            }
                        }
                    }
                } catch (\Throwable $e) {}
            }
        } catch (\Exception $e) {
            // ignore
        }
    }
}
