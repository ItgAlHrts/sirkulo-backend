<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AdminSystemController extends Controller
{
    public function index()
    {
        // ── Info Framework & Server ─────────────────────────────────────
        $laravelVersion = app()->version();
        $phpVersion     = PHP_VERSION;
        $phpMinRequired = '8.1.0';
        $phpOk          = version_compare($phpVersion, $phpMinRequired, '>=');
        $environment    = app()->environment();
        $debugMode      = config('app.debug');
        $appUrl         = config('app.url');
        $dbConnection   = config('database.default');
        $dbName         = config("database.connections.{$dbConnection}.database", 'sirkulo_db');
        $dbHost         = config("database.connections.{$dbConnection}.host", '127.0.0.1');
        $dbPort         = config("database.connections.{$dbConnection}.port", '3306');
        $isSecure       = request()->isSecure();

        // ── Ping & Koneksi Database ─────────────────────────────────────
        $dbConnected = false;
        $dbLatencyMs = null;
        $dbError     = null;
        $dbVersion   = '—';
        $dbCollation = '—';
        $dbCharset   = '—';
        $dbSizeMb    = 0;
        $dbSizeFormatted = '—';
        $dbOverheadFormatted = '0 KB';

        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $dbLatencyMs = round((microtime(true) - $start) * 1000, 2);
            $dbConnected = true;

            // MySQL Version
            $verResult = DB::select('SELECT VERSION() as v');
            if (!empty($verResult)) {
                $dbVersion = $verResult[0]->v;
            }

            // Charset & Collation
            $collResult = DB::select("SHOW VARIABLES LIKE 'collation_database'");
            if (!empty($collResult)) {
                $dbCollation = $collResult[0]->Value ?? '—';
            }
            $charResult = DB::select("SHOW VARIABLES LIKE 'character_set_database'");
            if (!empty($charResult)) {
                $dbCharset = $charResult[0]->Value ?? '—';
            }

            // Total Database Size & Overhead
            $sizeQuery = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb,
                    ROUND(SUM(data_free) / 1024, 2) AS free_kb
                FROM information_schema.tables
                WHERE table_schema = ?
            ", [$dbName]);

            if (!empty($sizeQuery) && isset($sizeQuery[0]->size_mb)) {
                $dbSizeMb = (float) $sizeQuery[0]->size_mb;
                $dbSizeFormatted = number_format($dbSizeMb, 2) . ' MB';
                $freeKb = (float) ($sizeQuery[0]->free_kb ?? 0);
                if ($freeKb >= 1024) {
                    $dbOverheadFormatted = number_format($freeKb / 1024, 2) . ' MB';
                } else {
                    $dbOverheadFormatted = number_format($freeKb, 1) . ' KB';
                }
            }
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        // ── Statistik Detail Setiap Tabel Database ───────────────────────
        $tableDict = [
            'admin' => [
                'label' => 'Akun Administrator',
                'desc'  => 'Super Admin & Petugas Kelola Sistem Web',
                'icon'  => 'shield',
            ],
            'nasabah' => [
                'label' => 'Data Nasabah Warga',
                'desc'  => 'Profil warga desa, saldo tabungan, & poin',
                'icon'  => 'users',
            ],
            'mitra' => [
                'label' => 'Pos & Petugas Mitra',
                'desc'  => 'Titik pos penimbangan desa & login petugas pos',
                'icon'  => 'building',
            ],
            'kategori_sampah' => [
                'label' => 'Katalog & Harga Sampah',
                'desc'  => 'Master jenis sampah, harga beli warga, & jual',
                'icon'  => 'trash',
            ],
            'pos_kategori_sampah' => [
                'label' => 'Katalog Khusus per Pos',
                'desc'  => 'Relasi dan penyesuaian harga khusus unit pos',
                'icon'  => 'tag',
            ],
            'transaksi' => [
                'label' => 'Riwayat Transaksi',
                'desc'  => 'Log setoran sampah warga & penarikan saldo kas',
                'icon'  => 'receipt',
            ],
            'edukasi' => [
                'label' => 'Artikel Edukasi Warga',
                'desc'  => 'Materi literasi lingkungan, video & tips daur ulang',
                'icon'  => 'book',
            ],
            'kritik_saran' => [
                'label' => 'Aspirasi & Pengaduan Warga',
                'desc'  => 'Masukan, saran perbaikan, & respon pihak desa',
                'icon'  => 'chat',
            ],
            'audit_log' => [
                'label' => 'Rekam Jejak & Audit Trail',
                'desc'  => 'Log keamanan forensik aktivitas seluruh administrator',
                'icon'  => 'clock',
            ],
            'notifikasi' => [
                'label' => 'Notifikasi Pengguna',
                'desc'  => 'Pemberitahuan transaksi & info bank sampah desa',
                'icon'  => 'bell',
            ],
            'pengaturan_sistem' => [
                'label' => 'Parameter Sistem Desa',
                'desc'  => 'Konfigurasi saldo minimal, jam buka, info desa',
                'icon'  => 'cog',
            ],
            'personal_access_tokens' => [
                'label' => 'Token Akses API Mobile',
                'desc'  => 'Sesi autentikasi aman aplikasi mobile Flutter',
                'icon'  => 'key',
            ],
            'migrations' => [
                'label' => 'Log Migrasi Skema',
                'desc'  => 'Riwayat pembaharuan tabel database aplikasi',
                'icon'  => 'database',
            ],
            'petugas_mitra' => [
                'label' => 'Akun Petugas Pos Lapangan',
                'desc'  => 'Data akun petugas operasional lapangan pos bank sampah',
                'icon'  => 'users',
            ],
            'log_whatsapp' => [
                'label' => 'Log Pesan WhatsApp',
                'desc'  => 'Riwayat pengiriman notifikasi pesan WhatsApp ke nasabah',
                'icon'  => 'chat',
            ],
            'cache' => [
                'label' => 'Tabel Cache Database',
                'desc'  => 'Penyimpanan cache kueri (jika mode DB cache aktif)',
                'icon'  => 'archive',
            ],
            'cache_locks' => [
                'label' => 'Lock Mutex Cache',
                'desc'  => 'Pengunci atomik proses konkurensi data transaksi',
                'icon'  => 'key',
            ],
            'jobs' => [
                'label' => 'Tabel Antrean Pekerjaan',
                'desc'  => 'Queue background jobs & task asynchronous',
                'icon'  => 'queue',
            ],
            'job_batches' => [
                'label' => 'Batch Antrean Pekerjaan',
                'desc'  => 'Pengelompokan eksekusi antrean tugas latar belakang',
                'icon'  => 'queue',
            ],
            'failed_jobs' => [
                'label' => 'Antrean Gagal',
                'desc'  => 'Log pekerjaan background yang mengalami kendala eksekusi',
                'icon'  => 'exclamation',
            ],
        ];

        $tableStats = [];
        $totalRowsCount = 0;
        $totalTablesCount = 0;
        $healthyTablesCount = 0;

        if ($dbConnected) {
            try {
                // Ambil daftar seluruh tabel fisik yang ada di database saat ini
                $rawTables = DB::select("
                    SELECT 
                        table_name,
                        engine,
                        table_rows,
                        data_length,
                        index_length,
                        data_free,
                        table_collation,
                        create_time,
                        update_time
                    FROM information_schema.tables
                    WHERE table_schema = ?
                    ORDER BY table_name ASC
                ", [$dbName]);

                $existingTablesMap = [];
                foreach ($rawTables as $rt) {
                    $existingTablesMap[$rt->table_name] = $rt;
                }

                // Gabungkan daftar tabel yang terdaftar di dictionary + tabel tambahan lain
                $allTableNames = array_unique(array_merge(array_keys($tableDict), array_keys($existingTablesMap)));
                sort($allTableNames);

                foreach ($allTableNames as $tbl) {
                    $isExist = isset($existingTablesMap[$tbl]);
                    $meta = $tableDict[$tbl] ?? [
                        'label' => ucwords(str_replace('_', ' ', $tbl)),
                        'desc'  => 'Tabel pendukung aplikasi',
                        'icon'  => 'database',
                    ];

                    $rows = 0;
                    $engine = '—';
                    $dataSize = '0 KB';
                    $indexSize = '0 KB';
                    $totalSize = '0 KB';
                    $overhead = '0 KB';
                    $status = 'BELUM_ADA';

                    if ($isExist) {
                        $info = $existingTablesMap[$tbl];
                        $engine = $info->engine ?? 'InnoDB';
                        try {
                            $rows = DB::table($tbl)->count();
                        } catch (\Throwable $e) {
                            $rows = (int) ($info->table_rows ?? 0);
                        }

                        $dataBytes = (int) ($info->data_length ?? 0);
                        $idxBytes  = (int) ($info->index_length ?? 0);
                        $freeBytes = (int) ($info->data_free ?? 0);

                        $dataSize  = self::formatBytes($dataBytes);
                        $indexSize = self::formatBytes($idxBytes);
                        $totalSize = self::formatBytes($dataBytes + $idxBytes);
                        $overhead  = self::formatBytes($freeBytes);

                        $status = ($freeBytes > 1048576) ? 'PERLU_OPTIMASI' : 'SEHAT';
                        $totalRowsCount += $rows;
                        $totalTablesCount++;
                        if ($status === 'SEHAT') $healthyTablesCount++;
                    }

                    $tableStats[] = [
                        'table'       => $tbl,
                        'label'       => $meta['label'],
                        'desc'        => $meta['desc'],
                        'icon'        => $meta['icon'],
                        'exists'      => $isExist,
                        'rows'        => $rows,
                        'engine'      => $engine,
                        'data_size'   => $dataSize,
                        'index_size'  => $indexSize,
                        'total_size'  => $totalSize,
                        'overhead'    => $overhead,
                        'status'      => $status,
                    ];
                }
            } catch (\Throwable $e) {
                // Fallback sederhana jika information_schema tidak dapat diakses
                foreach ($tableDict as $tbl => $meta) {
                    $exists = Schema::hasTable($tbl);
                    $count = $exists ? DB::table($tbl)->count() : 0;
                    $tableStats[] = [
                        'table'       => $tbl,
                        'label'       => $meta['label'],
                        'desc'        => $meta['desc'],
                        'icon'        => $meta['icon'],
                        'exists'      => $exists,
                        'rows'        => $count,
                        'engine'      => 'InnoDB',
                        'data_size'   => '—',
                        'index_size'  => '—',
                        'total_size'  => '—',
                        'overhead'    => '0 KB',
                        'status'      => $exists ? 'SEHAT' : 'BELUM_ADA',
                    ];
                    if ($exists) {
                        $totalRowsCount += $count;
                        $totalTablesCount++;
                        $healthyTablesCount++;
                    }
                }
            }
        }

        // ── Pemeriksaan Berkas Migrasi Database (Online vs File) ────────
        $migrationStats = [
            'total_files'   => 0,
            'executed'      => 0,
            'pending_count' => 0,
            'pending_files' => [],
            'is_synced'     => true,
        ];

        try {
            $migrationPath = database_path('migrations');
            $files = File::exists($migrationPath) ? File::files($migrationPath) : [];
            $allMigrationNames = [];
            foreach ($files as $f) {
                $allMigrationNames[] = pathinfo($f->getFilename(), PATHINFO_FILENAME);
            }
            $migrationStats['total_files'] = count($allMigrationNames);

            $executedNames = [];
            if ($dbConnected && Schema::hasTable('migrations')) {
                $executedNames = DB::table('migrations')->pluck('migration')->toArray();
                $migrationStats['executed'] = count($executedNames);
            }

            $pending = array_diff($allMigrationNames, $executedNames);
            $migrationStats['pending_count'] = count($pending);
            $migrationStats['pending_files'] = array_values($pending);
            $migrationStats['is_synced']     = empty($pending);
        } catch (\Throwable $e) {}

        // ── Storage & Berkas Publik ─────────────────────────────────────
        $storageLinked   = File::exists(public_path('storage'));
        $storageSize     = '0 KB';
        $storageFiles    = 0;
        $storageWritable = false;
        try {
            $storagePath = storage_path('app/public');
            if (File::exists($storagePath)) {
                $storageWritable = is_writable($storagePath);
                $bytes = 0;
                $allFiles = File::allFiles($storagePath);
                $storageFiles = count($allFiles);
                foreach ($allFiles as $file) {
                    $bytes += $file->getSize();
                }
                $storageSize = self::formatBytes($bytes);
            }
        } catch (\Throwable $e) {}

        // ── Cache & Optimasi Status ─────────────────────────────────────
        $configCached = File::exists(base_path('bootstrap/cache/config.php'));
        $routesCached = File::exists(base_path('bootstrap/cache/routes-v7.php'))
                     || File::exists(base_path('bootstrap/cache/routes.php'));
        $viewsCached  = count(File::glob(base_path('storage/framework/views/*.php'))) > 0;

        // ── PHP Extension Checklist (Kebutuhan Mutlak Hosting) ─────────
        $requiredExtensions = [
            'pdo'        => ['name' => 'PDO Core', 'role' => 'Abstraksi koneksi database utama'],
            'pdo_mysql'  => ['name' => 'PDO MySQL Driver', 'role' => 'Koneksi & kueri transaksi ke server MySQL/MariaDB'],
            'openssl'    => ['name' => 'OpenSSL', 'role' => 'Enkripsi kata sandi, token Sanctum, & sesi aman'],
            'mbstring'   => ['name' => 'Mbstring', 'role' => 'Pemrosesan teks multibyte UTF-8'],
            'tokenizer'  => ['name' => 'Tokenizer', 'role' => 'Penyusunan kode internal & kompilasi template Blade'],
            'xml'        => ['name' => 'XML Parser', 'role' => 'Parsing format data XML & dokumen'],
            'ctype'      => ['name' => 'Ctype', 'role' => 'Validasi tipe karakter dan string'],
            'json'       => ['name' => 'JSON Support', 'role' => 'Pertukaran format data API mobile aplikasi'],
            'bcmath'     => ['name' => 'BCMath', 'role' => 'Aritmatika presisi tinggi untuk saldo kas & poin warga'],
            'fileinfo'   => ['name' => 'FileInfo', 'role' => 'Deteksi MIME-type berkas upload agar anti-virus aman'],
            'zip'        => ['name' => 'ZIP Extension', 'role' => 'Kompresi arsip cadangan & ekspor berkas'],
            'curl'       => ['name' => 'cURL Client', 'role' => 'Pengiriman permintaan HTTP ke layanan eksternal'],
        ];

        $extensionStatus = [];
        $activeExtensionsCount = 0;
        foreach ($requiredExtensions as $ext => $info) {
            $isActive = extension_loaded($ext);
            if ($isActive) $activeExtensionsCount++;
            $extensionStatus[] = [
                'ext'    => $ext,
                'name'   => $info['name'],
                'role'   => $info['role'],
                'active' => $isActive,
            ];
        }

        // ── Izin Folder (Writeable Permissions untuk Hosting) ───────────
        $folderPermissions = [
            ['path' => storage_path(),                   'label' => 'storage/',                     'role' => 'Direktori utama penyimpanan dinamis'],
            ['path' => storage_path('app'),              'label' => 'storage/app/',                 'role' => 'Penyimpanan file internal'],
            ['path' => storage_path('app/public'),       'label' => 'storage/app/public/',          'role' => 'Folder foto upload publik'],
            ['path' => storage_path('logs'),             'label' => 'storage/logs/',                'role' => 'Penyimpanan berkas log error sistem'],
            ['path' => storage_path('framework'),        'label' => 'storage/framework/',           'role' => 'Cache internal framework Laravel'],
            ['path' => storage_path('framework/cache'),  'label' => 'storage/framework/cache/',     'role' => 'Cache aplikasi'],
            ['path' => storage_path('framework/sessions'),'label' => 'storage/framework/sessions/',  'role' => 'Penyimpanan berkas sesi login'],
            ['path' => storage_path('framework/views'),  'label' => 'storage/framework/views/',     'role' => 'Kompilasi template Blade tampilan'],
            ['path' => base_path('bootstrap/cache'),     'label' => 'bootstrap/cache/',            'role' => 'Cache konfigurasi & routing cepat'],
        ];

        $writableFoldersCount = 0;
        foreach ($folderPermissions as &$fp) {
            $fp['exists']   = is_dir($fp['path']);
            $fp['writable'] = $fp['exists'] && is_writable($fp['path']);
            if ($fp['writable']) $writableFoldersCount++;
        }
        unset($fp);

        // ── Dependensi Terpasang (composer.lock) ────────────────────────
        $installedPackages = [];
        $lockFile = base_path('composer.lock');
        if (File::exists($lockFile)) {
            try {
                $lockContent = json_decode(File::get($lockFile), true);
                $watched = [
                    'laravel/framework', 'laravel/sanctum', 'laravel/tinker',
                    'symfony/http-foundation', 'guzzlehttp/guzzle',
                    'league/flysystem', 'nesbot/carbon',
                ];
                foreach (($lockContent['packages'] ?? []) as $pkg) {
                    if (in_array($pkg['name'], $watched)) {
                        $installedPackages[] = [
                            'name'        => $pkg['name'],
                            'version'     => $pkg['version'],
                            'description' => $pkg['description'] ?? '-',
                        ];
                    }
                }
            } catch (\Throwable $e) {}
        }

        // ── Disk & Resource Server ──────────────────────────────────────
        $diskTotal   = '—';
        $diskFree    = '—';
        $diskUsedPct = 0;
        try {
            if (function_exists('disk_total_space') && function_exists('disk_free_space')) {
                $total = @disk_total_space(base_path());
                $free  = @disk_free_space(base_path());
                if ($total && $free) {
                    $used = $total - $free;
                    $diskTotal   = self::formatBytes((int)$total);
                    $diskFree    = self::formatBytes((int)$free);
                    $diskUsedPct = round(($used / $total) * 100, 1);
                }
            }
        } catch (\Throwable $e) {}

        // ── Parameter Memori & PHP Runtime ──────────────────────────────
        $memoryLimit   = ini_get('memory_limit') ?: '—';
        $memoryUsage   = self::formatBytes(memory_get_usage(true));
        $maxUploadSize = ini_get('upload_max_filesize') ?: '—';
        $maxPostSize   = ini_get('post_max_size') ?: '—';
        $maxExecTime   = (ini_get('max_execution_time') ?: '0') . ' detik';
        $queueDriver   = config('queue.default', 'sync');

        return view('admin.sistem.index', compact(
            'laravelVersion', 'phpVersion', 'phpOk', 'environment', 'debugMode',
            'appUrl', 'dbConnection', 'dbName', 'dbHost', 'dbPort',
            'dbConnected', 'dbLatencyMs', 'dbError', 'dbVersion', 'dbCollation', 'dbCharset',
            'dbSizeFormatted', 'dbOverheadFormatted', 'isSecure',
            'storageLinked', 'storageSize', 'storageFiles', 'storageWritable',
            'configCached', 'routesCached', 'viewsCached',
            'tableStats', 'totalRowsCount', 'totalTablesCount', 'healthyTablesCount',
            'migrationStats', 'extensionStatus', 'activeExtensionsCount',
            'folderPermissions', 'writableFoldersCount',
            'installedPackages', 'diskTotal', 'diskFree', 'diskUsedPct',
            'memoryLimit', 'memoryUsage', 'maxUploadSize', 'maxPostSize', 'maxExecTime',
            'queueDriver'
        ));
    }

    // ── Helper: Format Bytes ke Satuan Terbaca ──────────────────────────
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576)    return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024)       return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    // ── Aksi: Bersihkan Seluruh Cache Aplikasi ──────────────────────────
    public function clearCache()
    {
        try {
            Artisan::call('optimize:clear');
            \App\Services\AuditLogger::log(
                'BERSIHKAN_CACHE_SISTEM',
                'Membersihkan seluruh cache aplikasi: konfigurasi, rute, template view, dan compiled events.',
                'SISTEM', 'ACTION', 'SUKSES'
            );
            return back()->with('success', '✅ Seluruh cache aplikasi, konfigurasi, rute, dan template tampilan Blade berhasil dibersihkan!');
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_BERSIHKAN_CACHE', 'Gagal membersihkan cache: ' . $e->getMessage(), 'SISTEM', 'ACTION', 'GAGAL');
            return back()->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    // ── Aksi: Optimasi Produksi (Cache Config & Rute) ───────────────────
    public function optimize()
    {
        try {
            Artisan::call('optimize');
            \App\Services\AuditLogger::log(
                'OPTIMASI_SISTEM',
                'Optimasi performa hosting: file konfigurasi dan rute berhasil di-compile ke cache.',
                'SISTEM', 'ACTION', 'SUKSES'
            );
            return back()->with('success', '⚡ Sistem berhasil dioptimasi untuk hosting! Konfigurasi dan rute kini berjalan dengan kecepatan maksimal.');
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_OPTIMASI_SISTEM', 'Gagal optimasi sistem: ' . $e->getMessage(), 'SISTEM', 'ACTION', 'GAGAL');
            return back()->with('error', 'Gagal mengoptimasi sistem: ' . $e->getMessage());
        }
    }

    // ── Aksi: Hubungkan / Tautkan Ulang Folder Storage Publik ───────────
    public function storageLink()
    {
        try {
            File::ensureDirectoryExists(storage_path('app/public'));
            $target = public_path('storage');
            if (is_link($target)) {
                @unlink($target);
            }
            Artisan::call('storage:link', ['--force' => true]);
            \App\Services\AuditLogger::log(
                'PERBARUI_STORAGE_LINK',
                'Menghubungkan tautan simbolik folder storage/app/public ke public/storage.',
                'SISTEM', 'ACTION', 'SUKSES'
            );
            return back()->with('success', '🔗 Tautan storage berhasil diperbarui! Foto upload profil & edukasi kini dapat ditampilkan dengan baik.');
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_STORAGE_LINK', 'Gagal storage link: ' . $e->getMessage(), 'SISTEM', 'ACTION', 'GAGAL');
            return back()->with('error', 'Gagal menautkan storage: ' . $e->getMessage() . '. Tenang, SIRKULO memiliki router berkas bawaan (/storage/{path}) sehingga berkas tetap dapat diakses!');
        }
    }

    // ── Aksi: Jalankan Migrasi Database Online ──────────────────────────
    public function runMigrations()
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = Artisan::output();
            $cleanOutput = trim($output);

            \App\Services\AuditLogger::log(
                'JALANKAN_MIGRASI_DB',
                'Menjalankan migrasi database skema. Hasil: ' . ($cleanOutput ?: 'Database sudah versi terbaru.'),
                'SISTEM', 'ACTION', 'SUKSES'
            );

            return back()->with('success', '🗄️ Migrasi database berhasil dijalankan! ' . ($cleanOutput ?: 'Seluruh tabel sudah dalam versi terbaru.'));
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_MIGRASI_DB', 'Gagal migrasi database: ' . $e->getMessage(), 'SISTEM', 'ACTION', 'GAGAL');
            return back()->with('error', 'Gagal menjalankan migrasi skema: ' . $e->getMessage());
        }
    }

    // ── Aksi: Optimalkan & Defragmentasi Tabel Database ──────────────────
    public function optimizeTables()
    {
        try {
            $dbName = config("database.connections." . config('database.default') . ".database", 'sirkulo_db');
            $tables = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $count = 0;

            foreach ($tables as $tRow) {
                $prop = "Tables_in_" . $dbName;
                $tbl = isset($tRow->$prop) ? $tRow->$prop : array_values((array)$tRow)[0] ?? null;

                if ($tbl) {
                    try {
                        DB::statement("OPTIMIZE TABLE `{$tbl}`");
                        DB::statement("ANALYZE TABLE `{$tbl}`");
                        $count++;
                    } catch (\Throwable $ex) {}
                }
            }

            \App\Services\AuditLogger::log(
                'OPTIMASI_TABEL_DB',
                "Berhasil mengoptimalkan, defragmentasi, dan memperbarui indeks pada {$count} tabel database.",
                'SISTEM', 'ACTION', 'SUKSES'
            );

            return back()->with('success', "✨ Berhasil melakukan defragmentasi dan optimasi indeks pada {$count} tabel database! Performa kueri kini lebih cepat dan hemat ruang.");
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_OPTIMASI_TABEL', 'Gagal optimasi tabel: ' . $e->getMessage(), 'SISTEM', 'ACTION', 'GAGAL');
            return back()->with('error', 'Gagal mengoptimasi tabel: ' . $e->getMessage());
        }
    }

    // ── Aksi: Backup Database Lengkap (.sql Otomatis) ───────────────────
    public function backupDatabase()
    {
        try {
            $dbName = config("database.connections." . config('database.default') . ".database", 'sirkulo_db');
            
            // Ambil seluruh tabel dasar yang ada di database secara dinamis
            $tablesQuery = DB::select("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
            $tables = [];
            foreach ($tablesQuery as $tRow) {
                $prop = "Tables_in_" . $dbName;
                if (isset($tRow->$prop)) {
                    $tables[] = $tRow->$prop;
                } else {
                    $vals = array_values((array)$tRow);
                    if (!empty($vals)) $tables[] = $vals[0];
                }
            }

            // Fallback jika kosong
            if (empty($tables)) {
                $tables = [
                    'admin', 'mitra', 'nasabah', 'petugas_mitra',
                    'kategori_sampah', 'pos_kategori_sampah', 'transaksi',
                    'notifikasi', 'edukasi', 'kritik_saran',
                    'audit_log', 'pengaturan_sistem', 'personal_access_tokens',
                    'log_whatsapp', 'migrations',
                ];
            }

            $pdo = DB::getPdo();

            $sqlDump  = "-- ========================================================\n";
            $sqlDump .= "-- BACKUP RESMI DATABASE APLIKASI SIRKULO BANK SAMPAH DESA\n";
            $sqlDump .= "-- Dibuat Pada   : " . now()->format('Y-m-d H:i:s') . " WIB\n";
            $sqlDump .= "-- Framework     : Laravel " . app()->version() . "\n";
            $sqlDump .= "-- PHP Version   : " . PHP_VERSION . "\n";
            $sqlDump .= "-- Nama Database : " . $dbName . "\n";
            $sqlDump .= "-- Total Tabel   : " . count($tables) . " tabel\n";
            $sqlDump .= "-- ========================================================\n\n";
            $sqlDump .= "SET NAMES utf8mb4;\n";
            $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) continue;

                $sqlDump .= "-- --------------------------------------------------------\n";
                $sqlDump .= "-- Struktur Tabel: `{$table}`\n";
                $sqlDump .= "-- --------------------------------------------------------\n";
                $sqlDump .= "DROP TABLE IF EXISTS `{$table}`;\n";

                try {
                    $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createTable)) {
                        $create = $createTable[0]->{'Create Table'} ?? null;
                        if ($create) $sqlDump .= $create . ";\n\n";
                    }
                } catch (\Throwable $ex) {}

                $rows = DB::table($table)->get();
                if ($rows->count() > 0) {
                    $sqlDump .= "-- Data Isi Tabel `{$table}` ({$rows->count()} baris)\n";
                    $sqlDump .= "LOCK TABLES `{$table}` WRITE;\n";
                    
                    // Chunk insertion untuk menghindari buffer memory overhead
                    foreach ($rows->chunk(100) as $chunk) {
                        foreach ($chunk as $row) {
                            $rowArray = (array) $row;
                            $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($rowArray)));
                            $vals = implode(', ', array_map(function ($val) use ($pdo) {
                                if (is_null($val)) return 'NULL';
                                return $pdo->quote((string)$val);
                            }, array_values($rowArray)));
                            $sqlDump .= "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n";
                        }
                    }
                    
                    $sqlDump .= "UNLOCK TABLES;\n\n";
                }
            }

            $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
            $sqlDump .= "-- ========== SELESAI PROSES BACKUP DATABASE SIRKULO ==========\n";

            $filename = "backup_sirkulo_" . now()->format('Ymd_His') . ".sql";

            \App\Services\AuditLogger::log(
                'BACKUP_DATABASE',
                "Pencadangan database berhasil diunduh: {$filename} (" . count($tables) . " tabel).",
                'SISTEM', 'EXPORT', 'SUKSES',
                ['filename' => $filename, 'tabel_count' => count($tables)]
            );

            return response($sqlDump, 200, [
                'Content-Type'        => 'application/sql',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
            ]);
        } catch (\Throwable $e) {
            \App\Services\AuditLogger::log('GAGAL_BACKUP_DATABASE', 'Gagal backup: ' . $e->getMessage(), 'SISTEM', 'EXPORT', 'GAGAL');
            return back()->with('error', 'Gagal membuat cadangan database: ' . $e->getMessage());
        }
    }
}
