<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function loginForm()
    {
        $this->ensureSeparatedTablesExist();

        $dest = public_path('logo.png');
        if (!file_exists($dest)) {
            $src = base_path('../sirkulo-nasabah-app/app/src/main/res/drawable/logo.png');
            if (file_exists($src)) {
                @copy($src, $dest);
            }
        }

        if (Session::get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    private function ensureSeparatedTablesExist()
    {
        try {
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            // Hapus cache routes jika ada
            @unlink(base_path('bootstrap/cache/routes-v7.php'));
            @unlink(base_path('bootstrap/cache/config.php'));

            // 1. Tabel admin
            \Illuminate\Support\Facades\DB::statement("
                CREATE TABLE IF NOT EXISTS `admin` (
                    `id` CHAR(36) NOT NULL,
                    `nama` VARCHAR(255) NOT NULL,
                    `email` VARCHAR(255) NOT NULL UNIQUE,
                    `kata_sandi` VARCHAR(255) NOT NULL,
                    `telepon` VARCHAR(255) NULL,
                    `peran` VARCHAR(50) NOT NULL DEFAULT 'ADMIN',
                    `otp_reset` VARCHAR(10) NULL,
                    `kadaluarsa_otp` TIMESTAMP NULL,
                    `dibuat_pada` TIMESTAMP NULL,
                    `diperbarui_pada` TIMESTAMP NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            if (\Illuminate\Support\Facades\Schema::hasTable('admin')) {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('admin', 'otp_reset')) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `admin` ADD COLUMN `otp_reset` VARCHAR(10) NULL AFTER `peran`");
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('admin', 'kadaluarsa_otp')) {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `admin` ADD COLUMN `kadaluarsa_otp` TIMESTAMP NULL AFTER `otp_reset`");
                }
            }

            // 2. Tabel petugas_mitra
            \Illuminate\Support\Facades\DB::statement("
                CREATE TABLE IF NOT EXISTS `petugas_mitra` (
                    `id` CHAR(36) NOT NULL,
                    `nama` VARCHAR(255) NOT NULL,
                    `email` VARCHAR(255) NOT NULL UNIQUE,
                    `kata_sandi` VARCHAR(255) NOT NULL,
                    `telepon` VARCHAR(255) NOT NULL,
                    `alamat` VARCHAR(255) NULL,
                    `saldo` BIGINT NOT NULL DEFAULT 0,
                    `foto_url` VARCHAR(255) NULL,
                    `peran` VARCHAR(50) NOT NULL DEFAULT 'MITRA',
                    `dibuat_pada` TIMESTAMP NULL,
                    `diperbarui_pada` TIMESTAMP NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // 3. Tabel nasabah
            \Illuminate\Support\Facades\DB::statement("
                CREATE TABLE IF NOT EXISTS `nasabah` (
                    `id` CHAR(36) NOT NULL,
                    `nama` VARCHAR(255) NOT NULL,
                    `email` VARCHAR(255) NOT NULL UNIQUE,
                    `kata_sandi` VARCHAR(255) NOT NULL,
                    `telepon` VARCHAR(255) NOT NULL,
                    `alamat` VARCHAR(255) NULL,
                    `saldo` BIGINT NOT NULL DEFAULT 0,
                    `poin` INT NOT NULL DEFAULT 0,
                    `foto_url` VARCHAR(255) NULL,
                    `peran` VARCHAR(50) NOT NULL DEFAULT 'NASABAH',
                    `otp_reset` VARCHAR(10) NULL,
                    `kadaluarsa_otp` TIMESTAMP NULL,
                    `dibuat_pada` TIMESTAMP NULL,
                    `diperbarui_pada` TIMESTAMP NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            // Salin data jika ada tabel pengguna
            if (\Illuminate\Support\Facades\Schema::hasTable('pengguna')) {
                \Illuminate\Support\Facades\DB::statement("
                    INSERT IGNORE INTO `admin` (`id`, `nama`, `email`, `kata_sandi`, `telepon`, `peran`, `dibuat_pada`, `diperbarui_pada`)
                    SELECT `id`, `nama`, `email`, `kata_sandi`, `telepon`, 'ADMIN', `dibuat_pada`, `diperbarui_pada`
                    FROM `pengguna` WHERE `peran` = 'ADMIN'
                ");

                \Illuminate\Support\Facades\DB::statement("
                    INSERT IGNORE INTO `petugas_mitra` (`id`, `nama`, `email`, `kata_sandi`, `telepon`, `alamat`, `saldo`, `foto_url`, `peran`, `dibuat_pada`, `diperbarui_pada`)
                    SELECT `id`, `nama`, `email`, `kata_sandi`, `telepon`, `alamat`, `saldo`, `foto_url`, 'MITRA', `dibuat_pada`, `diperbarui_pada`
                    FROM `pengguna` WHERE `peran` = 'MITRA'
                ");

                \Illuminate\Support\Facades\DB::statement("
                    INSERT IGNORE INTO `nasabah` (`id`, `nama`, `email`, `kata_sandi`, `telepon`, `alamat`, `saldo`, `poin`, `foto_url`, `peran`, `otp_reset`, `kadaluarsa_otp`, `dibuat_pada`, `diperbarui_pada`)
                    SELECT `id`, `nama`, `email`, `kata_sandi`, `telepon`, `alamat`, `saldo`, `poin`, `foto_url`, 'NASABAH', `otp_reset`, `kadaluarsa_otp`, `dibuat_pada`, `diperbarui_pada`
                    FROM `pengguna` WHERE `peran` = 'NASABAH' OR `peran` IS NULL OR `peran` = ''
                ");
            }

            // ── Pastikan Akun Default Super Admin & Admin Operasional Tersedia ──
            // 1. Akun Super Admin
            $superAdminExists = \Illuminate\Support\Facades\DB::table('admin')->where('email', 'superadmin@sirkulo.id')->first();
            if (!$superAdminExists) {
                \Illuminate\Support\Facades\DB::table('admin')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'nama'            => 'Super Administrator',
                    'email'           => 'superadmin@sirkulo.id',
                    'kata_sandi'      => \Illuminate\Support\Facades\Hash::make('superadmin2026'),
                    'telepon'         => '081200001111',
                    'peran'           => 'SUPER_ADMIN',
                    'dibuat_pada'     => now(),
                    'diperbarui_pada' => now(),
                ]);
            }

            // 2. Akun Admin Operasional
            $adminExists = \Illuminate\Support\Facades\DB::table('admin')->where('email', 'admin@sirkulo.id')->first();
            if (!$adminExists) {
                \Illuminate\Support\Facades\DB::table('admin')->insert([
                    'id'              => (string) \Illuminate\Support\Str::uuid(),
                    'nama'            => 'Admin Operasional Desa',
                    'email'           => 'admin@sirkulo.id',
                    'kata_sandi'      => \Illuminate\Support\Facades\Hash::make('admindesa2026'),
                    'telepon'         => '081234567890',
                    'peran'           => 'ADMIN',
                    'dibuat_pada'     => now(),
                    'diperbarui_pada' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::error('Gagal inisialisasi tabel terpisah: ' . $e->getMessage());
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        // Ambil user LEBIH DULU sebelum AuditLogger dipanggil
        $user = Admin::where('email', $request->email)->first();

        // Parse perangkat & IP (dibungkus try-catch agar tidak crash jika AuditLogger error)
        $perangkat = 'Perangkat Tidak Dikenal';
        $ip = '0.0.0.0';
        try {
            $ua = $request->userAgent();
            $infoPerangkat = \App\Services\AuditLogger::parseUserAgent($ua);
            $perangkat = $infoPerangkat['nama'];
            $ip = $request->header('X-Forwarded-For') ?? $request->ip();
            if ($ip && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
        } catch (\Throwable $e) {
            \Log::warning('AuditLogger parseUserAgent error: ' . $e->getMessage());
        }

        if (!$user || !Hash::check($request->password, $user->kata_sandi)) {
            \App\Services\AuditLogger::log(
                'GAGAL_LOGIN_ADMIN',
                "Percobaan login GAGAL untuk email '{$request->email}' dari {$perangkat} (IP: {$ip}) — Kata sandi tidak cocok.",
                'AUTH',
                'SECURITY',
                'GAGAL',
                [
                    'email_input'    => $request->email,
                    'ip_address'     => $ip,
                    'nama_perangkat' => $perangkat,
                    'alasan'         => 'Kredensial atau kata sandi tidak cocok'
                ]
            );
            return back()->withErrors(['email' => 'Email atau password salah, atau akun bukan Admin.'])->withInput();
        }

        Session::put('admin_logged_in', true);
        Session::put('admin_id', $user->id);
        Session::put('admin_nama', $user->nama);
        Session::put('admin_email', $user->email);
        Session::put('admin_peran', strtoupper($user->peran ?: 'ADMIN'));

        \App\Services\AuditLogger::log(
            'LOGIN_ADMIN',
            "Administrator '{$user->nama}' ({$user->email}) berhasil login ke sistem web dari {$perangkat} (IP: {$ip}).",
            'AUTH',
            'AUTH',
            'SUKSES',
            [
                'email'           => $user->email,
                'peran'           => $user->peran,
                'nama_perangkat'  => $perangkat,
                'ip_address'      => $ip,
                'waktu_login'     => now()->format('Y-m-d H:i:s'),
            ],
            $user->id,
            $user->nama,
            $user->peran
        );

        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        $idAdmin   = session('admin_id');
        $namaAdmin = session('admin_nama');
        $peran     = session('admin_peran');

        $ua = request()->userAgent();
        $infoPerangkat = \App\Services\AuditLogger::parseUserAgent($ua);
        $perangkat = $infoPerangkat['nama'];
        $ip = request()->header('X-Forwarded-For') ?? request()->ip();
        if ($ip && str_contains($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
        }

        if ($namaAdmin) {
            \App\Services\AuditLogger::log(
                'LOGOUT_ADMIN',
                "Administrator '{$namaAdmin}' telah keluar (logout) dari sesi Web Admin dari {$perangkat} (IP: {$ip}).",
                'AUTH',
                'AUTH',
                'SUKSES',
                [
                    'nama'           => $namaAdmin,
                    'peran'          => $peran,
                    'ip_address'     => $ip,
                    'nama_perangkat' => $perangkat,
                ],
                $idAdmin,
                $namaAdmin,
                $peran
            );
        }

        Session::forget(['admin_logged_in', 'admin_id', 'admin_nama', 'admin_email', 'admin_peran']);
        return redirect()->route('admin.login')->with('success', 'Berhasil logout.');
    }

    // ── Lupa Kata Sandi Super Admin via OTP Gmail ────────────────────────

    public function showForgotPasswordForm()
    {
        $this->ensureSeparatedTablesExist();

        if (Session::get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        $targetEmail = 'sirkuloapp@gmail.com';
        return view('admin.lupa_password', compact('targetEmail'));
    }

    public function sendResetOtp(Request $request)
    {
        $this->ensureSeparatedTablesExist();

        $targetEmail = 'sirkuloapp@gmail.com';

        // Cari akun Super Admin
        $superAdmin = Admin::where('peran', 'SUPER_ADMIN')
            ->orWhere('email', 'superadmin@sirkulo.id')
            ->first();

        if (!$superAdmin) {
            // Jika belum ada, buat otomatis
            $superAdmin = Admin::create([
                'nama'       => 'Super Administrator',
                'email'      => 'superadmin@sirkulo.id',
                'kata_sandi' => Hash::make('superadmin2026'),
                'telepon'    => '081200001111',
                'peran'      => 'SUPER_ADMIN',
            ]);
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $expiry = now()->addMinutes(10);

        // Simpan OTP ke database
        DB::table('admin')->where('id', $superAdmin->id)->update([
            'otp_reset'      => $otp,
            'kadaluarsa_otp' => $expiry,
            'diperbarui_pada' => now(),
        ]);

        // Catat info perangkat & IP
        $perangkat = 'Perangkat Tidak Dikenal';
        $ip = '0.0.0.0';
        try {
            $ua = $request->userAgent();
            $infoPerangkat = \App\Services\AuditLogger::parseUserAgent($ua);
            $perangkat = $infoPerangkat['nama'];
            $ip = $request->header('X-Forwarded-For') ?? $request->ip();
            if ($ip && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
        } catch (\Throwable $e) {}

        $waktu = now()->format('d M Y, H:i');

        // Template HTML Email Resmi
        $htmlEmail = "
        <!DOCTYPE html>
        <html>
        <head><meta charset='utf-8'></head>
        <body style=\"margin:0;padding:24px;background-color:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;color:#1e293b;\">
            <div style=\"max-width:520px;margin:0 auto;background:#ffffff;border-radius:18px;border:1px solid #e2e8f0;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.06);\">
                <div style=\"background:linear-gradient(135deg, #064e3b 0%, #059669 100%);padding:28px 24px;text-align:center;\">
                    <h1 style=\"color:#ffffff;margin:0;font-size:24px;font-weight:900;letter-spacing:-0.5px;\">SIRKULO</h1>
                    <p style=\"color:#a7f3d0;margin:6px 0 0 0;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1.5px;\">Bank Sampah Desa & Ekonomi Sirkular</p>
                </div>
                <div style=\"padding:32px 28px;\">
                    <h2 style=\"color:#0f172a;margin:0 0 10px 0;font-size:18px;font-weight:800;\">Kode OTP Pemulihan Kata Sandi</h2>
                    <p style=\"color:#64748b;font-size:13px;line-height:1.6;margin:0 0 24px 0;\">
                        Halo Administrator,<br>
                        Kami menerima permintaan untuk mereset kata sandi akun <strong>Super Administrator</strong>. Masukkan 6 digit kode verifikasi berikut pada halaman konfirmasi:
                    </p>
                    <div style=\"background:#f0fdf4;border:2px dashed #34d399;border-radius:14px;padding:20px;text-align:center;margin-bottom:24px;\">
                        <span style=\"font-size:11px;font-weight:700;text-transform:uppercase;color:#065f46;letter-spacing:1.2px;display:block;margin-bottom:8px;\">Kode Verifikasi OTP Anda</span>
                        <span style=\"font-size:38px;font-weight:900;letter-spacing:8px;color:#047857;font-family:'Courier New',monospace;display:block;\">{$otp}</span>
                        <span style=\"font-size:11.5px;color:#059669;display:block;margin-top:8px;font-weight:600;\">⏱ Berlaku selama 10 menit</span>
                    </div>
                    <div style=\"background:#fffbeb;border-left:4px solid #f59e0b;padding:12px 16px;border-radius:8px;margin-bottom:24px;\">
                        <p style=\"margin:0;font-size:12px;color:#92400e;line-height:1.5;\">
                            <strong>Peringatan Keamanan:</strong> Jangan berikan kode ini kepada siapa pun. Petugas Bank Sampah Desa tidak akan pernah meminta kode OTP Anda.
                        </p>
                    </div>
                    <p style=\"font-size:11.5px;color:#94a3b8;line-height:1.6;margin:0;\">
                        Waktu Permintaan: <strong>{$waktu} WIB</strong><br>
                        Alamat IP: <strong>{$ip}</strong> ({$perangkat})
                    </p>
                </div>
                <div style=\"background:#f8fafc;border-top:1px solid #e2e8f0;padding:16px 24px;text-align:center;\">
                    <p style=\"font-size:11px;color:#94a3b8;margin:0;\">
                        Pesan ini dikirim secara otomatis ke <strong>{$targetEmail}</strong> oleh Sistem SIRKULO.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Kirim email via Mailer Laravel
        $emailSent = false;
        try {
            \Illuminate\Support\Facades\Mail::html($htmlEmail, function ($msg) use ($targetEmail, $otp) {
                $fromAddress = config('mail.from.address') ?: 'sirkuloapp@gmail.com';
                $fromName = config('mail.from.name') ?: 'SIRKULO Bank Sampah';
                $msg->from($fromAddress, $fromName)
                    ->to($targetEmail)
                    ->subject("[SIRKULO] Kode OTP Reset Kata Sandi Super Admin: {$otp}");
            });
            $emailSent = true;
        } catch (\Throwable $e) {
            \Log::warning("Gagal mengirim email OTP ke {$targetEmail}: " . $e->getMessage());
        }

        // Catat ke log audit aktivitas
        \App\Services\AuditLogger::log(
            'REQUEST_OTP_SUPER_ADMIN',
            "Permintaan OTP reset kata sandi Super Admin diajukan dari {$perangkat} (IP: {$ip}) menuju email {$targetEmail}. Status kirim: " . ($emailSent ? 'Terkirim' : 'Disimpan di log sistem'),
            'AUTH',
            'SECURITY',
            'SUKSES',
            [
                'target_email'   => $targetEmail,
                'ip_address'     => $ip,
                'nama_perangkat' => $perangkat,
                'email_sent'     => $emailSent,
            ]
        );

        Session::put('reset_otp_target', $targetEmail);
        Session::put('reset_otp_sent_at', now()->timestamp);

        // Jika di lingkungan lokal / SMTP belum aktif, sertakan flash OTP agar bisa langsung dicoba
        if (app()->environment('local') || config('app.debug')) {
            session()->flash('dev_otp_notice', "Kode OTP Anda: {$otp} (Juga telah dikirim ke {$targetEmail})");
        }

        return redirect()->route('admin.lupa-password.verifikasi')->with(
            'success',
            "✅ Kode OTP 6-digit telah dikirimkan ke {$targetEmail}. Silakan periksa kotak masuk atau folder spam Gmail Anda."
        );
    }

    public function showVerifyOtpForm()
    {
        $this->ensureSeparatedTablesExist();

        if (Session::get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        $superAdmin = Admin::where('peran', 'SUPER_ADMIN')
            ->orWhere('email', 'superadmin@sirkulo.id')
            ->first();

        $activeOtp = null;
        if ($superAdmin && $superAdmin->kadaluarsa_otp && now()->lessThan($superAdmin->kadaluarsa_otp)) {
            $activeOtp = $superAdmin->otp_reset;
        }

        $targetEmail = Session::get('reset_otp_target', 'sirkuloapp@gmail.com');
        $isSmtpConfigured = config('mail.default') === 'smtp' && !empty(env('MAIL_PASSWORD'));

        return view('admin.verifikasi_otp', compact('targetEmail', 'activeOtp', 'isSmtpConfigured'));
    }

    public function verifyOtpOnly(Request $request)
    {
        $this->ensureSeparatedTablesExist();

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size'     => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        $superAdmin = Admin::where('peran', 'SUPER_ADMIN')
            ->orWhere('email', 'superadmin@sirkulo.id')
            ->first();

        if (!$superAdmin || empty($superAdmin->otp_reset)) {
            return back()->withErrors(['otp' => 'Permintaan OTP tidak ditemukan. Silakan minta kode OTP baru.'])->withInput();
        }

        // Cek kedaluwarsa OTP (10 menit)
        if ($superAdmin->kadaluarsa_otp && now()->greaterThan($superAdmin->kadaluarsa_otp)) {
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa (berlaku 10 menit). Silakan klik "Kirim Ulang OTP".'])->withInput();
        }

        // Cek kecocokan OTP
        if (trim($superAdmin->otp_reset) !== trim($request->otp)) {
            return back()->withErrors(['otp' => 'Kode OTP yang Anda masukkan salah. Periksa kembali email sirkuloapp@gmail.com.'])->withInput();
        }

        // OTP Valid! Simpan ke sesi bahwa tahap OTP sudah lolos
        Session::put('reset_otp_verified', true);
        Session::put('reset_admin_id', $superAdmin->id);

        return redirect()->route('admin.lupa-password.ganti-sandi')->with(
            'success',
            '✅ Kode OTP berhasil diverifikasi! Silakan buat kata sandi baru Anda di bawah ini.'
        );
    }

    public function showResetPasswordForm()
    {
        $this->ensureSeparatedTablesExist();

        if (Session::get('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        if (!Session::get('reset_otp_verified') || !Session::get('reset_admin_id')) {
            return redirect()->route('admin.lupa-password')->withErrors([
                'email' => 'Silakan lakukan verifikasi kode OTP terlebih dahulu sebelum mengganti kata sandi.'
            ]);
        }

        return view('admin.ganti_sandi');
    }

    public function saveNewPassword(Request $request)
    {
        $this->ensureSeparatedTablesExist();

        if (!Session::get('reset_otp_verified') || !Session::get('reset_admin_id')) {
            return redirect()->route('admin.lupa-password')->withErrors([
                'email' => 'Sesi verifikasi OTP Anda telah berakhir. Silakan minta kode OTP baru.'
            ]);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $adminId = Session::get('reset_admin_id');
        $superAdmin = Admin::find($adminId);

        if (!$superAdmin) {
            return redirect()->route('admin.lupa-password')->withErrors([
                'email' => 'Akun Super Admin tidak ditemukan.'
            ]);
        }

        // Update password baru & bersihkan OTP di DB
        DB::table('admin')->where('id', $superAdmin->id)->update([
            'kata_sandi'      => Hash::make($request->password),
            'otp_reset'       => null,
            'kadaluarsa_otp'  => null,
            'diperbarui_pada' => now(),
        ]);

        // Catat info audit
        $perangkat = 'Perangkat Tidak Dikenal';
        $ip = '0.0.0.0';
        try {
            $ua = $request->userAgent();
            $infoPerangkat = \App\Services\AuditLogger::parseUserAgent($ua);
            $perangkat = $infoPerangkat['nama'];
            $ip = $request->header('X-Forwarded-For') ?? $request->ip();
            if ($ip && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }
        } catch (\Throwable $e) {}

        \App\Services\AuditLogger::log(
            'RESET_PASSWORD_SUPER_ADMIN_SUKSES',
            "Kata sandi akun Super Administrator berhasil diperbarui melalui verifikasi OTP Gmail dari {$perangkat} (IP: {$ip}).",
            'AUTH',
            'SECURITY',
            'SUKSES',
            [
                'email'          => $superAdmin->email,
                'ip_address'     => $ip,
                'nama_perangkat' => $perangkat,
                'waktu_reset'    => now()->format('Y-m-d H:i:s'),
            ],
            $superAdmin->id,
            $superAdmin->nama,
            'SUPER_ADMIN'
        );

        Session::forget(['reset_otp_target', 'reset_otp_sent_at', 'reset_otp_verified', 'reset_admin_id']);

        return redirect()->route('admin.login')->with(
            'success',
            '🎉 Kata sandi Super Administrator berhasil diperbarui! Silakan masuk menggunakan kata sandi baru Anda.'
        );
    }
}
