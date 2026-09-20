<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Partner;
use App\Models\Transaction;
use App\Models\Nasabah;
use App\Models\PetugasMitra;
use App\Models\TrashCategory;
use App\Models\Feedback;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $dest = public_path('logo.png');
        if (!file_exists($dest)) {
            $src = base_path('../sirkulo-nasabah-app/app/src/main/res/drawable/logo.png');
            if (file_exists($src)) {
                @copy($src, $dest);
            }
        }

        $totalNasabah   = Nasabah::count();
        $totalMitra     = PetugasMitra::count();
        $totalPos       = Partner::count();
        $totalKas       = Nasabah::sum('saldo');
        $totalPoin      = Nasabah::sum('poin');
        $totalKategori  = TrashCategory::count();
        $feedbackCount  = Feedback::whereNull('jawaban')->count();

        $bulanIni            = Carbon::now()->startOfMonth();
        $totalSetoranCount   = Transaction::where('jenis', 'SETORAN')->where('dibuat_pada', '>=', $bulanIni)->count();
        $totalPenarikanCount = Transaction::whereIn('jenis', ['PENARIKAN', 'CAIRKAN'])->where('dibuat_pada', '>=', $bulanIni)->count();
        $transaksiCount      = $totalSetoranCount + $totalPenarikanCount;
        $totalAktivitasCount = $transaksiCount + ($feedbackCount ?? 0);

        $totalSetoranRp = Transaction::where('jenis', 'SETORAN')
            ->where('dibuat_pada', '>=', $bulanIni)
            ->sum('jumlah_total');
        $totalCairkanRp = Transaction::whereIn('jenis', ['PENARIKAN', 'CAIRKAN'])
            ->where('dibuat_pada', '>=', $bulanIni)
            ->sum('jumlah_total');

        // Backward compatibility
        $totalSetoranKg = $totalSetoranRp;

        // Grafik 6 bulan terakhir: Jumlah transaksi dan Nominal Rupiah
        $chartLabels         = [];
        $chartDataSetoran    = [];
        $chartDataPenarikan  = [];
        $chartDataTotalCount = [];
        $chartDataNominal    = [];
        $chartDataCairkan    = [];

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $chartLabels[] = $bulan->translatedFormat('M Y');

            $setoranCount = Transaction::where('jenis', 'SETORAN')
                ->whereYear('dibuat_pada', $bulan->year)
                ->whereMonth('dibuat_pada', $bulan->month)
                ->count();

            $penarikanCount = Transaction::whereIn('jenis', ['PENARIKAN', 'CAIRKAN'])
                ->whereYear('dibuat_pada', $bulan->year)
                ->whereMonth('dibuat_pada', $bulan->month)
                ->count();

            $chartDataSetoran[]    = $setoranCount;
            $chartDataPenarikan[]  = $penarikanCount;
            $chartDataTotalCount[] = $setoranCount + $penarikanCount;

            $chartDataNominal[] = (int) Transaction::where('jenis', 'SETORAN')
                ->whereYear('dibuat_pada', $bulan->year)
                ->whereMonth('dibuat_pada', $bulan->month)
                ->sum('jumlah_total');

            $chartDataCairkan[] = (int) Transaction::whereIn('jenis', ['PENARIKAN', 'CAIRKAN'])
                ->whereYear('dibuat_pada', $bulan->year)
                ->whereMonth('dibuat_pada', $bulan->month)
                ->sum('jumlah_total');
        }

        // Kompatibilitas variabel chartData lama
        $chartData = $chartDataSetoran;

        // Transaksi terbaru dengan relasi nasabah dan pos
        $transaksiTerbaru = Transaction::with(['nasabah', 'partner'])
            ->orderByDesc('dibuat_pada')
            ->limit(7)
            ->get();

        // Daftar pos operasional
        $posList = Partner::with('petugas')->get();

        // Komposisi jenis aktivitas & transaksi bulan ini
        $distribusiJenis = [
            'setoran'   => $totalSetoranCount,
            'penarikan' => $totalPenarikanCount,
            'feedback'  => (int) ($feedbackCount ?? 0),
        ];

        // Statistik khusus integrasi & Super Admin
        $totalAdmin = Admin::count();
        $recentAuditLogs = [];
        if (session('admin_peran') === 'SUPER_ADMIN') {
            if (Schema::hasTable('audit_log')) {
                $recentAuditLogs = DB::table('audit_log')
                    ->orderByDesc('dibuat_pada')
                    ->limit(5)
                    ->get();
            }
        }

        return view('admin.dashboard', compact(
            'totalNasabah', 'totalMitra', 'totalPos', 'totalKas', 'totalPoin',
            'totalKategori', 'feedbackCount', 'totalAdmin', 'recentAuditLogs',
            'transaksiCount', 'totalSetoranCount', 'totalPenarikanCount', 'totalAktivitasCount',
            'totalSetoranKg', 'totalSetoranRp', 'totalCairkanRp',
            'chartLabels', 'chartData', 'chartDataSetoran', 'chartDataPenarikan', 'chartDataTotalCount',
            'chartDataNominal', 'chartDataCairkan',
            'transaksiTerbaru', 'posList', 'distribusiJenis'
        ));
    }
}
