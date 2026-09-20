<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * Ambil daftar notifikasi milik user yang sedang login.
     * Default mengembalikan List JSON Array agar kompatibel dengan Retrofit Android,
     * atau format wrapped jika query ?format=wrapped disertakan.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Pastikan tabel dan kolom notifikasi lengkap
        $this->ensureNotifTable();

        // Jika user belum memiliki notifikasi sama sekali, buatkan notifikasi sambutan awal
        $totalNotifUser = Notification::where('id_pengguna', $user->id)->count();
        if ($totalNotifUser === 0) {
            static::kirim(
                $user->id,
                'Selamat Datang di SIRKULO! 🌱',
                'Akun Anda telah aktif. Setiap aktivitas transaksi, poin, dan pengumuman bank sampah akan muncul di sini.',
                'INFO'
            );
        }

        $notifs = Notification::where('id_pengguna', $user->id)
            ->orderByDesc('dibuat_pada')
            ->limit(50)
            ->get()
            ->map(fn ($n) => [
                'id'           => (string) $n->id,
                'id_pengguna'  => (string) $n->id_pengguna,
                'judul'        => (string) $n->judul,
                'deskripsi'    => (string) ($n->deskripsi ?: ($n->isi ?: '')),
                'isi'          => (string) ($n->isi ?: ($n->deskripsi ?: '')),
                'jenis'        => (string) ($n->jenis ?: 'INFO'),
                'sudah_dibaca' => (bool) $n->sudah_dibaca,
                'dibuat_pada'  => $n->dibuat_pada ? \Carbon\Carbon::parse($n->dibuat_pada)->format('Y-m-d H:i:s') : null,
                'waktu_relatif'=> $this->relativeTime($n->dibuat_pada),
            ]);

        $belumDibaca = Notification::where('id_pengguna', $user->id)
            ->where('sudah_dibaca', false)
            ->count();

        // Jika client meminta format wrapped:
        if ($request->query('format') === 'wrapped' || $request->query('wrapped')) {
            return response()->json([
                'sukses'       => true,
                'notifikasi'   => $notifs,
                'belum_dibaca' => $belumDibaca,
            ]);
        }

        // Default: kembalikan langsung Array JSON untuk Retrofit Android
        return response()->json($notifs);
    }

    /**
     * PUT /api/notifications/{id}/read
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(Request $request, string $id)
    {
        $this->ensureNotifTable();

        $notif = Notification::where('id', $id)
            ->where('id_pengguna', $request->user()->id)
            ->first();

        if ($notif) {
            $notif->update(['sudah_dibaca' => true]);
        }

        return response()->json(['sukses' => true, 'pesan' => 'Notifikasi ditandai sudah dibaca.']);
    }

    /**
     * POST /api/notifications/read-all
     * Tandai semua notifikasi milik user sebagai sudah dibaca.
     */
    public function markAllRead(Request $request)
    {
        $this->ensureNotifTable();

        Notification::where('id_pengguna', $request->user()->id)
            ->where('sudah_dibaca', false)
            ->update(['sudah_dibaca' => true]);

        return response()->json(['sukses' => true, 'pesan' => 'Semua notifikasi telah ditandai sudah dibaca.']);
    }

    /**
     * DELETE /api/notifications/{id}
     * Hapus satu notifikasi.
     */
    public function destroy(Request $request, string $id)
    {
        $this->ensureNotifTable();

        Notification::where('id', $id)
            ->where('id_pengguna', $request->user()->id)
            ->delete();

        return response()->json(['sukses' => true, 'pesan' => 'Notifikasi dihapus.']);
    }

    // ── Helper: Buat notifikasi ──────────────────────────────────────────
    /**
     * Static helper — kirim notifikasi ke user tertentu.
     * Dipanggil dari controller lain setelah aksi penting.
     */
    public static function kirim(string $idPengguna, string $judul, string $isi, string $jenis = 'INFO'): void
    {
        try {
            static::ensureNotifTableStatic();

            Notification::create([
                'id_pengguna'  => $idPengguna,
                'judul'        => $judul,
                'deskripsi'    => $isi,
                'isi'          => $isi,
                'jenis'        => $jenis,
                'sudah_dibaca' => false,
            ]);
        } catch (\Throwable $e) {
            // Jangan biarkan kegagalan notif menggagalkan transaksi utama
            \Log::warning('Gagal kirim notifikasi: ' . $e->getMessage());
        }
    }

    // ── Private Helpers ──────────────────────────────────────────────────

    private static bool $tableEnsured = false;

    private function ensureNotifTable(): void
    {
        static::ensureNotifTableStatic();
    }

    private static function ensureNotifTableStatic(): void
    {
        if (static::$tableEnsured) {
            return;
        }

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

                // Sinkronkan isi dan deskripsi bila ada yang null
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
        } catch (\Throwable $e) {
            // Tabel mungkin sudah ada atau sedang dimodifikasi
        }

        static::$tableEnsured = true;
    }

    private function relativeTime($datetime): string
    {
        if (!$datetime) return '';
        $dt = \Carbon\Carbon::parse($datetime)->setTimezone('Asia/Jakarta');
        $now = \Carbon\Carbon::now('Asia/Jakarta');
        $diff = $dt->diffInMinutes($now);

        if ($diff < 1) return 'Baru saja';
        if ($diff < 60) return $diff . ' menit lalu';
        if ($diff < 1440) return (int)($diff / 60) . ' jam lalu';
        return (int)($diff / 1440) . ' hari lalu';
    }
}
