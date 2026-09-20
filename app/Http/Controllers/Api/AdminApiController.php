<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Nasabah;
use App\Models\Partner;
use App\Models\PetugasMitra;
use App\Models\Transaction;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminApiController extends Controller
{
    /**
     * GET /api/admin/summary
     * Ringkasan statistik realtime untuk dashboard web admin.
     * Bisa diakses oleh ADMIN dan SUPER_ADMIN.
     */
    public function summary(Request $request)
    {
        $bulanIni = now()->startOfMonth();

        return response()->json([
            'sukses' => true,
            'data'   => [
                'total_nasabah'      => Nasabah::count(),
                'total_mitra'        => PetugasMitra::count(),
                'total_pos'          => Partner::count(),
                'total_kas'          => Nasabah::sum('saldo'),
                'transaksi_bulan_ini'=> Transaction::where('dibuat_pada', '>=', $bulanIni)->count(),
                'setoran_bulan_ini'  => (int) Transaction::where('jenis', 'SETORAN')
                                            ->where('dibuat_pada', '>=', $bulanIni)->sum('jumlah_total'),
                'cairkan_bulan_ini'  => (int) Transaction::whereIn('jenis', ['PENARIKAN', 'CAIRKAN'])
                                            ->where('dibuat_pada', '>=', $bulanIni)->sum('jumlah_total'),
                'feedback_pending'   => Feedback::whereNull('jawaban')->count(),
                'transaksi_hari_ini' => Transaction::where('dibuat_pada', '>=', now()->startOfDay())->count(),
                'waktu'              => now()->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    /**
     * PUT /api/admin/{id}/promote
     * Naikkan peran Admin Operasional menjadi Super Admin.
     * Hanya bisa dilakukan oleh Super Admin.
     */
    public function promote(Request $request, string $id)
    {
        $target = Admin::findOrFail($id);

        if ($target->peran === 'SUPER_ADMIN') {
            return response()->json(['sukses' => false, 'pesan' => 'Akun ini sudah berstatus Super Admin.'], 422);
        }

        $target->update(['peran' => 'SUPER_ADMIN']);

        // Log aktivitas
        $this->logAktivitas($request, 'PROMOTE_ADMIN', "Akun '{$target->nama}' dipromosikan menjadi Super Admin.");

        return response()->json([
            'sukses' => true,
            'pesan'  => "Akun '{$target->nama}' berhasil dipromosikan menjadi Super Admin.",
        ]);
    }

    /**
     * PUT /api/admin/{id}/demote
     * Turunkan peran Super Admin menjadi Admin Operasional.
     * Tidak bisa mendemote diri sendiri.
     */
    public function demote(Request $request, string $id)
    {
        $actor  = $request->user();
        $target = Admin::findOrFail($id);

        // Cegah self-demote
        if ($target->id === $actor->id) {
            return response()->json(['sukses' => false, 'pesan' => 'Anda tidak dapat menurunkan peran akun Anda sendiri.'], 422);
        }

        // Cegah jika ini satu-satunya Super Admin
        $superCount = Admin::where('peran', 'SUPER_ADMIN')->count();
        if ($superCount <= 1 && $target->peran === 'SUPER_ADMIN') {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Tidak dapat mendemote satu-satunya Super Admin pada sistem.',
            ], 422);
        }

        $target->update(['peran' => 'ADMIN']);

        $this->logAktivitas($request, 'DEMOTE_ADMIN', "Akun '{$target->nama}' diturunkan menjadi Admin Operasional.");

        return response()->json([
            'sukses' => true,
            'pesan'  => "Akun '{$target->nama}' berhasil diturunkan menjadi Admin Operasional.",
        ]);
    }

    /**
     * GET /api/admin/audit-log
     * Log aktivitas sensitif sistem (Super Admin Only).
     */
    public function auditLog(Request $request)
    {
        $this->ensureAuditTable();

        $logs = DB::table('audit_log')
            ->orderByDesc('dibuat_pada')
            ->limit(100)
            ->get();

        return response()->json(['sukses' => true, 'log' => $logs]);
    }

    // ── Private Helpers ──────────────────────────────────────────────────

    private function logAktivitas(Request $request, string $aksi, string $keterangan): void
    {
        try {
            $this->ensureAuditTable();
            DB::table('audit_log')->insert([
                'id'          => (string) \Illuminate\Support\Str::uuid(),
                'id_admin'    => $request->user()?->id,
                'nama_admin'  => $request->user()?->nama,
                'aksi'        => $aksi,
                'keterangan'  => $keterangan,
                'ip_address'  => $request->ip(),
                'dibuat_pada' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Gagal tulis audit log: ' . $e->getMessage());
        }
    }

    private function ensureAuditTable(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('audit_log')) {
                DB::statement("
                    CREATE TABLE IF NOT EXISTS `audit_log` (
                        `id`          CHAR(36)      NOT NULL,
                        `id_admin`    CHAR(36)      NULL,
                        `nama_admin`  VARCHAR(255)  NULL,
                        `aksi`        VARCHAR(100)  NOT NULL,
                        `keterangan`  TEXT          NOT NULL,
                        `ip_address`  VARCHAR(50)   NULL,
                        `dibuat_pada` TIMESTAMP     NULL,
                        PRIMARY KEY (`id`),
                        INDEX `idx_audit_admin` (`id_admin`),
                        INDEX `idx_audit_waktu`  (`dibuat_pada`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
                ");
            }
        } catch (\Throwable $e) {
            // Tabel mungkin sudah ada
        }
    }
}
