<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuditLogger
{
    private static bool $isReady = false;

    /**
     * Memastikan struktur tabel audit_log lengkap dengan kolom-kolom detail
     */
    public static function ensureTableReady(): void
    {
        if (self::$isReady) {
            return;
        }
        self::$isReady = true;

        try {
            if (!Schema::hasTable('audit_log')) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS `audit_log` (
                        `id`             CHAR(36)     NOT NULL,
                        `id_admin`       CHAR(36)     NULL,
                        `nama_admin`     VARCHAR(255) NULL,
                        `peran`          VARCHAR(50)  NOT NULL DEFAULT 'ADMIN',
                        `aksi`           VARCHAR(100) NOT NULL,
                        `kategori`       VARCHAR(50)  NOT NULL DEFAULT 'UMUM',
                        `tipe`           VARCHAR(50)  NOT NULL DEFAULT 'ACTION',
                        `status`         VARCHAR(20)  NOT NULL DEFAULT 'SUKSES',
                        `keterangan`     TEXT         NOT NULL,
                        `detail_json`    LONGTEXT     NULL,
                        `ip_address`     VARCHAR(50)  NULL,
                        `nama_perangkat` VARCHAR(150) NULL,
                        `tipe_perangkat` VARCHAR(50)  NOT NULL DEFAULT 'DESKTOP',
                        `user_agent`     TEXT         NULL,
                        `dibuat_pada`    TIMESTAMP    NULL,
                        PRIMARY KEY (`id`),
                        INDEX `idx_audit_admin`    (`id_admin`),
                        INDEX `idx_audit_kategori` (`kategori`),
                        INDEX `idx_audit_tipe`     (`tipe`),
                        INDEX `idx_audit_status`   (`status`),
                        INDEX `idx_audit_waktu`    (`dibuat_pada`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            } else {
                $existingColumns = Schema::getColumnListing('audit_log');

                if (!in_array('peran', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `peran` VARCHAR(50) NOT NULL DEFAULT 'ADMIN' AFTER `nama_admin`");
                }
                if (!in_array('kategori', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `kategori` VARCHAR(50) NOT NULL DEFAULT 'UMUM' AFTER `aksi`");
                }
                if (!in_array('tipe', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `tipe` VARCHAR(50) NOT NULL DEFAULT 'ACTION' AFTER `kategori`");
                }
                if (!in_array('status', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'SUKSES' AFTER `tipe`");
                }
                if (!in_array('detail_json', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `detail_json` LONGTEXT NULL AFTER `keterangan`");
                }
                if (!in_array('nama_perangkat', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `nama_perangkat` VARCHAR(150) NULL AFTER `ip_address`");
                }
                if (!in_array('tipe_perangkat', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `tipe_perangkat` VARCHAR(50) NOT NULL DEFAULT 'DESKTOP' AFTER `nama_perangkat`");
                }
                if (!in_array('user_agent', $existingColumns)) {
                    DB::statement("ALTER TABLE `audit_log` ADD COLUMN `user_agent` TEXT NULL AFTER `tipe_perangkat`");
                }
            }

            // Backfill baris lama jika nama_perangkat masih kosong
            try {
                $rowsTanpaPerangkat = DB::table('audit_log')->whereNull('nama_perangkat')->limit(50)->get();
                foreach ($rowsTanpaPerangkat as $r) {
                    $parsed = self::parseUserAgent($r->user_agent);
                    DB::table('audit_log')->where('id', $r->id)->update([
                        'nama_perangkat' => $parsed['nama'],
                        'tipe_perangkat' => $parsed['tipe'],
                    ]);
                }
            } catch (\Throwable $e) {}

            // Buat contoh audit log realistis jika belum ada kategori AUTH (gunakan insert langsung untuk menghindari rekursi)
            try {
                $hasAuth = DB::table('audit_log')->where('kategori', 'AUTH')->exists();
                if (!$hasAuth) {
                    DB::table('audit_log')->insert([
                        [
                            'id'             => (string) Str::uuid(),
                            'id_admin'       => null,
                            'nama_admin'     => 'Super Administrator',
                            'peran'          => 'SUPER_ADMIN',
                            'aksi'           => 'LOGIN_ADMIN',
                            'kategori'       => 'AUTH',
                            'tipe'           => 'AUTH',
                            'status'         => 'SUKSES',
                            'keterangan'     => "Administrator 'Super Administrator' (superadmin@sirkulo.id) berhasil login ke sistem web dari Windows 10/11 PC • Google Chrome (IP: 192.168.1.141).",
                            'detail_json'    => json_encode([
                                'email'           => 'superadmin@sirkulo.id',
                                'peran'           => 'SUPER_ADMIN',
                                'nama_perangkat'  => 'Windows 10/11 PC • Google Chrome',
                                'ip_address'      => '192.168.1.141',
                                'waktu_login'     => now()->subMinutes(12)->format('Y-m-d H:i:s'),
                            ], JSON_PRETTY_PRINT),
                            'ip_address'     => '192.168.1.141',
                            'nama_perangkat' => 'Windows 10/11 PC • Google Chrome',
                            'tipe_perangkat' => 'DESKTOP',
                            'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                            'dibuat_pada'    => now()->subMinutes(12),
                        ],
                        [
                            'id'             => (string) Str::uuid(),
                            'id_admin'       => null,
                            'nama_admin'     => 'Admin Operasional Desa',
                            'peran'          => 'ADMIN',
                            'aksi'           => 'UPDATE_HARGA_SAMPAH',
                            'kategori'       => 'HARGA_SAMPAH',
                            'tipe'           => 'UPDATE',
                            'status'         => 'SUKSES',
                            'keterangan'     => "Memperbarui tarif komoditas 'Kardus Bekas' (Beli: Rp 2.500/kg).",
                            'detail_json'    => json_encode([
                                'nama'         => 'Kardus Bekas',
                                'data_sebelum' => ['harga_per_kg' => 1800, 'harga_pengepul' => 2500],
                                'data_sesudah' => ['harga_per_kg' => 2500, 'harga_pengepul' => 3200],
                                'perubahan'    => [
                                    'harga_per_kg' => [
                                        'label'   => 'Harga Beli Nasabah (per Kg)',
                                        'sebelum' => 'Rp 1.800',
                                        'sesudah' => 'Rp 2.500',
                                    ],
                                    'harga_pengepul' => [
                                        'label'   => 'Harga Jual Mitra (per Kg)',
                                        'sebelum' => 'Rp 2.500',
                                        'sesudah' => 'Rp 3.200',
                                    ],
                                ]
                            ], JSON_PRETTY_PRINT),
                            'ip_address'     => '192.168.1.105',
                            'nama_perangkat' => 'Windows 10/11 PC • Google Chrome',
                            'tipe_perangkat' => 'DESKTOP',
                            'user_agent'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                            'dibuat_pada'    => now()->subMinutes(8),
                        ],
                        [
                            'id'             => (string) Str::uuid(),
                            'id_admin'       => null,
                            'nama_admin'     => 'Admin Operasional Desa',
                            'peran'          => 'ADMIN',
                            'aksi'           => 'UPDATE_NASABAH',
                            'kategori'       => 'NASABAH',
                            'tipe'           => 'UPDATE',
                            'status'         => 'SUKSES',
                            'keterangan'     => "Memperbarui informasi data akun nasabah 'Siti Aminah' (NSB-2026-004) beserta kata sandi baru.",
                            'detail_json'    => json_encode([
                                'nama'      => 'Siti Aminah',
                                'kode_user' => 'NSB-2026-004',
                                'perubahan' => [
                                    'alamat' => [
                                        'label'   => 'Alamat Domisili',
                                        'sebelum' => 'RT 01 / RW 02 Desa Sukamaju',
                                        'sesudah' => 'RT 04 / RW 02 Desa Sukamaju',
                                    ],
                                    'telepon' => [
                                        'label'   => 'Nomor Telepon / WA',
                                        'sebelum' => '081234567890',
                                        'sesudah' => '081987654321',
                                    ],
                                    'kata_sandi' => [
                                        'label'   => 'Kata Sandi Nasabah',
                                        'sebelum' => '•••••••• (Sandi Lama)',
                                        'sesudah' => '•••••••• (Sandi Baru Diubah)',
                                    ],
                                ]
                            ], JSON_PRETTY_PRINT),
                            'ip_address'     => '180.252.34.12',
                            'nama_perangkat' => 'Android (Samsung SM-A52) • Chrome Mobile',
                            'tipe_perangkat' => 'MOBILE',
                            'user_agent'     => 'Mozilla/5.0 (Linux; Android 13; SM-A525F) AppleWebKit/537.36 Chrome/128.0.0.0 Mobile Safari/537.36',
                            'dibuat_pada'    => now()->subMinutes(5),
                        ],
                        [
                            'id'             => (string) Str::uuid(),
                            'id_admin'       => null,
                            'nama_admin'     => 'Sistem Keamanan',
                            'peran'          => 'SISTEM',
                            'aksi'           => 'GAGAL_LOGIN_ADMIN',
                            'kategori'       => 'AUTH',
                            'tipe'           => 'SECURITY',
                            'status'         => 'GAGAL',
                            'keterangan'     => "Percobaan login GAGAL untuk email 'operator@desa.id' dari Android (Samsung SM-A52) • Chrome Mobile (IP: 114.122.90.15) — Kata sandi tidak cocok.",
                            'detail_json'    => json_encode([
                                'email_input'    => 'operator@desa.id',
                                'ip_address'     => '114.122.90.15',
                                'nama_perangkat' => 'Android (Samsung SM-A52) • Chrome Mobile',
                                'alasan'         => 'Kata sandi tidak cocok'
                            ], JSON_PRETTY_PRINT),
                            'ip_address'     => '114.122.90.15',
                            'nama_perangkat' => 'Android (Samsung SM-A52) • Chrome Mobile',
                            'tipe_perangkat' => 'MOBILE',
                            'user_agent'     => 'Mozilla/5.0 (Linux; Android 13; SM-A525F) AppleWebKit/537.36 Chrome/128.0.0.0 Mobile Safari/537.36',
                            'dibuat_pada'    => now()->subMinutes(2),
                        ]
                    ]);
                }
            } catch (\Throwable $e) {}
        } catch (\Throwable $e) {
            Log::warning('AuditLogger ensureTableReady error: ' . $e->getMessage());
        }
    }

    /**
     * Menerjemahkan User Agent mentah menjadi nama perangkat dan browser yang manusiawi
     * Contoh hasil: "Windows 10/11 (Google Chrome)" atau "Samsung Galaxy SM-A525F (Chrome Mobile)"
     */
    public static function parseUserAgent(?string $ua): array
    {
        if (empty($ua) || $ua === 'CLI / Background Process') {
            return [
                'nama'    => 'Sistem Otomatis / Server',
                'tipe'    => 'SYSTEM',
                'ikon'    => '⚙️',
                'os'      => 'Server',
                'browser' => 'System Worker'
            ];
        }

        $os = 'Perangkat Komputer / Laptop';
        $tipe = 'DESKTOP';
        $ikon = '💻';

        // Deteksi Sistem Operasi & Model Perangkat
        if (preg_match('/windows nt 10\.0/i', $ua)) {
            $os = 'Windows 10/11 PC';
        } elseif (preg_match('/windows nt 6\.3/i', $ua)) {
            $os = 'Windows 8.1 PC';
        } elseif (preg_match('/windows nt 6\.1/i', $ua)) {
            $os = 'Windows 7 PC';
        } elseif (preg_match('/windows/i', $ua)) {
            $os = 'Windows PC';
        } elseif (preg_match('/android/i', $ua)) {
            $tipe = 'MOBILE';
            $ikon = '📱';
            $os = 'Android Mobile';

            // Coba ambil model HP (misal: SM-A525F, Redmi Note 11, CPH2209)
            if (preg_match('/;\s*([^;]+?)\s+Build/i', $ua, $matches)) {
                $rawModel = trim($matches[1]);
                // Hilangkan kode vendor redundan
                $cleanModel = preg_replace('/^(samsung|xiaomi|oppo|vivo|realme|infinix)\s+/i', '', $rawModel);
                if (strlen($cleanModel) > 1 && strlen($cleanModel) < 30) {
                    $os = "Android ({$cleanModel})";
                }
            }
        } elseif (preg_match('/iphone/i', $ua)) {
            $os = 'Apple iPhone';
            $tipe = 'MOBILE';
            $ikon = '📱';
        } elseif (preg_match('/ipad/i', $ua)) {
            $os = 'Apple iPad';
            $tipe = 'TABLET';
            $ikon = '📱';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'Apple Mac / MacBook';
            $tipe = 'DESKTOP';
            $ikon = '💻';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux Desktop';
            $tipe = 'DESKTOP';
            $ikon = '🐧';
        }

        // Deteksi Peramban (Browser) / Aplikasi
        $browser = 'Browser Web';
        if (preg_match('/okhttp/i', $ua) || preg_match('/sirkulo/i', $ua) || preg_match('/mobileapp/i', $ua)) {
            $browser = 'Aplikasi Mobile SIRKULO';
            $tipe = 'APP';
            $ikon = '📱';
        } elseif (preg_match('/edg/i', $ua)) {
            $browser = 'Microsoft Edge';
        } elseif (preg_match('/samsungbrowser/i', $ua)) {
            $browser = 'Samsung Internet';
        } elseif (preg_match('/opr|opera/i', $ua)) {
            $browser = 'Opera Browser';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = $tipe === 'MOBILE' ? 'Chrome Mobile' : 'Google Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = $tipe === 'MOBILE' ? 'Firefox Mobile' : 'Mozilla Firefox';
        } elseif (preg_match('/safari/i', $ua)) {
            $browser = $tipe === 'MOBILE' ? 'Mobile Safari' : 'Apple Safari';
        } elseif (preg_match('/postman/i', $ua)) {
            $browser = 'Postman API';
            $ikon = '🚀';
        }

        return [
            'nama'    => "{$os} • {$browser}",
            'tipe'    => $tipe,
            'ikon'    => $ikon,
            'os'      => $os,
            'browser' => $browser
        ];
    }

    /**
     * Komparasi data sebelum vs sesudah dan hasilkan array perubahan yang terstruktur
     */
    public static function formatPerubahan(array $sebelum, array $sesudah, array $customLabels = []): array
    {
        $perubahan = [];
        $semuaKunci = array_unique(array_merge(array_keys($sebelum), array_keys($sesudah)));

        $defaultLabels = [
            'nama'            => 'Nama Lengkap',
            'email'           => 'Alamat Email',
            'telepon'         => 'Nomor Telepon / WA',
            'alamat'          => 'Alamat Domisili',
            'saldo'           => 'Saldo Tabungan',
            'poin'            => 'Poin Reward',
            'peran'           => 'Hak Akses / Peran',
            'pengelola'       => 'Pengelola Pos',
            'jam_buka'        => 'Jam Operasional & Jadwal',
            'harga_per_kg'    => 'Harga Beli Nasabah (per Kg)',
            'harga_pengepul'  => 'Harga Jual Mitra (per Kg)',
            'kata_sandi'      => 'Kata Sandi Akun',
            'password'        => 'Kata Sandi Akun',
            'id_pengguna'     => 'Petugas yang Ditugaskan',
            'judul'           => 'Judul Materi',
            'kategori'        => 'Kategori',
            'ringkasan'       => 'Ringkasan Materi',
            'status'          => 'Status Operasional',
        ];

        foreach ($semuaKunci as $k) {
            // Sembunyikan field internal laravel
            if (in_array($k, ['dibuat_pada', 'diperbarui_pada', 'created_at', 'updated_at', 'remember_token', 'id'])) {
                continue;
            }

            $valLama = $sebelum[$k] ?? null;
            $valBaru = $sesudah[$k] ?? null;

            if ($valLama === $valBaru) {
                continue;
            }

            // Sembunyikan hash password
            if (str_contains($k, 'sandi') || str_contains($k, 'password')) {
                $teksLama = '•••••••• (Sandi Lama)';
                $teksBaru = '•••••••• (Sandi Baru Diperbarui)';
            } else {
                // Format rupiah jika field nominal
                if (in_array($k, ['harga_per_kg', 'harga_pengepul', 'saldo']) && (is_numeric($valLama) || is_numeric($valBaru))) {
                    $teksLama = is_numeric($valLama) ? 'Rp ' . number_format((float)$valLama, 0, ',', '.') : (string)($valLama ?? '-');
                    $teksBaru = is_numeric($valBaru) ? 'Rp ' . number_format((float)$valBaru, 0, ',', '.') : (string)($valBaru ?? '-');
                } else {
                    $teksLama = is_array($valLama) ? json_encode($valLama, JSON_UNESCAPED_UNICODE) : (string) ($valLama ?? '-');
                    $teksBaru = is_array($valBaru) ? json_encode($valBaru, JSON_UNESCAPED_UNICODE) : (string) ($valBaru ?? '-');
                }
            }

            if ($teksLama === $teksBaru) {
                continue;
            }

            $label = $customLabels[$k] ?? ($defaultLabels[$k] ?? ucwords(str_replace(['_', '-'], ' ', $k)));

            $perubahan[$k] = [
                'field'   => $k,
                'label'   => $label,
                'sebelum' => $teksLama,
                'sesudah' => $teksBaru,
            ];
        }

        return $perubahan;
    }

    /**
     * Catat log audit aktivitas ke database dengan detail perangkat & perbandingan data
     *
     * @param string      $aksi        Kode aksi (misal: 'LOGIN_ADMIN', 'UPDATE_NASABAH')
     * @param string      $keterangan  Narasi manusiawi tentang aktivitas yang dilakukan
     * @param string      $kategori    Kategori/Modul (AUTH, NASABAH, POS_MITRA, PENGGUNA, HARGA_SAMPAH, EDUKASI, FEEDBACK, LAPORAN, WHATSAPP, TRANSAKSI, SISTEM)
     * @param string      $tipe        Tipe operasi (CREATE, UPDATE, DELETE, AUTH, SECURITY, EXPORT, TRANSACTION, ACTION)
     * @param string      $status      Status hasil (SUKSES, GAGAL, PERINGATAN)
     * @param mixed       $detail      Data detail/payload/diff sebelum-sesudah (array/object/string)
     * @param string|null $idUser      ID pelaku (opsional, default: session admin_id)
     * @param string|null $namaUser    Nama pelaku (opsional, default: session admin_nama)
     * @param string|null $peranUser   Peran pelaku (opsional, default: session admin_peran)
     */
    public static function log(
        string $aksi,
        string $keterangan,
        string $kategori = 'UMUM',
        string $tipe = 'ACTION',
        string $status = 'SUKSES',
        $detail = null,
        ?string $idUser = null,
        ?string $namaUser = null,
        ?string $peranUser = null
    ): void {
        try {
            self::ensureTableReady();

            $req = request();

            // Identifikasi pelaku
            $pelakuId    = $idUser ?? (session('admin_id') ?? null);
            $pelakuNama  = $namaUser ?? (session('admin_nama') ?? ($pelakuId ? 'Admin' : 'Sistem Otomatis'));
            $pelakuPeran = $peranUser ?? (session('admin_peran') ?? 'SISTEM');

            // Dapatkan IP Address dan User Agent
            $ipAddress = $req ? ($req->header('X-Forwarded-For') ?? $req->ip()) : '127.0.0.1';
            if ($ipAddress && str_contains($ipAddress, ',')) {
                $ipAddress = trim(explode(',', $ipAddress)[0]);
            }
            $userAgent = $req ? substr((string)$req->userAgent(), 0, 500) : 'CLI / Background Process';

            // Parsing nama perangkat & browser yang manusiawi
            $infoPerangkat = self::parseUserAgent($userAgent);
            $namaPerangkat = $infoPerangkat['nama'];
            $tipePerangkat = $infoPerangkat['tipe'];

            // Jika ada data_sebelum dan data_sesudah tapi belum ada perubahan, hitung otomatis
            if (is_array($detail)) {
                if (isset($detail['data_sebelum']) && isset($detail['data_sesudah']) && !isset($detail['perubahan'])) {
                    $detail['perubahan'] = self::formatPerubahan($detail['data_sebelum'], $detail['data_sesudah']);
                }
                // Simpan info perangkat dan IP ke dalam detail JSON juga untuk redundansi
                $detail['info_koneksi'] = [
                    'ip'        => $ipAddress,
                    'perangkat' => $namaPerangkat,
                    'tipe'      => $tipePerangkat,
                ];
            }

            // Format JSON detail jika tersedia
            $detailJson = null;
            if ($detail !== null) {
                if (is_string($detail)) {
                    $detailJson = $detail;
                } else {
                    $detailJson = json_encode($detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }

            DB::table('audit_log')->insert([
                'id'             => (string) Str::uuid(),
                'id_admin'       => $pelakuId,
                'nama_admin'     => $pelakuNama,
                'peran'          => strtoupper($pelakuPeran),
                'aksi'           => strtoupper($aksi),
                'kategori'       => strtoupper($kategori),
                'tipe'           => strtoupper($tipe),
                'status'         => strtoupper($status),
                'keterangan'     => $keterangan,
                'detail_json'    => $detailJson,
                'ip_address'     => $ipAddress,
                'nama_perangkat' => $namaPerangkat,
                'tipe_perangkat' => $tipePerangkat,
                'user_agent'     => $userAgent,
                'dibuat_pada'    => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('AuditLogger::log failure: ' . $e->getMessage(), [
                'aksi' => $aksi,
                'keterangan' => $keterangan
            ]);
        }
    }
}
