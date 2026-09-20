<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DataController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\MitraController;

Route::get('health', function () {
    return response()->json([
        'status' => 'ok',
        'app'    => 'SIRKULO Backend API',
        'server_ip' => gethostbyname(gethostname()),
        'secure' => request()->isSecure(),
        'time'   => now()->toDateTimeString(),
    ]);
});

Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
    Route::post('register',         [AuthController::class, 'register']);
    Route::post('login',            [AuthController::class, 'login']);
    Route::post('forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',   [AuthController::class, 'resetPassword']);
});

// ── PUBLIC DATA (Read-Only, tanpa login) ─────────────────────────────────
Route::get('trash-prices',  [DataController::class, 'getTrashPrices']);
Route::get('partners',      [DataController::class, 'getPartners']);
Route::get('educations',    [DataController::class, 'getEducations']);

// ── PROTECTED ROUTES (Sanctum Token + Throttle: max 60 req / min) ──────
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // ── Auth & Session ──────────────────────────────────────────────
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // ── Profil User (Nasabah & Mitra) ──────────────────────────────
    Route::get('user/profile',    [UserController::class, 'profile']);
    Route::put('user/profile',    [UserController::class, 'updateProfile']);
    Route::post('user/photo',     [UserController::class, 'updatePhoto']);
    Route::put('user/password',   [UserController::class, 'changePassword']);

    // ── Transaksi Nasabah ───────────────────────────────────────────
    // Hanya NASABAH sendiri yang bisa melihat transaksi miliknya
    Route::middleware('role:NASABAH')->group(function () {
        Route::get('transactions',          [TransactionController::class, 'index']);
        Route::get('transactions/{id}',     [TransactionController::class, 'show']);
        Route::post('transactions',         [TransactionController::class, 'store']);
    });

    // ── Notifikasi (Nasabah & Mitra) ────────────────────────────────
    Route::get('notifications',                     [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read',            [NotificationController::class, 'markRead']);
    Route::post('notifications/read-all',            [NotificationController::class, 'markAllRead']);
    Route::delete('notifications/{id}',              [NotificationController::class, 'destroy']);

    // ── Feedback Nasabah (Lihat Riwayat & Kirim Masukan) ────────────
    Route::get('feedback',                           [DataController::class, 'getFeedbacks']);
    Route::post('feedback',                          [DataController::class, 'submitFeedback']);

    // ── Master Data Sampah (Read oleh semua, Write oleh Admin) ──────
    Route::middleware('role:ADMIN,SUPER_ADMIN')->group(function () {
        Route::post('trash-prices',              [DataController::class, 'storeTrashPrice']);
        Route::post('trash-prices/upload-photo', [DataController::class, 'uploadTrashPhoto']);
        Route::put('trash-prices/{id}',          [DataController::class, 'updateTrashPrice']);
        Route::delete('trash-prices/{id}',       [DataController::class, 'destroyTrashPrice']);

        Route::post('partners',                  [DataController::class, 'storePartner']);
        Route::delete('partners/{id}',           [DataController::class, 'destroyPartner']);

        Route::post('educations',                [DataController::class, 'storeEducation']);
        Route::post('educations/upload-photo',   [DataController::class, 'uploadEducationPhoto']);
        Route::put('educations/{id}',            [DataController::class, 'updateEducation']);
        Route::delete('educations/{id}',         [DataController::class, 'destroyEducation']);

        // Feedback — admin bisa balas dan hapus
        Route::post('feedback/{id}/jawab',       [DataController::class, 'replyFeedback']);
        Route::delete('feedback/{id}',           [DataController::class, 'destroyFeedback']);

        // Ringkasan statistik untuk dashboard web
        Route::get('admin/summary',              [AdminApiController::class, 'summary']);
    });

    // ── Super Admin Only ────────────────────────────────────────────
    Route::middleware('role:SUPER_ADMIN')->group(function () {
        Route::get('admin/audit-log',            [AdminApiController::class, 'auditLog']);
        Route::put('admin/{id}/promote',         [AdminApiController::class, 'promote']);
        Route::put('admin/{id}/demote',          [AdminApiController::class, 'demote']);
    });

    // ── Mitra Routes (Petugas Lapangan) ────────────────────────────
    // Semua endpoint mitra dilindungi: hanya MITRA yang boleh akses
    Route::prefix('mitra')->middleware('role:MITRA')->group(function () {
        Route::get('pos-list',                [MitraController::class, 'listPos']);
        Route::get('dashboard',               [MitraController::class, 'dashboard']);
        Route::get('nasabah',                 [MitraController::class, 'daftarNasabah']);
        Route::get('nasabah/{id}',            [MitraController::class, 'detailNasabah']);

        // DILARANG UNTUK MITRA: registrasi & edit nasabah → 403 oleh controller
        Route::post('nasabah',                [MitraController::class, 'registrasiNasabah']);
        Route::put('nasabah/{id}',            [MitraController::class, 'updateNasabah']);
        Route::put('nasabah/{id}/password',   [MitraController::class, 'updateNasabahPassword']);
        Route::delete('nasabah/{id}',         [MitraController::class, 'hapusNasabah']);

        // Operasional Timbangan & Kasir (DIIZINKAN)
        Route::post('setoran',                [MitraController::class, 'prosesSetoran']);
        Route::post('cairkan-saldo-nasabah',  [MitraController::class, 'cairkanSaldoNasabah']);
        Route::post('penarikan',              [MitraController::class, 'ajukanPenarikan']);
        Route::get('transaksi',               [MitraController::class, 'riwayatTransaksi']);
        Route::get('laporan',                 [MitraController::class, 'laporan']);
        Route::get('ringkasan-harian',        [MitraController::class, 'ringkasanHarian']);

        // Pengaturan Pos (hanya jadwal & alamat)
        Route::put('pos',                     [MitraController::class, 'updatePosProfile']);
        Route::post('tambah-pos',             [MitraController::class, 'createPosBranch']);
        Route::delete('pos/{id}',             [MitraController::class, 'destroyPos']);

        // Mitra Feedback — mitra bisa lihat riwayat & kirim kritik/saran ke admin
        Route::get('feedback',                [DataController::class, 'getMitraFeedbacks']);
        Route::post('feedback',               [DataController::class, 'submitMitraFeedback']);
    });
});
