<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminEducationController;
use App\Http\Controllers\Admin\AdminFeedbackController;
use App\Http\Controllers\Admin\AdminHargaSampahController;
use App\Http\Controllers\Admin\AdminLaporanController;
use App\Http\Controllers\Admin\AdminMitraController;
use App\Http\Controllers\Admin\AdminNasabahController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Admin\AdminUserController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

// Route logo resmi SIRKULO (menggunakan logo dari aplikasi mobile)
Route::get('/logo.png', function () {
    $dest = public_path('logo.png');
    if (!file_exists($dest)) {
        $src = base_path('../sirkulo-nasabah-app/app/src/main/res/drawable/logo.png');
        if (file_exists($src)) {
            @copy($src, $dest);
        }
    }
    if (file_exists($dest)) {
        return response()->file($dest, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
    $src = base_path('../sirkulo-nasabah-app/app/src/main/res/drawable/logo.png');
    if (file_exists($src)) {
        return response()->file($src, [
            'Content-Type'  => 'image/png',
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
    abort(404);
});

// Route untuk menyajikan file upload (foto edukasi, foto profil, dll)
Route::get('storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }
    $mime = mime_content_type($fullPath) ?: 'image/jpeg';
    return response()->file($fullPath, [
        'Content-Type'  => $mime,
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->where('path', '.*');

// ── Route Publik: Verifikasi Keaslian & Anti-Manipulasi Dokumen Laporan Resmi ──
Route::get('/verifikasi-laporan', [AdminLaporanController::class, 'verifikasiPublic'])->name('laporan.verifikasi');

// ── Admin Panel Routes ─────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Auth (tidak perlu login)
    Route::get('/login', [AdminAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // Pemulihan Kata Sandi Super Admin via OTP Gmail (Alur Bertahap: Minta OTP -> Verifikasi OTP -> Ganti Sandi)
    Route::get('/lupa-password', [AdminAuthController::class, 'showForgotPasswordForm'])->name('lupa-password');
    Route::post('/lupa-password/kirim-otp', [AdminAuthController::class, 'sendResetOtp'])->name('lupa-password.kirim-otp');
    Route::get('/lupa-password/verifikasi', [AdminAuthController::class, 'showVerifyOtpForm'])->name('lupa-password.verifikasi');
    Route::post('/lupa-password/verifikasi', [AdminAuthController::class, 'verifyOtpOnly'])->name('lupa-password.verifikasi.post');
    Route::get('/lupa-password/ganti-sandi', [AdminAuthController::class, 'showResetPasswordForm'])->name('lupa-password.ganti-sandi');
    Route::post('/lupa-password/ganti-sandi', [AdminAuthController::class, 'saveNewPassword'])->name('lupa-password.ganti-sandi.post');

    // Protected routes (perlu login admin)
    Route::middleware('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Kelola Nasabah
        Route::get('/nasabah', [AdminNasabahController::class, 'index'])->name('nasabah.index');
        Route::get('/nasabah/create', [AdminNasabahController::class, 'create'])->name('nasabah.create');
        Route::post('/nasabah', [AdminNasabahController::class, 'store'])->name('nasabah.store');
        Route::post('/nasabah/{id}/reset-password', [AdminNasabahController::class, 'resetPassword'])->name('nasabah.resetPassword');
        Route::get('/nasabah/{id}', [AdminNasabahController::class, 'show'])->name('nasabah.show');
        Route::get('/nasabah/{id}/edit', [AdminNasabahController::class, 'edit'])->name('nasabah.edit');
        Route::put('/nasabah/{id}', [AdminNasabahController::class, 'update'])->name('nasabah.update');
        Route::delete('/nasabah/{id}', [AdminNasabahController::class, 'destroy'])->name('nasabah.destroy');

        // Kelola Mitra & Pos
        Route::get('/mitra', [AdminMitraController::class, 'index'])->name('mitra.index');
        Route::post('/mitra', [AdminMitraController::class, 'store'])->name('mitra.store');
        Route::get('/mitra/{id}', [AdminMitraController::class, 'show'])->name('mitra.show');
        Route::put('/mitra/{id}', [AdminMitraController::class, 'update'])->name('mitra.update');
        Route::delete('/mitra/{id}', [AdminMitraController::class, 'destroy'])->name('mitra.destroy');

        // Kelola Harga Sampah
        Route::get('/harga-sampah', [AdminHargaSampahController::class, 'index'])->name('harga-sampah.index');
        Route::post('/harga-sampah', [AdminHargaSampahController::class, 'store'])->name('harga-sampah.store');
        Route::put('/harga-sampah/{id}', [AdminHargaSampahController::class, 'update'])->name('harga-sampah.update');
        Route::delete('/harga-sampah/{id}/foto', [AdminHargaSampahController::class, 'deletePhoto'])->name('harga-sampah.deletePhoto');
        Route::delete('/harga-sampah/{id}', [AdminHargaSampahController::class, 'destroy'])->name('harga-sampah.destroy');

        // Edukasi Warga
        Route::get('/edukasi', [AdminEducationController::class, 'index'])->name('edukasi.index');
        Route::post('/edukasi', [AdminEducationController::class, 'store'])->name('edukasi.store');
        Route::put('/edukasi/{id}', [AdminEducationController::class, 'update'])->name('edukasi.update');
        Route::delete('/edukasi/{id}', [AdminEducationController::class, 'destroy'])->name('edukasi.destroy');

        // Kritik & Saran Warga
        Route::get('/feedback', [AdminFeedbackController::class, 'index'])->name('feedback.index');
        Route::post('/feedback/{id}/reply', [AdminFeedbackController::class, 'reply'])->name('feedback.reply');
        Route::delete('/feedback/{id}', [AdminFeedbackController::class, 'destroy'])->name('feedback.destroy');

        // Laporan & Rekap
        Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/export-csv', [AdminLaporanController::class, 'exportCsv'])->name('laporan.exportCsv');
        Route::get('/laporan/export-excel', [AdminLaporanController::class, 'exportExcel'])->name('laporan.exportExcel');

        // Panduan & Integrasi Portal Satu Jari DLH
        Route::get('/portal-satu-jari', function () {
            $email = \App\Models\Setting::get('dlh_portal_email', 'srikandiberdikari@gmail.com');
            $password = \App\Models\Setting::get('dlh_portal_password', '123456');
            $portalUrl = "https://jari-satu.web.app/loginlink/" . rawurlencode($email) . "/" . rawurlencode($password);
            return view('admin.portal_satu_jari', compact('email', 'password', 'portalUrl'));
        })->name('portal-satu-jari');

        // Update Kredensial Portal Satu Jari (Khusus Super Admin)
        Route::post('/portal-satu-jari/update-kredensial', function (\Illuminate\Http\Request $request) {
            if (session('admin_peran') !== 'SUPER_ADMIN') {
                return redirect()->back()->with('error', 'Hanya Super Admin yang memiliki wewenang untuk mengubah password dan kredensial Portal Satu Jari DLH.');
            }

            $validated = $request->validate([
                'email'    => 'required|email|max:120',
                'password' => 'required|string|min:4|max:50',
            ], [
                'email.required'    => 'Email DLH wajib diisi.',
                'email.email'       => 'Format email DLH tidak valid.',
                'password.required' => 'Password DLH wajib diisi.',
                'password.min'      => 'Password minimal 4 karakter.',
            ]);

            \App\Models\Setting::set('dlh_portal_email', trim($validated['email']), 'Email Akun Portal Satu Jari DLH');
            \App\Models\Setting::set('dlh_portal_password', trim($validated['password']), 'Password Akun Portal Satu Jari DLH');

            try {
                if (class_exists(\App\Services\AuditLogger::class)) {
                    \App\Services\AuditLogger::log(
                        'UPDATE_PORTAL_DLH',
                        "Super Admin memperbarui kredensial Portal Satu Jari DLH (Email: {$validated['email']}).",
                        'SISTEM',
                        'UPDATE',
                        'SUKSES',
                        ['email' => $validated['email']]
                    );
                }
            } catch (\Throwable $e) {}

            return redirect()->back()->with('success', 'Password dan kredensial Portal Satu Jari DLH berhasil diperbarui!');
        })->name('portal-satu-jari.update');

        // ── 1. Kelola Petugas Pos Lapangan ──────────────────────────────
        Route::get('/petugas', [AdminUserController::class, 'indexPetugas'])->name('petugas.index');
        Route::post('/petugas', [AdminUserController::class, 'storeMitra'])->name('petugas.store');
        Route::put('/petugas/{id}', [AdminUserController::class, 'updateMitra'])->name('petugas.update');
        Route::put('/petugas/{id}/pos', [AdminUserController::class, 'assignPos'])->name('petugas.assignPos');
        Route::post('/petugas/{id}/pos/unlink', [AdminUserController::class, 'unlinkPos'])->name('petugas.unlinkPos');
        Route::delete('/petugas/{id}', [AdminUserController::class, 'destroyMitra'])->name('petugas.destroy');
        Route::put('/petugas/{id}/password', [AdminUserController::class, 'updatePasswordPetugas'])->name('petugas.updatePassword');

        // ── Pengaturan Akun & Ganti Kata Sandi Pribadi (Semua Admin) ──────
        Route::get('/pengaturan/sandi', [AdminUserController::class, 'viewGantiSandi'])->name('pengaturan.sandi');
        Route::put('/pengaturan/sandi', [AdminUserController::class, 'updateGantiSandi'])->name('pengaturan.updateSandi');

        // Alias & Backward Compatibility: Pengguna
        Route::get('/pengguna', fn() => redirect()->route('admin.petugas.index'))->name('pengguna.index');
        Route::post('/pengguna/mitra', [AdminUserController::class, 'storeMitra'])->name('pengguna.storeMitra');
        Route::put('/pengguna/mitra/{id}', [AdminUserController::class, 'updateMitra'])->name('pengguna.updateMitra');
        Route::put('/pengguna/mitra/{id}/pos', [AdminUserController::class, 'assignPos'])->name('pengguna.assignPos');
        Route::post('/pengguna/mitra/{id}/pos/unlink', [AdminUserController::class, 'unlinkPos'])->name('pengguna.unlinkPos');
        Route::delete('/pengguna/mitra/{id}', [AdminUserController::class, 'destroyMitra'])->name('pengguna.destroyMitra');
        Route::put('/pengguna/{id}/password', [AdminUserController::class, 'updatePassword'])->name('pengguna.updatePassword');

        // ── Khusus Super Admin ─────────────────────────────────────────────
        Route::middleware('superadmin')->group(function () {
            // Kelola Administrator Desa
            Route::get('/administrator', [AdminUserController::class, 'indexAdmin'])->name('administrator.index');
            Route::put('/administrator/{id}/password', [AdminUserController::class, 'updatePasswordAdmin'])->name('administrator.updatePassword');
            Route::post('/administrator', function () {
                abort(403, 'Penambahan akun administrator baru tidak diizinkan.');
            })->name('administrator.store');
            Route::delete('/administrator/{id}', [AdminUserController::class, 'destroyAdmin'])->name('administrator.destroy');
            Route::put('/administrator/{id}/promote', [AdminUserController::class, 'promoteAdmin'])->name('administrator.promote');
            Route::put('/administrator/{id}/demote', [AdminUserController::class, 'demoteAdmin'])->name('administrator.demote');

            // Log Aktivitas & Audit Trail (Khusus Super Admin)
            Route::get('/aktivitas', [\App\Http\Controllers\Admin\AdminAktivitasController::class, 'index'])->name('aktivitas.index');
            Route::get('/aktivitas/export-csv', [\App\Http\Controllers\Admin\AdminAktivitasController::class, 'exportCsv'])->name('aktivitas.exportCsv');

            // Legacy routes
            Route::post('/pengguna/admin', function () {
                abort(403, 'Penambahan akun administrator baru tidak diizinkan.');
            })->name('pengguna.storeAdmin');
            Route::delete('/pengguna/admin/{id}', [AdminUserController::class, 'destroyAdmin'])->name('pengguna.destroyAdmin');
            Route::put('/pengguna/admin/{id}/promote', [AdminUserController::class, 'promoteAdmin'])->name('pengguna.promoteAdmin');
            Route::put('/pengguna/admin/{id}/demote', [AdminUserController::class, 'demoteAdmin'])->name('pengguna.demoteAdmin');

            // Sistem, Diagnostik, Maintenance & Backup
            Route::get('/sistem', [AdminSystemController::class, 'index'])->name('sistem.index');
            Route::post('/sistem/clear-cache', [AdminSystemController::class, 'clearCache'])->name('sistem.clearCache');
            Route::post('/sistem/optimize', [AdminSystemController::class, 'optimize'])->name('sistem.optimize');
            Route::post('/sistem/storage-link', [AdminSystemController::class, 'storageLink'])->name('sistem.storageLink');
            Route::post('/sistem/run-migrations', [AdminSystemController::class, 'runMigrations'])->name('sistem.runMigrations');
            Route::post('/sistem/optimize-tables', [AdminSystemController::class, 'optimizeTables'])->name('sistem.optimizeTables');
            Route::get('/sistem/backup-db', [AdminSystemController::class, 'backupDatabase'])->name('sistem.backupDb');
        });
    });
});
