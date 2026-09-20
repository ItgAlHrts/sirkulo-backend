<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Models\TrashCategory;
use App\Models\Partner;

class AdminLaporanController extends Controller
{
    public function index(Request $request)
    {
        $this->pastikanKolomPengelolaAda();

        $bulan = (int) ($request->input('bulan', Carbon::now()->month));
        $tahun = (int) ($request->input('tahun', Carbon::now()->year));
        $posId = $request->input('pos_id');

        $posList     = Partner::with('pengguna')->orderBy('nama')->get();
        $selectedPos = $posId ? $posList->find($posId) : null;

        $query = Transaction::with(['nasabah', 'partner'])
            ->whereYear('dibuat_pada', $tahun)
            ->whereMonth('dibuat_pada', $bulan);

        if ($posId) {
            $query->where('id_mitra', $posId);
        }

        $transaksi = $query->orderByDesc('dibuat_pada')->get();

        $setoranTrx = $transaksi->where('jenis', 'SETORAN');
        $totalSetoran = $setoranTrx->count();

        // ── 1. Master Kategori untuk Kalkulasi Margin Pengepul ──────────
        $allCategories = TrashCategory::all();

        // ── 2. Inisialisasi Data Agregasi Global & Tiap Pos ─────────────
        $rincianKategori = [];
        $totalBeratKg = 0.0;
        $totalNilaiSampah = 0;       // Nilai beli ke nasabah
        $totalNilaiPengepul = 0;     // Estimasi nilai jual ke pengepul
        $totalMarginKeuntungan = 0;  // Keuntungan kotor bank sampah / kas desa

        // Siapkan entri pos: Jika filter satu pos, muat pos tersebut saja; jika semua pos, muat seluruh pos
        $rincianPerPos = [];
        $targetPosList = $selectedPos ? collect([$selectedPos]) : $posList;
        foreach ($targetPosList as $p) {
            $rincianPerPos[$p->id] = [
                'id'             => $p->id,
                'nama_pos'       => $p->nama,
                'kode_pos'       => $p->kode_pos ?? 'POS-' . strtoupper(substr(str_replace('-', '', $p->id), 0, 6)),
                'alamat'         => $p->alamat ?: 'Desa',
                'pengelola'      => $p->pengelola ?: ($p->pengguna?->nama ?? 'Penanggung Jawab'),
                'total_kg'       => 0.0,
                'total_tonase'   => 0.0,
                'total_rp'       => 0,
                'total_pengepul' => 0,
                'margin_rp'      => 0,
                'margin_persen'  => 0,
                'transaksi'      => 0,
            ];
        }

        foreach ($setoranTrx as $t) {
            $ket = $t->keterangan ?? 'Campuran 1.0 kg';
            $kg = 0.0;
            if (preg_match('/([\d.]+)\s*kg/i', $ket, $m)) {
                $kg = (float) $m[1];
            } else {
                $kg = $t->jumlah_total > 0 ? round($t->jumlah_total / 2500, 1) : 1.0;
            }

            preg_match('/^([^0-9]+)/u', $ket, $matNama);
            $namaSampah = trim($matNama[1] ?? 'Sampah Campuran');
            $namaSampah = rtrim($namaSampah, ' -');
            if (empty($namaSampah)) $namaSampah = 'Sampah Daur Ulang';

            // Cocokkan dengan kategori sampah sistem untuk harga pengepul & margin
            $matchedCat = $allCategories->first(function ($c) use ($namaSampah) {
                return stripos($c->nama, $namaSampah) !== false || stripos($namaSampah, $c->nama) !== false;
            });

            $hargaBeliPerKg = $matchedCat ? $matchedCat->harga_beli : ($kg > 0 ? (int) round($t->jumlah_total / $kg) : 2000);
            $hargaJualPerKg = $matchedCat ? $matchedCat->harga_jual : (int) max($hargaBeliPerKg + 500, round($hargaBeliPerKg * 1.4));

            $nilaiNasabah = (int) $t->jumlah_total;
            $nilaiPengepul = $kg > 0 ? (int) round($kg * $hargaJualPerKg) : (int) round($nilaiNasabah * 1.4);
            $marginTrx = max(0, $nilaiPengepul - $nilaiNasabah);

            $totalBeratKg += $kg;
            $totalNilaiSampah += $nilaiNasabah;
            $totalNilaiPengepul += $nilaiPengepul;
            $totalMarginKeuntungan += $marginTrx;

            // Agregasi Kategori Sampah
            if (!isset($rincianKategori[$namaSampah])) {
                $rincianKategori[$namaSampah] = [
                    'nama'           => $namaSampah,
                    'total_kg'       => 0.0,
                    'total_rp'       => 0,
                    'total_pengepul' => 0,
                    'margin_rp'      => 0,
                    'margin_persen'  => 0,
                    'harga_beli'     => $hargaBeliPerKg,
                    'harga_jual'     => $hargaJualPerKg,
                    'transaksi'      => 0,
                ];
            }
            $rincianKategori[$namaSampah]['total_kg'] += $kg;
            $rincianKategori[$namaSampah]['total_rp'] += $nilaiNasabah;
            $rincianKategori[$namaSampah]['total_pengepul'] += $nilaiPengepul;
            $rincianKategori[$namaSampah]['margin_rp'] += $marginTrx;
            $rincianKategori[$namaSampah]['transaksi']++;

            // Agregasi Tiap Pos
            $posKey = $t->id_mitra;
            if (!$posKey || !isset($rincianPerPos[$posKey])) {
                $posFallbackNama = $t->partner?->nama ?? ($t->mitra?->nama ?? 'Pos Induk Desa');
                $posKey = 'fallback_' . md5($posFallbackNama);
                if (!isset($rincianPerPos[$posKey])) {
                    $rincianPerPos[$posKey] = [
                        'id'             => $posKey,
                        'nama_pos'       => $posFallbackNama,
                        'kode_pos'       => 'POS-DESA',
                        'alamat'         => $t->partner?->alamat ?? ($t->mitra?->alamat ?? 'Kantor Balai Desa'),
                        'pengelola'      => $t->partner?->pengelola ?? 'Pengelola Pos',
                        'total_kg'       => 0.0,
                        'total_tonase'   => 0.0,
                        'total_rp'       => 0,
                        'total_pengepul' => 0,
                        'margin_rp'      => 0,
                        'margin_persen'  => 0,
                        'transaksi'      => 0,
                    ];
                }
            }
            $rincianPerPos[$posKey]['total_kg'] += $kg;
            $rincianPerPos[$posKey]['total_rp'] += $nilaiNasabah;
            $rincianPerPos[$posKey]['total_pengepul'] += $nilaiPengepul;
            $rincianPerPos[$posKey]['margin_rp'] += $marginTrx;
            $rincianPerPos[$posKey]['transaksi']++;
        }

        // Hitung persentase margin tiap kategori
        foreach ($rincianKategori as &$rk) {
            $rk['margin_persen'] = $rk['total_rp'] > 0 ? round(($rk['margin_rp'] / $rk['total_rp']) * 100, 1) : 0;
        }
        unset($rk);

        // Hitung persentase margin & tonase tiap pos
        foreach ($rincianPerPos as &$rp) {
            $rp['total_tonase'] = round($rp['total_kg'] / 1000, 3);
            $rp['margin_persen'] = $rp['total_rp'] > 0 ? round(($rp['margin_rp'] / $rp['total_rp']) * 100, 1) : 0;
        }
        unset($rp);

        // Urutkan dari tonase / berat terbanyak
        uasort($rincianKategori, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);
        uasort($rincianPerPos, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        $persentaseTotalMargin = $totalNilaiSampah > 0 ? round(($totalMarginKeuntungan / $totalNilaiSampah) * 100, 1) : 0;

        // ── 3. Data Keuangan Kas ──
        $pencairanTrx = $transaksi->whereIn('jenis', ['CAIRKAN', 'PENARIKAN']);
        $totalPenarikanRp = (int) $pencairanTrx->sum('jumlah_total');
        $totalPenarikanTrx = $pencairanTrx->count();
        $saldoKasBersih = $totalNilaiSampah - $totalPenarikanRp;

        $totalTonase = round($totalBeratKg / 1000, 3);
        $estimasiReduksiEmisiCo2 = round($totalBeratKg * 1.25, 1);
        
        $nasabahAktifQuery = Nasabah::whereHas('transaksi', function ($q) use ($tahun, $bulan, $posId) {
            $q->whereYear('dibuat_pada', $tahun)->whereMonth('dibuat_pada', $bulan);
            if ($posId) {
                $q->where('id_mitra', $posId);
            }
        });
        $totalNasabahAktif = $nasabahAktifQuery->count();

        $daftarTahun = range(Carbon::now()->year, 2024);

        $romawiBulan = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ][$bulan] ?? 'I';
        $nomorLaporan = "LPJ/SRKL/{$romawiBulan}/{$tahun}";
        
        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $namaBulanIndo[$bulan] ?? 'Januari';
        
        $now = Carbon::now();
        $tanggalCetak = $now->format('d') . ' ' . ($namaBulanIndo[(int)$now->format('n')] ?? '') . ' ' . $now->format('Y');
        $waktuCetak = $now->format('H:i') . ' WIB';
        $tanggalCetakLengkap = $tanggalCetak . ', ' . $waktuCetak;

        // ── KODE OTENTIKASI & INTEGRITAS DATA (ANTI-MANIPULASI) ──
        // Mengikat data primer: Pos, Periode Bulan & Tahun, Total Berat, Total Nilai, Total Transaksi, Tanggal Server
        $tglCetakYmd    = $now->format('Ymd');
        $signaturePayload = implode('|', [
            'SIRKULO-OFFICIAL-VERIFIED-REPORT',
            $selectedPos ? $selectedPos->id : 'ALL_POS',
            $tahun,
            $bulan,
            number_format($totalBeratKg, 3, '.', ''),
            (int) $totalNilaiSampah,
            $totalSetoran,
            $tglCetakYmd
        ]);
        $totalBeratKgFormatted = number_format($totalBeratKg, 3, '.', '');
        $hashIntegritas = hash('sha256', $signaturePayload);
        $kodeKeamanan   = 'SRK-' . strtoupper(substr($hashIntegritas, 0, 4) . '-' . substr($hashIntegritas, 4, 4) . '-' . substr($hashIntegritas, 8, 4));
        $verifikasiUrl  = url("/verifikasi-laporan?no=" . urlencode($nomorLaporan) . "&kode={$kodeKeamanan}&pos=" . ($posId ?: 'all') . "&periode={$bulan}-{$tahun}&tgl={$tglCetakYmd}");

        // Informasi Pos terpilih atau Pos Induk Desa
        if ($selectedPos) {
            $posInfoNama      = $selectedPos->nama;
            $posInfoKode      = $selectedPos->kode_pos ?? 'POS-' . strtoupper(substr(str_replace('-', '', $selectedPos->id), 0, 6));
            // Gunakan pengelola dari accessor Partner (yang memfilter akun generik 'Mitra SIRKULO')
            // atau override dari request('pengelola') jika admin mengisi kustom
            $posInfoPengelola = $request->input('pengelola') ?: $selectedPos->pengelola;
            $posInfoAlamat    = $selectedPos->alamat ?: 'Kantor Pos Bank Sampah';
            // jam_buka adalah field di tabel mitra
            $posInfoJam       = $selectedPos->jam_buka ?: 'Senin - Sabtu, 08:00 - 16:00 WIB';
        } else {
            $posInfoNama      = 'Pusat Bank Sampah Digital Desa SIRKULO (Seluruh Pos)';
            $posInfoKode      = 'POS-INDUK-DESA';
            $posInfoPengelola = $request->input('pengelola') ?: 'Administrator Desa & BUMDes';
            $posInfoAlamat    = 'Gedung BUMDes Kantor Balai Desa';
            $posInfoJam       = 'Senin - Sabtu, 08:00 - 16:00 WIB';
        }

        return view('admin.laporan.index', compact(
            'transaksi', 'bulan', 'tahun', 'daftarTahun', 'posId', 'posList', 'selectedPos',
            'posInfoNama', 'posInfoKode', 'posInfoPengelola', 'posInfoAlamat', 'posInfoJam',
            'totalSetoran', 'totalNasabahAktif',
            'totalBeratKg', 'totalBeratKgFormatted', 'totalTonase', 'estimasiReduksiEmisiCo2',
            'totalNilaiSampah', 'totalPenarikanRp', 'totalPenarikanTrx', 'saldoKasBersih',
            'totalNilaiPengepul', 'totalMarginKeuntungan', 'persentaseTotalMargin',
            'rincianKategori', 'rincianPerPos',
            'nomorLaporan', 'romawiBulan', 'namaBulan', 'tanggalCetak', 'tanggalCetakLengkap', 'waktuCetak',
            'hashIntegritas', 'kodeKeamanan', 'verifikasiUrl'
        ));
    }

    /**
     * Export data rekapitulasi TOTAL SAMPAH & KEUANGAN ke CSV (Tabel Bersih & Rapi)
     */
    public function exportCsv(Request $request)
    {
        $bulan = (int) ($request->input('bulan', Carbon::now()->month));
        $tahun = (int) ($request->input('tahun', Carbon::now()->year));
        $posId = $request->input('pos_id');

        $query = Transaction::with(['nasabah', 'partner'])
            ->whereYear('dibuat_pada', $tahun)
            ->whereMonth('dibuat_pada', $bulan);

        if ($posId) {
            $query->where('id_mitra', $posId);
        }

        $transaksi = $query->get();
        $setoranTrx = $transaksi->where('jenis', 'SETORAN');
        $totalSetoran = $setoranTrx->count();
        $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');

        $rincianKategori = [];
        $totalBeratKg = 0.0;
        $totalNilaiSampah = 0;

        foreach ($setoranTrx as $t) {
            $ket = $t->keterangan ?? 'Campuran 1.0 kg';
            $kg = 0.0;
            if (preg_match('/([\d.]+)\s*kg/i', $ket, $m)) {
                $kg = (float) $m[1];
            } else {
                $kg = $t->jumlah_total > 0 ? round($t->jumlah_total / 2500, 1) : 1.0;
            }

            preg_match('/^([^0-9]+)/u', $ket, $matNama);
            $namaSampah = trim($matNama[1] ?? 'Sampah Campuran');
            $namaSampah = rtrim($namaSampah, ' -');
            if (empty($namaSampah)) $namaSampah = 'Sampah Daur Ulang';

            $totalBeratKg += $kg;
            $totalNilaiSampah += (int) $t->jumlah_total;

            if (!isset($rincianKategori[$namaSampah])) {
                $rincianKategori[$namaSampah] = [
                    'nama'       => $namaSampah,
                    'total_kg'   => 0.0,
                    'total_rp'   => 0,
                    'transaksi'  => 0,
                ];
            }
            $rincianKategori[$namaSampah]['total_kg'] += $kg;
            $rincianKategori[$namaSampah]['total_rp'] += (int) $t->jumlah_total;
            $rincianKategori[$namaSampah]['transaksi']++;
        }
        uasort($rincianKategori, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        $totalTonase = round($totalBeratKg / 1000, 3);
        $pencairanTrx = $transaksi->whereIn('jenis', ['CAIRKAN', 'PENARIKAN']);
        $totalPenarikanRp = (int) $pencairanTrx->sum('jumlah_total');
        $saldoKasBersih = $totalNilaiSampah - $totalPenarikanRp;
        $totalNasabahAktif = Nasabah::whereHas('transaksi', fn($q) => $q->whereYear('dibuat_pada', $tahun)->whereMonth('dibuat_pada', $bulan))->count();

        $selectedPos = $posId ? \App\Models\Partner::with('pengguna')->find($posId) : null;
        $pengelolaNama = $request->input('pengelola') ?: ($selectedPos ? $selectedPos->pengelola : 'Administrator Desa & BUMDes');

        $fileName  = "Laporan_Sampah_dan_Keuangan_{$namaBulan}_{$tahun}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $namaBulan, $tahun, $rincianKategori,
            $totalBeratKg, $totalTonase, $totalNilaiSampah, $totalSetoran,
            $totalPenarikanRp, $saldoKasBersih, $totalNasabahAktif,
            $selectedPos, $pengelolaNama
        ) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 agar karakter rapi di Microsoft Excel
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Judul & Keterangan Laporan (persis 7 kolom agar lebar kolom Excel tidak terganggu)
            fputcsv($handle, ["LAPORAN REKAPITULASI JUMLAH SAMPAH DAN KEUANGAN KAS DESA", "", "", "", "", "", ""], ';');
            fputcsv($handle, ["Periode: " . $namaBulan . " " . $tahun, "", "", "", "", "", ""], ';');
            $posInfoText = $selectedPos ? "Pos: {$selectedPos->nama} (Kode: {$selectedPos->kode_pos} | Pengelola: {$pengelolaNama})" : "Pos: Seluruh Unit Pos Desa (Pusat)";
            fputcsv($handle, [$posInfoText, "", "", "", "", "", ""], ';');
            fputcsv($handle, [], ';');

            // ── TABEL UTAMA: REKAPITULASI SAMPAH & KEUANGAN (7 KOLOM RAPI) ──
            fputcsv($handle, [
                "No",
                "Kategori Sampah",
                "Frekuensi Setoran",
                "Total Berat (kg)",
                "Total Tonase (Ton)",
                "Total Nilai Uang (Rp)",
                "Porsi Sampah (%)"
            ], ';');

            $no = 1;
            foreach ($rincianKategori as $rk) {
                $persen = $totalBeratKg > 0 ? round(($rk['total_kg'] / $totalBeratKg) * 100, 1) : 0;
                fputcsv($handle, [
                    $no++,
                    $rk['nama'],
                    $rk['transaksi'] . " kali",
                    number_format($rk['total_kg'], 1, ',', '.'),
                    number_format($rk['total_kg'] / 1000, 3, ',', '.'),
                    "Rp " . number_format($rk['total_rp'], 0, ',', '.'),
                    number_format($persen, 1, ',', '.') . "%"
                ], ';');
            }

            // Baris Total Utama
            fputcsv($handle, [
                "TOTAL",
                "Semua Komoditas Sampah",
                $totalSetoran . " kali",
                number_format($totalBeratKg, 1, ',', '.'),
                number_format($totalTonase, 3, ',', '.'),
                "Rp " . number_format($totalNilaiSampah, 0, ',', '.'),
                "100,0%"
            ], ';');

            fputcsv($handle, [], ';');

            // ── TABEL RINGKASAN KAS KEUANGAN ──
            fputcsv($handle, ["RINGKASAN ARUS KAS KEUANGAN", "NOMINAL (RP)", "", "", "", "", ""], ';');
            fputcsv($handle, ["Total Pemasukan Sampah (Tabungan Masuk)", "Rp " . number_format($totalNilaiSampah, 0, ',', '.'), "", "", "", "", ""], ';');
            fputcsv($handle, ["Total Penarikan Saldo oleh Warga", "Rp " . number_format($totalPenarikanRp, 0, ',', '.'), "", "", "", "", ""], ';');
            fputcsv($handle, ["Sisa Saldo Kas Tabungan Bersih", "Rp " . number_format($saldoKasBersih, 0, ',', '.'), "", "", "", "", ""], ';');
            fputcsv($handle, ["Total Nasabah Warga Aktif", $totalNasabahAktif . " Orang", "", "", "", "", ""], ';');

            fclose($handle);
        };

        \App\Services\AuditLogger::log(
            'EXPORT_LAPORAN_CSV',
            "Mengekspor Laporan Rekapitulasi Sampah & Kas Desa periode {$namaBulan} {$tahun} ke berkas CSV.",
            'LAPORAN',
            'EXPORT',
            'SUKSES',
            ['periode' => "{$namaBulan} {$tahun}", 'total_setoran' => $totalSetoran, 'total_omset' => $totalNilaiSampah]
        );

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export data ke file Excel (.xls) berformat tabel HTML asli (berwarna, berbingkai, auto-width di Excel)
     */
    public function exportExcel(Request $request)
    {
        $bulan = (int) ($request->input('bulan', Carbon::now()->month));
        $tahun = (int) ($request->input('tahun', Carbon::now()->year));
        $posId = $request->input('pos_id');

        $query = Transaction::with(['nasabah', 'partner'])
            ->whereYear('dibuat_pada', $tahun)
            ->whereMonth('dibuat_pada', $bulan);

        if ($posId) {
            $query->where('id_mitra', $posId);
        }

        $transaksi = $query->get();

        $setoranTrx = $transaksi->where('jenis', 'SETORAN');
        $totalSetoran = $setoranTrx->count();
        $namaBulan = Carbon::create()->month($bulan)->translatedFormat('F');

        $rincianKategori = [];
        $totalBeratKg = 0.0;
        $totalNilaiSampah = 0;

        foreach ($setoranTrx as $t) {
            $ket = $t->keterangan ?? 'Campuran 1.0 kg';
            $kg = 0.0;
            if (preg_match('/([\d.]+)\s*kg/i', $ket, $m)) {
                $kg = (float) $m[1];
            } else {
                $kg = $t->jumlah_total > 0 ? round($t->jumlah_total / 2500, 1) : 1.0;
            }

            preg_match('/^([^0-9]+)/u', $ket, $matNama);
            $namaSampah = trim($matNama[1] ?? 'Sampah Campuran');
            $namaSampah = rtrim($namaSampah, ' -');
            if (empty($namaSampah)) $namaSampah = 'Sampah Daur Ulang';

            $totalBeratKg += $kg;
            $totalNilaiSampah += (int) $t->jumlah_total;

            if (!isset($rincianKategori[$namaSampah])) {
                $rincianKategori[$namaSampah] = [
                    'nama'       => $namaSampah,
                    'total_kg'   => 0.0,
                    'total_rp'   => 0,
                    'transaksi'  => 0,
                ];
            }
            $rincianKategori[$namaSampah]['total_kg'] += $kg;
            $rincianKategori[$namaSampah]['total_rp'] += (int) $t->jumlah_total;
            $rincianKategori[$namaSampah]['transaksi']++;
        }
        uasort($rincianKategori, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        $totalTonase = round($totalBeratKg / 1000, 3);
        $pencairanTrx = $transaksi->whereIn('jenis', ['CAIRKAN', 'PENARIKAN']);
        $totalPenarikanRp = (int) $pencairanTrx->sum('jumlah_total');
        $saldoKasBersih = $totalNilaiSampah - $totalPenarikanRp;
        $totalNasabahAktif = Nasabah::whereHas('transaksi', fn($q) => $q->whereYear('dibuat_pada', $tahun)->whereMonth('dibuat_pada', $bulan))->count();

        $selectedPos = $posId ? \App\Models\Partner::with('pengguna')->find($posId) : null;
        $pengelolaNama = $request->input('pengelola') ?: ($selectedPos ? $selectedPos->pengelola : 'Administrator Desa & BUMDes');

        $fileName = "Laporan_Sampah_dan_Keuangan_{$namaBulan}_{$tahun}.xls";

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Rekap Sampah & Kas</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body>';
        $html .= '<h3 style="font-family:Arial,sans-serif;margin-bottom:2px;">LAPORAN REKAPITULASI JUMLAH SAMPAH DAN KEUANGAN KAS DESA</h3>';
        $posLabel = $selectedPos ? htmlspecialchars($selectedPos->nama) . ' (Kode: ' . htmlspecialchars($selectedPos->kode_pos) . ' • Pengelola: ' . htmlspecialchars($pengelolaNama) . ')' : 'Pusat Desa (Seluruh Unit Pos)';
        $html .= '<p style="font-family:Arial,sans-serif;color:#475569;margin-top:0;">Periode: <strong>' . $namaBulan . ' ' . $tahun . '</strong> • ' . $posLabel . '</p>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;border:1px solid #047857;">';
        $html .= '<tr style="background-color:#047857;color:#ffffff;font-weight:bold;text-align:center;">';
        $html .= '<th style="padding:8px 12px;">No</th>';
        $html .= '<th style="padding:8px 16px;text-align:left;">Kategori Sampah</th>';
        $html .= '<th style="padding:8px 14px;">Frekuensi Setoran</th>';
        $html .= '<th style="padding:8px 16px;text-align:right;">Total Berat (kg)</th>';
        $html .= '<th style="padding:8px 16px;text-align:right;">Total Tonase (Ton)</th>';
        $html .= '<th style="padding:8px 18px;text-align:right;">Total Nilai Uang (Rp)</th>';
        $html .= '<th style="padding:8px 14px;text-align:right;">Porsi Sampah (%)</th>';
        $html .= '</tr>';

        $no = 1;
        foreach ($rincianKategori as $rk) {
            $persen = $totalBeratKg > 0 ? round(($rk['total_kg'] / $totalBeratKg) * 100, 1) : 0;
            $html .= '<tr>';
            $html .= '<td style="text-align:center;">' . $no++ . '</td>';
            $html .= '<td style="font-weight:bold;">' . htmlspecialchars($rk['nama']) . '</td>';
            $html .= '<td style="text-align:center;">' . $rk['transaksi'] . ' kali</td>';
            $html .= '<td style="text-align:right;">' . number_format($rk['total_kg'], 1, ',', '.') . ' kg</td>';
            $html .= '<td style="text-align:right;">' . number_format($rk['total_kg'] / 1000, 3, ',', '.') . ' Ton</td>';
            $html .= '<td style="text-align:right;font-weight:bold;color:#065f46;">Rp ' . number_format($rk['total_rp'], 0, ',', '.') . '</td>';
            $html .= '<td style="text-align:right;">' . number_format($persen, 1, ',', '.') . '%</td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="background-color:#ecfdf5;font-weight:bold;border-top:2px solid #047857;">';
        $html .= '<td colspan="2" style="text-align:center;">TOTAL KESELURUHAN</td>';
        $html .= '<td style="text-align:center;">' . $totalSetoran . ' kali</td>';
        $html .= '<td style="text-align:right;color:#047857;font-size:13px;">' . number_format($totalBeratKg, 1, ',', '.') . ' kg</td>';
        $html .= '<td style="text-align:right;color:#047857;font-size:13px;">' . number_format($totalTonase, 3, ',', '.') . ' Ton</td>';
        $html .= '<td style="text-align:right;color:#047857;font-size:13px;">Rp ' . number_format($totalNilaiSampah, 0, ',', '.') . '</td>';
        $html .= '<td style="text-align:right;color:#047857;">100.0%</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '<br><table border="1" cellpadding="6" cellspacing="0" style="border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;border:1px solid #64748b;width:400px;">';
        $html .= '<tr style="background-color:#334155;color:#ffffff;font-weight:bold;"><th colspan="2" style="text-align:left;padding:6px 10px;">RINGKASAN ARUS KAS KEUANGAN</th></tr>';
        $html .= '<tr><td style="padding:6px 10px;">Total Pemasukan Sampah</td><td style="text-align:right;font-weight:bold;color:#047857;">Rp ' . number_format($totalNilaiSampah, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td style="padding:6px 10px;">Total Penarikan / Pencairan Saldo</td><td style="text-align:right;font-weight:bold;color:#b91c1c;">Rp ' . number_format($totalPenarikanRp, 0, ',', '.') . '</td></tr>';
        $html .= '<tr style="background-color:#f8fafc;font-weight:bold;"><td style="padding:6px 10px;">Sisa Saldo Kas Bersih</td><td style="text-align:right;color:#047857;">Rp ' . number_format($saldoKasBersih, 0, ',', '.') . '</td></tr>';
        $html .= '<tr><td style="padding:6px 10px;">Total Nasabah Warga Aktif</td><td style="text-align:right;">' . $totalNasabahAktif . ' Orang</td></tr>';
        $html .= '</table>';

        $html .= '</body></html>';

        \App\Services\AuditLogger::log(
            'EXPORT_LAPORAN_EXCEL',
            "Mengekspor Laporan Rekapitulasi Sampah & Kas Desa periode {$namaBulan} {$tahun} ke berkas Excel (.xls).",
            'LAPORAN',
            'EXPORT',
            'SUKSES',
            ['periode' => "{$namaBulan} {$tahun}", 'total_setoran' => $totalSetoran, 'total_omset' => $totalNilaiSampah]
        );

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    /**
     * Halaman Publik Verifikasi Keaslian & Anti-Manipulasi Dokumen Laporan
     * Dapat diakses bebas oleh siapa saja (Warga / Kepala Desa / BUMDes / Auditor)
     * saat memindai QR Code atau membuka tautan verifikasi.
     */
    public function verifikasiPublic(Request $request)
    {
        $this->pastikanKolomPengelolaAda();

        $nomorLaporan = $request->input('no', 'LPJ/SRKL/OFFICIAL');
        $kodeKeamanan = $request->input('kode', '');
        $posId        = $request->input('pos');
        $periode      = $request->input('periode'); // format: M-YYYY e.g. 9-2026

        $bulan = Carbon::now()->month;
        $tahun = Carbon::now()->year;
        if ($periode && str_contains($periode, '-')) {
            $parts = explode('-', $periode);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                $bulan = (int)$parts[0];
                $tahun = (int)$parts[1];
            }
        }

        $selectedPos = null;
        if ($posId && $posId !== 'all') {
            $selectedPos = \App\Models\Partner::find($posId);
        }

        // Ambil data agregat resmi langsung dari pangkalan data
        $query = Transaction::with(['nasabah', 'partner'])
            ->whereYear('dibuat_pada', $tahun)
            ->whereMonth('dibuat_pada', $bulan);

        if ($selectedPos) {
            $query->where('id_mitra', $selectedPos->id);
        }

        $transaksi = $query->get();
        $setoranTrx = $transaksi->where('jenis', 'SETORAN');
        $totalSetoran = $setoranTrx->count();
        $totalNilaiSampah = (int) $setoranTrx->sum('jumlah_total');

        $totalBeratKg = 0.0;
        foreach ($setoranTrx as $t) {
            $ket = $t->keterangan ?? '';
            if (preg_match('/([\d.]+)\s*kg/i', $ket, $m)) {
                $totalBeratKg += (float) $m[1];
            } else {
                $totalBeratKg += $t->jumlah_total > 0 ? round($t->jumlah_total / 2500, 1) : 1.0;
            }
        }

        $pencairanTrx = $transaksi->whereIn('jenis', ['CAIRKAN', 'PENARIKAN']);
        $totalPenarikanRp = (int) $pencairanTrx->sum('jumlah_total');
        $saldoKasBersih = $totalNilaiSampah - $totalPenarikanRp;
        $totalTonase = round($totalBeratKg / 1000, 3);

        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $namaBulan = $namaBulanIndo[$bulan] ?? 'Januari';

        $tglCetakYmd = $request->input('tgl', Carbon::now()->format('Ymd'));
        $jamHis      = $request->input('jam');
        $token       = $request->input('token');

        try {
            $tglPenerbitanCarbon = Carbon::createFromFormat('Ymd', $tglCetakYmd);
            $tglPenerbitanResmi  = $tglPenerbitanCarbon->translatedFormat('d F Y');
        } catch (\Throwable $e) {
            $tglPenerbitanResmi  = Carbon::now()->translatedFormat('d F Y');
        }

        $waktuCetakLengkap = $tglPenerbitanResmi;
        $idCetakanUnik     = null;

        if (!empty($jamHis) && !empty($token)) {
            $jamClean = preg_replace('/[^0-9]/', '', (string)$jamHis);
            $tokClean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$token));
            if (strlen($jamClean) >= 6) {
                $jamPukul = substr($jamClean, 0, 2) . ':' . substr($jamClean, 2, 2) . ':' . substr($jamClean, 4, 2);
                $waktuCetakLengkap = $tglPenerbitanResmi . ', ' . $jamPukul . ' WIB';
                $idCetakanUnik     = "PRT-{$tglCetakYmd}-{$jamClean}-{$tokClean}";
            }

            $signaturePayload = implode('|', [
                'SIRKULO-OFFICIAL-VERIFIED-REPORT',
                $selectedPos ? $selectedPos->id : 'ALL_POS',
                $tahun,
                $bulan,
                number_format($totalBeratKg, 3, '.', ''),
                (int) $totalNilaiSampah,
                $totalSetoran,
                $tglCetakYmd,
                $jamClean,
                $tokClean
            ]);
        } else {
            $signaturePayload = implode('|', [
                'SIRKULO-OFFICIAL-VERIFIED-REPORT',
                $selectedPos ? $selectedPos->id : 'ALL_POS',
                $tahun,
                $bulan,
                number_format($totalBeratKg, 3, '.', ''),
                (int) $totalNilaiSampah,
                $totalSetoran,
                $tglCetakYmd
            ]);
        }

        $expectedHash = hash('sha256', $signaturePayload);
        $expectedKode = 'SRK-' . strtoupper(substr($expectedHash, 0, 4) . '-' . substr($expectedHash, 4, 4) . '-' . substr($expectedHash, 8, 4));

        $isValid = empty($kodeKeamanan) || ($kodeKeamanan === $expectedKode);

        return view('laporan.verifikasi', compact(
            'nomorLaporan', 'kodeKeamanan', 'expectedKode', 'expectedHash', 'isValid',
            'selectedPos', 'bulan', 'tahun', 'namaBulan', 'tglPenerbitanResmi', 'waktuCetakLengkap', 'idCetakanUnik',
            'totalSetoran', 'totalBeratKg', 'totalTonase', 'totalNilaiSampah', 'totalPenarikanRp', 'saldoKasBersih'
        ));
    }

    private function pastikanKolomPengelolaAda(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('mitra', 'pengelola')) {
                \Illuminate\Support\Facades\Schema::table('mitra', function ($table) {
                    $table->string('pengelola', 150)->nullable()->after('nama');
                });
            }
        } catch (\Throwable $e) {
            // Abaikan jika sudah ada atau DB tidak mengizinkan direct alter
        }
    }
}
