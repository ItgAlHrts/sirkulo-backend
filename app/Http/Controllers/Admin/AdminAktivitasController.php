<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAktivitasController extends Controller
{
    public function index(Request $request)
    {
        AuditLogger::ensureTableReady();

        $query = DB::table('audit_log')->orderByDesc('dibuat_pada');

        // 1. Filter Pencarian Teks
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('keterangan', 'like', "%$q%")
                  ->orWhere('nama_admin', 'like', "%$q%")
                  ->orWhere('aksi', 'like', "%$q%")
                  ->orWhere('ip_address', 'like', "%$q%")
                  ->orWhere('nama_perangkat', 'like', "%$q%")
                  ->orWhere('id', 'like', "%$q%");
            });
        }

        // 2. Filter Kategori / Modul
        if ($request->filled('kategori') && $request->kategori !== 'SEMUA') {
            $query->where('kategori', $request->kategori);
        }

        // 3. Filter Tipe Operasi
        if ($request->filled('tipe') && $request->tipe !== 'SEMUA') {
            $query->where('tipe', $request->tipe);
        }

        // 4. Filter Status
        if ($request->filled('status') && $request->status !== 'SEMUA') {
            $query->where('status', $request->status);
        }

        // 5. Filter Periode
        if ($request->filled('periode')) {
            switch ($request->periode) {
                case 'hari_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfDay());
                    break;
                case 'minggu_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfWeek());
                    break;
                case 'bulan_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfMonth());
                    break;
            }
        }

        $logs = $query->paginate(25)->withQueryString();

        // Perkaya setiap log dengan parsing perangkat dan ekstraksi daftar perubahan sebelum-sesudah
        $logs->getCollection()->transform(function ($log) {
            if (empty($log->nama_perangkat)) {
                $parsed = AuditLogger::parseUserAgent($log->user_agent);
                $log->nama_perangkat = $parsed['nama'];
                $log->tipe_perangkat = $parsed['tipe'];
            }

            $log->daftar_perubahan = [];
            if (!empty($log->detail_json)) {
                $detail = json_decode($log->detail_json, true);
                if (is_array($detail) && !empty($detail['perubahan'])) {
                    $log->daftar_perubahan = $detail['perubahan'];
                }
            }
            return $log;
        });

        // ── Statistik Global Audit ─────────────────────────────────────────
        $statsHariIni = DB::table('audit_log')
            ->where('dibuat_pada', '>=', now()->startOfDay())
            ->count();

        $statsMingguIni = DB::table('audit_log')
            ->where('dibuat_pada', '>=', now()->startOfWeek())
            ->count();

        $statsSecurityAuth = DB::table('audit_log')
            ->whereIn('kategori', ['AUTH', 'SECURITY'])
            ->orWhereIn('tipe', ['AUTH', 'SECURITY'])
            ->count();

        $statsPerubahanData = DB::table('audit_log')
            ->whereIn('tipe', ['CREATE', 'UPDATE', 'DELETE', 'RESET_PASSWORD'])
            ->count();

        $topAksi = DB::table('audit_log')
            ->select('aksi', DB::raw('COUNT(*) as jumlah'))
            ->groupBy('aksi')
            ->orderByDesc('jumlah')
            ->limit(6)
            ->get();

        $daftarKategori = [
            'AUTH'         => 'Keamanan & Autentikasi',
            'NASABAH'      => 'Nasabah Warga',
            'POS_MITRA'    => 'Pos Bank Sampah & Mitra',
            'PENGGUNA'     => 'Staf & Administrator',
            'HARGA_SAMPAH' => 'Harga & Komoditas',
            'TRANSAKSI'    => 'Transaksi Finansial',
            'LAPORAN'      => 'Laporan & Ekspor',

            'EDUKASI'      => 'Edukasi & Literasi',
            'FEEDBACK'     => 'Kritik & Saran',
            'SISTEM'       => 'Sistem & Pemeliharaan',
            'UMUM'         => 'Aktivitas Umum',
        ];

        return view('admin.aktivitas.index', compact(
            'logs', 'statsHariIni', 'statsMingguIni', 'statsSecurityAuth', 'statsPerubahanData',
            'topAksi', 'daftarKategori'
        ));
    }

    /**
     * Ekspor seluruh log audit yang terfilter ke format CSV
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        AuditLogger::ensureTableReady();

        $query = DB::table('audit_log')->orderByDesc('dibuat_pada');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($b) use ($q) {
                $b->where('keterangan', 'like', "%$q%")
                  ->orWhere('nama_admin', 'like', "%$q%")
                  ->orWhere('aksi', 'like', "%$q%")
                  ->orWhere('ip_address', 'like', "%$q%");
            });
        }
        if ($request->filled('kategori') && $request->kategori !== 'SEMUA') {
            $query->where('kategori', $request->kategori);
        }
        if ($request->filled('tipe') && $request->tipe !== 'SEMUA') {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('status') && $request->status !== 'SEMUA') {
            $query->where('status', $request->status);
        }
        if ($request->filled('periode')) {
            switch ($request->periode) {
                case 'hari_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfDay());
                    break;
                case 'minggu_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfWeek());
                    break;
                case 'bulan_ini':
                    $query->where('dibuat_pada', '>=', now()->startOfMonth());
                    break;
            }
        }

        $records = $query->limit(5000)->get();

        // Catat aksi ekspor audit trail ke audit_log
        AuditLogger::log(
            'EXPORT_AUDIT_LOG',
            'Mengunduh berkas ekspor data Audit Trail & Log Aktivitas Sistem (' . $records->count() . ' baris).',
            'LAPORAN',
            'EXPORT',
            'SUKSES',
            ['total_baris' => $records->count(), 'filter' => $request->all()]
        );

        $filename = 'SIRKULO_AUDIT_TRAIL_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            // Tulis UTF-8 BOM agar Excel menampilkan aksen/karakter dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID Log',
                'Waktu (WIB)',
                'Nama Pelaku',
                'Peran',
                'Modul / Kategori',
                'Tipe Operasi',
                'Kode Aksi',
                'Status',
                'Narasi Keterangan',
                'Alamat IP',
                'Nama Perangkat & Browser',
                'User Agent Mentah',
                'Rincian Data JSON'
            ]);

            foreach ($records as $r) {
                $perangkat = $r->nama_perangkat ?? null;
                if (empty($perangkat)) {
                    $perangkat = AuditLogger::parseUserAgent($r->user_agent)['nama'];
                }

                fputcsv($handle, [
                    $r->id,
                    \Carbon\Carbon::parse($r->dibuat_pada)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s'),
                    $r->nama_admin ?? 'Sistem',
                    $r->peran ?? 'ADMIN',
                    $r->kategori ?? 'UMUM',
                    $r->tipe ?? 'ACTION',
                    $r->aksi,
                    $r->status ?? 'SUKSES',
                    $r->keterangan,
                    $r->ip_address ?? '-',
                    $perangkat,
                    $r->user_agent ?? '-',
                    $r->detail_json ?? '-',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
