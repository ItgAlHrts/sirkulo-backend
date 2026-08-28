<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\DataController;

// ── HEALTH CHECK (Railway/ngrok) ──────────────────────────────
Route::get('health', function () {
    return response()->json(['status' => 'ok', 'app' => 'Sirkulo API', 'time' => now()->toDateTimeString()]);
});

// ── RESET SALDO PENGGUNA ──────────────────────────────────────
Route::get('dev/reset-saldo', function (Request $request) {
    try {
        $email = $request->query('email');
        $hapusRiwayat = $request->query('hapus_riwayat', false);

        if ($email) {
            $user = \App\Models\User::where('email', $email)->first();
            if (!$user) {
                return response()->json(['status' => 'gagal', 'pesan' => "User dengan email $email tidak ditemukan."], 404);
            }
            $user->update([
                'saldo_poin' => 0,
                'total_sampah_kg' => 0,
            ]);

            if ($hapusRiwayat) {
                \App\Models\Transaction::where('id_pengguna', $user->id)->delete();
                \App\Models\Notification::where('id_pengguna', $user->id)->delete();
            }

            return response()->json([
                'status' => 'sukses',
                'pesan' => "Saldo untuk user {$user->nama} ({$user->email}) berhasil di-reset menjadi Rp 0.",
                'user' => [
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'saldo_poin' => $user->saldo_poin,
                    'total_sampah_kg' => $user->total_sampah_kg,
                ]
            ]);
        }

        // Reset SEMUA pengguna yang ada di database sekarang
        $users = \App\Models\User::all();
        foreach ($users as $u) {
            $u->saldo_poin = 0;
            $u->total_sampah_kg = 0;
            $u->save();
        }

        // Reset saldo pos mitra jika ada
        if (\Illuminate\Support\Facades\Schema::hasColumn('mitra', 'saldo_pos')) {
            \App\Models\Partner::query()->update(['saldo_pos' => 0]);
        }

        if ($hapusRiwayat) {
            \App\Models\Transaction::truncate();
            \App\Models\Notification::truncate();
        }

        $daftarPengguna = \App\Models\User::select('id', 'nama', 'email', 'peran', 'saldo_poin', 'total_sampah_kg')->get();

        return response()->json([
            'status' => 'sukses',
            'pesan' => 'Semua pengguna yang terdaftar di database berhasil di-reset saldonya menjadi Rp 0!',
            'total_pengguna' => $daftarPengguna->count(),
            'daftar_pengguna_di_database' => $daftarPengguna,
            'hapus_riwayat' => $hapusRiwayat ? 'Semua riwayat transaksi & notifikasi juga dibersihkan' : 'Riwayat transaksi tetap disimpan'
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'galat' => $e->getMessage()], 500);
    }
});

// ── DEV UTILITIES (Untuk verifikasi akun & seeding instan) ────────
Route::get('dev/clean-udin', function () {
    try {
        $udinUsers = \App\Models\User::where('email', 'like', '%udin%')->orWhere('nama', 'like', '%udin%')->get();
        foreach ($udinUsers as $u) {
            \DB::table('personal_access_tokens')->where('tokenable_id', $u->id)->delete();
            \App\Models\Notification::where('id_pengguna', $u->id)->delete();
            \App\Models\Transaction::where('id_pengguna', $u->id)->delete();
            $mitras = \App\Models\Partner::where('id_pengguna', $u->id)->get();
            foreach ($mitras as $m) {
                \App\Models\Transaction::where('id_mitra', $m->id)->delete();
                $m->delete();
            }
            $u->delete();
        }
        $udinPartners = \App\Models\Partner::where('nama', 'like', '%udin%')->get();
        foreach ($udinPartners as $m) {
            \App\Models\Transaction::where('id_mitra', $m->id)->delete();
            $m->delete();
        }
        return response()->json(['pesan' => 'Database mitra udin berhasil dihapus permanen!']);
    } catch (\Exception $e) {
        return response()->json(['galat' => $e->getMessage()], 500);
    }
});

Route::get('dev/fix-mitra', function () {
    $hasil = [];

    // 1. Drop unique constraint mitra_id_pengguna_unique jika masih ada
    try {
        $hasIdx = \DB::select(
            "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = 'mitra'
             AND INDEX_NAME = 'mitra_id_pengguna_unique'"
        );
        if (!empty($hasIdx) && $hasIdx[0]->cnt > 0) {
            \DB::statement('ALTER TABLE mitra DROP INDEX mitra_id_pengguna_unique');
            $hasil[] = '✅ Unique constraint mitra_id_pengguna_unique berhasil dihapus';
        } else {
            $hasil[] = 'ℹ️ Constraint sudah tidak ada (sudah pernah dihapus)';
        }
    } catch (\Exception $e) {
        $hasil[] = '❌ Gagal drop constraint: ' . $e->getMessage();
    }

    // 2. Pastikan semua Pos dimiliki mitrasirkulo
    try {
        $mitra = \App\Models\User::where('email', 'mitrasirkulo@gmail.com')->first();
        if ($mitra) {
            \App\Models\Partner::whereNull('id_pengguna')
                ->orWhere('id_pengguna', '!=', $mitra->id)
                ->update(['id_pengguna' => $mitra->id]);
            $hasil[] = '✅ Semua Pos sudah diarahkan ke mitrasirkulo@gmail.com';
        } else {
            $hasil[] = '❌ User mitrasirkulo@gmail.com tidak ditemukan';
        }
    } catch (\Exception $e) {
        $hasil[] = '❌ Gagal update id_pengguna: ' . $e->getMessage();
    }

    // 3. Tampilkan status index sekarang
    try {
        $indexes = \DB::select(
            "SELECT INDEX_NAME, NON_UNIQUE FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mitra'
             GROUP BY INDEX_NAME, NON_UNIQUE"
        );
        $hasil[] = 'Index mitra sekarang: ' . json_encode($indexes);
    } catch (\Exception $e) {}

    $posList = \App\Models\Partner::all(['id', 'nama', 'id_pengguna']);

    return response()->json([
        'hasil'    => $hasil,
        'pos_list' => $posList,
    ]);
});

Route::get('dev/seed', function () {
    $seeder = new \Database\Seeders\DatabaseSeeder();
    $seeder->run();
    $users = \App\Models\User::select('id', 'nama', 'email', 'telepon', 'peran', 'saldo', 'poin')->get();
    return response()->json([
        'pesan' => 'Database berhasil di-seed ulang (Hanya Nasabah & Mitra)!',
        'daftar_pengguna' => $users,
        'info_login' => [
            ['peran' => 'NASABAH', 'email' => 'itang@gmail.com', 'password' => 'password123'],
            ['peran' => 'MITRA (Pengelola Seluruh Pos)', 'email' => 'mitrasirkulo@gmail.com', 'password' => 'sirkulo2026'],
        ]
    ]);
});

Route::get('dev/users', function () {
    $users = \App\Models\User::select('id', 'nama', 'email', 'telepon', 'peran', 'saldo', 'poin')->get();
    return response()->json([
        'total_pengguna' => $users->count(),
        'pengguna' => $users
    ]);
});

Route::post('debug/login', function (\Illuminate\Http\Request $request) {
    $pengguna = \App\Models\User::where('email', $request->email)->first();
    return response()->json([
        'request_diterima' => $request->all(),
        'pengguna_ditemukan' => $pengguna ? true : false,
        'email_di_db' => $pengguna?->email,
        'hash_di_db_awal' => $pengguna ? substr($pengguna->kata_sandi, 0, 20).'...' : null,
        'hash_cocok' => $pengguna ? \Illuminate\Support\Facades\Hash::check($request->kata_sandi, $pengguna->kata_sandi) : false,
    ]);
});

// ── Auth Routes ───────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('register',         [AuthController::class, 'register']);
    Route::post('login',            [AuthController::class, 'login']);
    Route::post('forgot-password',  [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',   [AuthController::class, 'resetPassword']);
});

// Public data (no token needed)
Route::get('trash-prices',  [DataController::class, 'getTrashPrices']);
Route::get('partners',      [DataController::class, 'getPartners']);
Route::get('educations',    [DataController::class, 'getEducations']);

// ── Protected Routes (Sanctum token required) ─────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // User Profile
    Route::get('user/profile',    [UserController::class, 'profile']);
    Route::put('user/profile',    [UserController::class, 'updateProfile']);
    Route::post('user/photo',     [UserController::class, 'updatePhoto']);
    Route::put('user/password',   [UserController::class, 'changePassword']);

    // Transactions
    Route::get('transactions',          [TransactionController::class, 'index']);
    Route::get('transactions/{id}',     [TransactionController::class, 'show']);
    Route::post('transactions',         [TransactionController::class, 'store']);
    Route::delete('transactions/{id}',  [TransactionController::class, 'destroy']);

    // Data Management (Admin/Mitra)
    Route::post('trash-prices',             [DataController::class, 'storeTrashPrice']);
    Route::put('trash-prices/{id}',         [DataController::class, 'updateTrashPrice']);
    Route::delete('trash-prices/{id}',      [DataController::class, 'destroyTrashPrice']);

    Route::post('partners',                 [DataController::class, 'storePartner']);
    Route::delete('partners/{id}',          [DataController::class, 'destroyPartner']);

    Route::post('educations',               [DataController::class, 'storeEducation']);
    Route::post('educations/upload-photo',  [DataController::class, 'uploadEducationPhoto']);
    Route::put('educations/{id}',           [DataController::class, 'updateEducation']);
    Route::delete('educations/{id}',        [DataController::class, 'destroyEducation']);

    Route::get('notifications',             [DataController::class, 'getNotifications']);

    // ── Mitra Routes ──────────────────────────────────────────────
    Route::prefix('mitra')->group(function () {
        Route::get('pos-list',       [\App\Http\Controllers\Api\MitraController::class, 'listPos']);
        Route::get('dashboard',      [\App\Http\Controllers\Api\MitraController::class, 'dashboard']);
        Route::get('nasabah',        [\App\Http\Controllers\Api\MitraController::class, 'daftarNasabah']);
        Route::get('nasabah/{id}',   [\App\Http\Controllers\Api\MitraController::class, 'detailNasabah']);
        Route::post('nasabah',       [\App\Http\Controllers\Api\MitraController::class, 'registrasiNasabah']);
        Route::delete('nasabah/{id}',[\App\Http\Controllers\Api\MitraController::class, 'hapusNasabah']);
        Route::post('setoran',                [\App\Http\Controllers\Api\MitraController::class, 'prosesSetoran']);
        Route::post('cairkan-saldo-nasabah',  [\App\Http\Controllers\Api\MitraController::class, 'cairkanSaldoNasabah']);
        Route::post('penarikan',              [\App\Http\Controllers\Api\MitraController::class, 'ajukanPenarikan']);
        Route::get('transaksi',      [\App\Http\Controllers\Api\MitraController::class, 'riwayatTransaksi']);
        Route::get('laporan',        [\App\Http\Controllers\Api\MitraController::class, 'laporan']);
        Route::put('pos',            [\App\Http\Controllers\Api\MitraController::class, 'updatePosProfile']);
        Route::delete('pos/{id}',    [\App\Http\Controllers\Api\MitraController::class, 'destroyPos']);
        Route::post('tambah-pos',     [\App\Http\Controllers\Api\MitraController::class, 'createPosBranch']);
    });
});
