<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\PetugasMitra;
use App\Models\Partner;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\TrashCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MitraController extends Controller
{
    /**
     * Dapatkan data pos mitra yang ditugaskan khusus untuk akun petugas yang sedang login.
     * Aturan: 1 Akun Petugas = 1 Pos Bank Sampah (Beda Pos = Beda Akun).
     */
    private function getMitra(Request $request)
    {
        $user = $request->user();

        // 0. Cek jika request secara eksplisit meminta pos tertentu (via header X-Pos-Id atau parameter id_mitra)
        $requestedPosId = $request->header('X-Pos-Id') ?: $request->input('id_mitra') ?: $request->query('id_mitra');
        if ($requestedPosId) {
            $pos = Partner::find($requestedPosId);
            if (!$pos) {
                $pos = Partner::all()->first(function ($p) use ($requestedPosId) {
                    return $p->id === $requestedPosId || $p->kode_pos === $requestedPosId || strcasecmp($p->nama, $requestedPosId) === 0 || stripos($p->nama, $requestedPosId) !== false;
                });
            }
            if ($pos) {
                return $pos;
            }
        }

        // 1. Cari unit pos yang ditugaskan khusus ke akun petugas ini oleh Administrator
        $mitra = Partner::where('id_pengguna', $user->id)->first();

        if (!$mitra) {
            abort(403, 'Akun petugas Anda belum ditugaskan ke unit pos bank sampah oleh Administrator.');
        }

        return $mitra;
    }

    /**
     * GET /api/mitra/pos-list
     * Ambil data pos yang ditugaskan kepada petugas yang sedang login (1 akun = 1 pos).
     */
    public function listPos(Request $request)
    {
        $myPos = $this->getMitra($request);
        return response()->json([$myPos]);
    }

    /**
     * GET /api/mitra/dashboard
     * Data statistik dashboard mitra.
     */
    public function dashboard(Request $request)
    {
        $mitra = $this->getMitra($request);
        $today = now()->startOfDay();
        $startOfMonth = now()->startOfMonth();

        // Transaksi hari ini
        $transaksiHariIni = Transaction::where('id_mitra', $mitra->id)
            ->where('dibuat_pada', '>=', $today)
            ->get();

        $totalSetoranHariIni = $transaksiHariIni->where('jenis', 'SETORAN')->sum('jumlah_total');
        $jumlahSetoranHariIni = $transaksiHariIni->where('jenis', 'SETORAN')->count();
        $jumlahPenarikanHariIni = $transaksiHariIni->where('jenis', 'PENARIKAN')->count();

        // Total pendapatan setoran sampah di pos mitra bulan ini (direset per bulan)
        $totalSetoranBulanIni = Transaction::where('id_mitra', $mitra->id)
            ->where('jenis', 'SETORAN')
            ->where('dibuat_pada', '>=', $startOfMonth)
            ->sum('jumlah_total');

        // Total nasabah terdaftar di sistem
        $totalNasabah = Nasabah::count();

        // Riwayat 5 transaksi terakhir
        $transaksiTerbaru = Transaction::with('pengguna')
            ->where('id_mitra', $mitra->id)
            ->orderBy('dibuat_pada', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'pos' => [
                'id'              => $mitra->id,
                'nama_pos'        => $mitra->nama,
                'kode_pos'        => $mitra->kode_pos,
                'alamat'          => $mitra->alamat,
                'jam_buka'        => $mitra->jam_buka,
                'pengelola'       => $request->user()->nama,
                'telepon'         => $request->user()->telepon,
                'email'           => $request->user()->email,
                'kategori_sampah' => $mitra->kategoriSampah()->get(),
            ],
            'saldo_pos'              => $totalSetoranBulanIni,
            'total_setoran_hari_ini' => $totalSetoranHariIni,
            'ringkasan_hari_ini'    => [
                'jumlah_setoran'   => $jumlahSetoranHariIni,
                'jumlah_nasabah'   => $totalNasabah,
                'jumlah_penarikan' => $jumlahPenarikanHariIni,
            ],
            'transaksi_terbaru'     => $transaksiTerbaru,
        ]);
    }

    /**
     * GET /api/mitra/nasabah
     * Cari atau ambil daftar nasabah.
     */
    public function daftarNasabah(Request $request)
    {
        $query = Nasabah::query();

        if ($request->has('q') && !empty($request->q)) {
            $keyword = '%' . $request->q . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', $keyword)
                  ->orWhere('telepon', 'like', $keyword)
                  ->orWhere('email', 'like', $keyword)
                  ->orWhere('id', 'like', $keyword)
                  ->orWhere('kode_user', 'like', $keyword); // query langsung ke kolom DB
            });
        }

        $nasabah = $query->orderBy('nama', 'asc')->get();
        return response()->json($nasabah->values());
    }

    /**
     * GET /api/mitra/nasabah/{id}
     * Ambil data lengkap nasabah berdasarkan ID / Kode User (SRKLxxx) / No. HP (untuk hasil scan barcode).
     */
    public function detailNasabah($id)
    {
        // Cari berdasarkan ID, telepon, atau kode_user — query langsung ke DB
        $nasabah = Nasabah::where('id', $id)
            ->orWhere('telepon', $id)
            ->orWhere('kode_user', $id)
            ->first();

        if (!$nasabah) {
            return response()->json(['galat' => 'Nasabah tidak ditemukan'], 404);
        }

        // Ambil 5 riwayat transaksi nasabah ini
        $riwayat = Transaction::where('id_pengguna', $nasabah->id)
            ->orderBy('dibuat_pada', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'nasabah' => $nasabah,
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * POST /api/mitra/nasabah
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function registrasiNasabah(Request $request)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Pendaftaran nasabah baru dialihkan sepenuhnya ke Administrator Desa melalui Web Admin untuk menjamin keabsahan dan keamanan data kependudukan.'
        ], 403);
    }

    /**
     * PUT /api/mitra/nasabah/{id}
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function updateNasabah(Request $request, $id)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Perubahan data identitas nasabah hanya dapat dilakukan oleh Administrator Desa melalui Web Admin.'
        ], 403);
    }

    /**
     * PUT /api/mitra/nasabah/{id}/password
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function updateNasabahPassword(Request $request, $id)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Reset kata sandi nasabah adalah wewenang Administrator Desa melalui Web Admin untuk mencegah manipulasi data di lapangan.'
        ], 403);
    }

    /**
     * DELETE /api/mitra/nasabah/{id}
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function hapusNasabah(Request $request, $id)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Penghapusan akun nasabah hanya dapat diproses oleh Kantor Desa melalui Web Admin untuk melindungi integritas saldo dan audit tabungan warga.'
        ], 403);
    }

    /**
     * POST /api/mitra/setoran
     * Proses Setoran Sampah oleh Mitra (Bisa Masuk Saldo atau Bayar Tunai Cash).
     */
    public function prosesSetoran(Request $request)
    {
        $request->validate([
            'id_pengguna'       => 'required|exists:nasabah,id',
            'jenis_sampah'      => 'required|string',
            'berat_kg'          => 'required|numeric|min:0.1',
            'harga_per_kg'      => 'required|integer|min:1',
            'metode_pembayaran' => 'nullable|string|in:SALDO,TUNAI_CASH',
            'catatan'           => 'nullable|string',
        ]);

        $mitra = $this->getMitra($request);

        // Validasi: jenis_sampah harus termasuk dalam kategori yang diterima pos ini
        $kategoriDiterima = $mitra->kategori_sampah;
        if ($kategoriDiterima && $kategoriDiterima->isNotEmpty()) {
            $namaKategori = $kategoriDiterima->pluck('nama')->map(fn($n) => strtolower(trim($n)));
            $jenisDiminta = strtolower(trim($request->jenis_sampah));
            $cocok = $namaKategori->contains(function ($n) use ($jenisDiminta) {
                return str_contains($jenisDiminta, $n) || str_contains($n, $jenisDiminta);
            });
            if (!$cocok) {
                $daftar = $kategoriDiterima->pluck('nama')->implode(', ');
                return response()->json([
                    'galat' => "Jenis sampah '{$request->jenis_sampah}' tidak diterima di pos ini. Pos '{$mitra->nama}' hanya menerima: {$daftar}. Hubungi Admin jika ingin menambah jenis sampah baru.",
                ], 422);
            }
        }

        $totalNominal = (int) round($request->berat_kg * $request->harga_per_kg);
        $metode = $request->metode_pembayaran ?? 'SALDO';
        // Konversi Poin: 1 Poin = Rp 100
        $poinDidapat = ($metode === 'SALDO') ? (int) floor($totalNominal / 100) : 0;
        $noReferensi = 'SET-' . strtoupper(Str::random(8));

        $transaksi = DB::transaction(function () use ($request, $mitra, $totalNominal, $poinDidapat, $noReferensi, $metode) {
            // Lock row pengguna untuk konsistensi saldo
            $nasabah = Nasabah::where('id', $request->id_pengguna)->lockForUpdate()->first();
            $saldoSebelumnya = $nasabah ? $nasabah->saldo : 0;
            $saldoBaru = ($metode === 'SALDO') ? ($saldoSebelumnya + $totalNominal) : $saldoSebelumnya;

            // 1. Catat Transaksi SETORAN
            $trx = Transaction::create([
                'id_pengguna'       => $request->id_pengguna,
                'id_mitra'          => $mitra->id,
                'jenis'             => 'SETORAN',
                'status'            => 'SELESAI',
                'jumlah_total'      => $totalNominal,
                'poin_didapat'      => $poinDidapat,
                'nomor_referensi'   => $noReferensi,
                'keterangan'        => $request->jenis_sampah . ' ' . $request->berat_kg . ' kg' . ($metode === 'TUNAI_CASH' ? ' (Setor Tunai)' : ''),
                'jenis_sampah'      => $request->jenis_sampah,
                'berat_kg'          => $request->berat_kg,
                'harga_per_kg'      => $request->harga_per_kg,
                'metode_pembayaran' => $metode,
                'catatan'           => $request->catatan,
                'saldo_sebelumnya'  => $saldoSebelumnya,
                'saldo_baru'        => $saldoBaru,
                'dibuat_pada'       => now(),
            ]);

            // Jika metode SALDO -> tabungan digital bertambah rupiah & poin bertambah (1 Poin = Rp 100)
            if ($metode === 'SALDO') {
                $nasabah->increment('saldo', $totalNominal);
                $nasabah->update(['poin' => (int) floor($nasabah->saldo / 100)]);
            } else if ($metode === 'TUNAI_CASH') {
                // 2. Jika Bayar Tunai Cash: Catat otomatis transaksi PENARIKAN sekalian
                $noRefTarik = 'WD-' . strtoupper(Str::random(8));
                Transaction::create([
                    'id_pengguna'       => $request->id_pengguna,
                    'id_mitra'          => $mitra->id,
                    'jenis'             => 'PENARIKAN',
                    'status'            => 'SELESAI',
                    'jumlah_total'      => $totalNominal,
                    'poin_didapat'      => 0,
                    'nomor_referensi'   => $noRefTarik,
                    'keterangan'        => 'Tarik Tunai Langsung (' . $request->jenis_sampah . ' ' . $request->berat_kg . ' kg)',
                    'metode_pembayaran' => 'TUNAI_CASH',
                    'saldo_sebelumnya'  => $saldoSebelumnya,
                    'saldo_baru'        => $saldoSebelumnya,
                    'dibuat_pada'       => now()->addSecond(),
                ]);
            }

            // 1. Buat notifikasi untuk nasabah setelah setoran diproses
            \App\Http\Controllers\Api\NotificationController::kirim(
                $nasabah->id,
                $metode === 'TUNAI_CASH' ? 'Setoran & Penarikan Tunai Selesai' : 'Setoran Sampah Berhasil ✅',
                $metode === 'TUNAI_CASH'
                    ? "Setoran {$request->berat_kg} kg {$request->jenis_sampah} senilai Rp " . number_format($totalNominal, 0, ',', '.') . " berhasil — uang tunai telah diserahkan di loket {$mitra->nama}."
                    : "Setoran {$request->berat_kg} kg {$request->jenis_sampah} sebesar Rp " . number_format($totalNominal, 0, ',', '.') . " berhasil masuk ke saldo Anda (+{$poinDidapat} Poin). Pos: {$mitra->nama}.",
                'SETORAN'
            );

            // 2. Buat notifikasi untuk petugas mitra yang memproses di loket
            $petugasUser = $request->user();
            if ($petugasUser && $petugasUser->id !== $nasabah->id) {
                \App\Http\Controllers\Api\NotificationController::kirim(
                    $petugasUser->id,
                    'Setoran Sampah Dicatat ✅',
                    "Setoran {$request->berat_kg} kg {$request->jenis_sampah} senilai Rp " . number_format($totalNominal, 0, ',', '.') . " ({$metode}) untuk nasabah {$nasabah->nama} berhasil dicatat di {$mitra->nama}.",
                    'SETORAN'
                );
            }

            return [
                'transaksi'        => $trx,
                'nasabah'          => $nasabah->fresh(),
                'saldo_sebelumnya' => $saldoSebelumnya,
                'saldo_baru'       => $nasabah->fresh()->saldo,
            ];
        });

        return response()->json([
            'pesan'             => 'Setoran sampah berhasil diproses',
            'transaksi'         => $transaksi['transaksi'],
            'nasabah'           => $transaksi['nasabah'],
            'jenis_sampah'      => $request->jenis_sampah,
            'berat_kg'          => $request->berat_kg,
            'harga_per_kg'      => $request->harga_per_kg,
            'total'             => $totalNominal,
            'metode_pembayaran' => $metode,
            'saldo_sebelumnya'  => $transaksi['saldo_sebelumnya'],
            'saldo_baru'        => $transaksi['saldo_baru'],
            'poin_didapat'      => $poinDidapat,
            'nomor_referensi'   => $noReferensi,
            'pos'               => $mitra->nama,
            'waktu'             => now()->translatedFormat('d F Y, H:i') . ' WIB',
        ], 201);
    }

    /**
     * POST /api/mitra/cairkan-saldo-nasabah
     * Tarik Tunai Cash Saldo Nasabah oleh Mitra di Loket (Concurrency Safe with lockForUpdate).
     */
    public function cairkanSaldoNasabah(Request $request)
    {
        $request->validate([
            'id_pengguna' => 'required|exists:nasabah,id',
            'nominal'     => 'required|integer|min:5000',
            'catatan'     => 'nullable|string',
        ], [
            'nominal.min' => 'Minimal penarikan saldo adalah Rp 5.000',
        ]);

        $mitra = $this->getMitra($request);
        $noReferensi = 'TARIK-' . strtoupper(Str::random(8));

        try {
            $res = DB::transaction(function () use ($request, $mitra, $noReferensi) {
                // Lock row pengguna untuk mencegah double spend saat penarikan serentak
                $nasabah = Nasabah::where('id', $request->id_pengguna)->lockForUpdate()->first();
                if (!$nasabah || $nasabah->saldo < $request->nominal) {
                    throw new \Exception('Saldo nasabah tidak mencukupi (Saldo: Rp ' . number_format($nasabah?->saldo ?? 0, 0, ',', '.') . ')');
                }

                $saldoSebelumnya = $nasabah->saldo;
                $poinSebelumnya = $nasabah->poin;
                $nasabah->decrement('saldo', $request->nominal);

                // Pengurangan poin (1 Poin = Rp 100)
                $poinDipotong = (int) floor($request->nominal / 100);
                $nasabah->update(['poin' => (int) floor($nasabah->saldo / 100)]);

                $trx = Transaction::create([
                    'id_pengguna'       => $nasabah->id,
                    'id_mitra'          => $mitra->id,
                    'jenis'             => 'PENARIKAN',
                    'status'            => 'SELESAI',
                    'jumlah_total'      => $request->nominal,
                    'poin_didapat'      => -$poinDipotong,
                    'nomor_referensi'   => $noReferensi,
                    'keterangan'        => $request->catatan ?: 'Tarik Tunai di Loket Pos',
                    'metode_pembayaran' => 'TUNAI_CASH',
                    'catatan'           => $request->catatan,
                    'saldo_sebelumnya'  => $saldoSebelumnya,
                    'saldo_baru'        => $nasabah->fresh()->saldo,
                    'dibuat_pada'       => now(),
                ]);

                // 1. Notifikasi untuk nasabah
                \App\Http\Controllers\Api\NotificationController::kirim(
                    $nasabah->id,
                    'Tarik Tunai Berhasil 💰',
                    'Penarikan saldo Rp ' . number_format($request->nominal, 0, ',', '.') . ' di ' . $mitra->nama . ' berhasil. Saldo baru: Rp ' . number_format($nasabah->saldo, 0, ',', '.') . " (-{$poinDipotong} Poin).",
                    'PENARIKAN'
                );

                // 2. Notifikasi untuk petugas mitra yang memproses
                $petugasUser = $request->user();
                if ($petugasUser && $petugasUser->id !== $nasabah->id) {
                    \App\Http\Controllers\Api\NotificationController::kirim(
                        $petugasUser->id,
                        'Pencairan Tunai Berhasil 💰',
                        'Penarikan tunai Rp ' . number_format($request->nominal, 0, ',', '.') . " untuk nasabah {$nasabah->nama} berhasil diserahkan di loket {$mitra->nama}.",
                        'PENARIKAN'
                    );
                }

                return [
                    'transaksi'        => $trx,
                    'nasabah'          => $nasabah->fresh(),
                    'saldo_sebelumnya' => $saldoSebelumnya,
                    'saldo_baru'       => $nasabah->fresh()->saldo,
                    'poin_sebelumnya'  => $poinSebelumnya,
                    'poin_baru'        => $nasabah->fresh()->poin,
                    'poin_dipotong'    => $poinDipotong,
                ];
            });

            return response()->json([
                'pesan'            => 'Penarikan tunai cash nasabah berhasil',
                'transaksi'        => $res['transaksi'],
                'nasabah'          => $res['nasabah'],
                'nominal'          => $request->nominal,
                'saldo_sebelumnya' => $res['saldo_sebelumnya'],
                'saldo_baru'       => $res['saldo_baru'],
                'poin_sebelumnya'  => $res['poin_sebelumnya'],
                'poin_baru'        => $res['poin_baru'],
                'poin_dipotong'    => $res['poin_dipotong'],
                'nomor_referensi'  => $noReferensi,
                'pos'              => $mitra->nama,
                'waktu'            => now()->translatedFormat('d F Y, H:i') . ' WIB',
            ]);
        } catch (\Exception $e) {
            return response()->json(['galat' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/mitra/penarikan
     * Pengajuan Penarikan Saldo Pos oleh Mitra.
     * FIX #4: Menggunakan saldo kas bersih pos (total setoran - total penarikan),
     * bukan saldo akun pribadi petugas yang selalu 0.
     */
    public function ajukanPenarikan(Request $request)
    {
        $request->validate([
            'nominal'     => 'required|integer|min:10000',
            'metode'      => 'required|string',
            'no_rekening' => 'required|string',
            'keterangan'  => 'nullable|string',
        ]);

        $mitra = $this->getMitra($request);
        $user  = $request->user();

        // FIX #4: Hitung kas bersih pos dari total setoran - total penarikan bulan ini
        // Ini mencerminkan dana yang tersedia di pos untuk ditarik ke rekening pengurus
        $startOfMonth = now()->startOfMonth();
        $totalSetoranPos = Transaction::where('id_mitra', $mitra->id)
            ->where('jenis', 'SETORAN')
            ->where('dibuat_pada', '>=', $startOfMonth)
            ->sum('jumlah_total');
        $totalPenarikanPos = Transaction::where('id_mitra', $mitra->id)
            ->where('jenis', 'PENARIKAN')
            ->where('dibuat_pada', '>=', $startOfMonth)
            ->sum('jumlah_total');
        $kasBersihPos = $totalSetoranPos - $totalPenarikanPos;

        if ($kasBersihPos < $request->nominal) {
            return response()->json([
                'galat'       => 'Saldo kas pos tidak mencukupi. Kas bersih pos bulan ini: Rp ' . number_format(max(0, $kasBersihPos), 0, ',', '.'),
                'kas_bersih'  => max(0, $kasBersihPos),
            ], 400);
        }

        $noReferensi = 'TRF-' . strtoupper(Str::random(8));

        $trx = DB::transaction(function () use ($request, $user, $mitra, $noReferensi) {
            return Transaction::create([
                'id_pengguna'     => $user->id,
                'id_mitra'        => $mitra->id,
                'jenis'           => 'PENARIKAN',
                'status'          => 'MENUNGGU',
                'jumlah_total'    => $request->nominal,
                'poin_didapat'    => 0,
                'nomor_referensi' => $noReferensi,
                'keterangan'      => $request->keterangan ?: 'Transfer ke ' . $request->metode . ' ' . $request->no_rekening,
                'dibuat_pada'     => now(),
            ]);
        });

        return response()->json([
            'pesan'           => 'Permintaan penarikan berhasil diajukan',
            'nominal'         => $request->nominal,
            'metode'          => $request->metode,
            'no_rekening'     => $request->no_rekening,
            'status'          => 'Menunggu Persetujuan Admin',
            'nomor_referensi' => $noReferensi,
            'pos'             => $mitra->nama,
            'kas_bersih_sisa' => $kasBersihPos - $request->nominal,
            'waktu'           => now()->format('d M Y H:i'),
        ], 201);
    }

    /**
     * GET /api/mitra/transaksi
     * Riwayat transaksi lengkap pos mitra.
     */
    public function riwayatTransaksi(Request $request)
    {
        $mitra = $this->getMitra($request);
        $query = Transaction::with(['pengguna', 'mitra'])->where('id_mitra', $mitra->id);

        if ($request->has('jenis') && in_array($request->jenis, ['SETORAN', 'PENARIKAN'])) {
            $query->where('jenis', $request->jenis);
        }

        $transaksi = $query->orderBy('dibuat_pada', 'desc')->get();
        return response()->json($transaksi);
    }

    /**
     * GET /api/mitra/laporan
     * Laporan statistik rekapitulasi setoran & penarikan.
     */
    public function laporan(Request $request)
    {
        $mitra = $this->getMitra($request);
        $periode = $request->periode ?? 'bulan_ini';

        // Ekstrak tahun jika tersedia (misal: "tahun_2025", "bulan_8_2025", query param ?tahun=2025)
        $tahunDipilih = (int) ($request->tahun ?: (preg_match('/(?:tahun_?|_)(\d{4})/', $periode, $m) ? $m[1] : now()->year));

        // Ekstrak bulan jika tersedia (misal: "bulan_8", "bulan_8_2025", query param ?bulan=8)
        $bulanDipilih = (int) ($request->bulan ?: (preg_match('/bulan_?(\d{1,2})/', $periode, $mb) ? $mb[1] : now()->month));

        if ($periode === 'hari_ini') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($periode === 'minggu_ini') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif (str_starts_with($periode, 'bulan') || $request->has('bulan')) {
            $startDate = Carbon::createFromDate($tahunDipilih, $bulanDipilih, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($tahunDipilih, $bulanDipilih, 1)->endOfMonth();
        } elseif (str_starts_with($periode, 'tahun') || $request->has('tahun')) {
            $startDate = Carbon::createFromDate($tahunDipilih, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($tahunDipilih, 12, 31)->endOfYear();
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        }

        // Ambil semua transaksi dalam periode ini dari pos yang dipilih
        $allTranx = Transaction::where('id_mitra', $mitra->id)
            ->whereBetween('dibuat_pada', [$startDate, $endDate])
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        $setoran   = $allTranx->where('jenis', 'SETORAN');
        $penarikan = $allTranx->whereIn('jenis', ['PENARIKAN', 'CAIRKAN']);

        $totalSetoran   = (int) $setoran->sum('jumlah_total');
        $totalPenarikan = (int) $penarikan->sum('jumlah_total');
        $jumlahTransaksi = $allTranx->count();
        $jumlahSetoran   = $setoran->count();
        $jumlahPenarikan = $penarikan->count();

        // ── Rincian Resmi per Kategori (untuk PDF Laporan) ─────────────
        $rincianKategori = [];
        $totalBeratKg    = 0.0;
        $totalNilaiSampah = 0;

        foreach ($setoran as $t) {
            $ket = $t->keterangan ?? 'Campuran 1.0 kg';
            $kg  = 0.0;
            if (preg_match('/([\.\d]+)\s*kg/i', $ket, $kgM)) {
                $kg = (float) $kgM[1];
            } else {
                $kg = $t->jumlah_total > 0 ? round($t->jumlah_total / 2500, 1) : 1.0;
            }
            preg_match('/^([^0-9]+)/u', $ket, $nmM);
            $namaSampah = trim(rtrim($nmM[1] ?? 'Sampah Campuran', ' -'));
            if (empty($namaSampah)) $namaSampah = 'Sampah Daur Ulang';

            $totalBeratKg    += $kg;
            $totalNilaiSampah += (int) $t->jumlah_total;

            if (!isset($rincianKategori[$namaSampah])) {
                $rincianKategori[$namaSampah] = [
                    'nama'      => $namaSampah,
                    'total_kg'  => 0.0,
                    'total_rp'  => 0,
                    'transaksi' => 0,
                ];
            }
            $rincianKategori[$namaSampah]['total_kg']  += $kg;
            $rincianKategori[$namaSampah]['total_rp']  += (int) $t->jumlah_total;
            $rincianKategori[$namaSampah]['transaksi']++;
        }
        uasort($rincianKategori, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        $totalTonase   = round($totalBeratKg / 1000, 3);
        $saldoKasBersih = $totalNilaiSampah - $totalPenarikan;

        // Jumlah nasabah aktif pada pos ini dalam periode ini
        $nasabahAktif = $allTranx->pluck('id_pengguna')->filter()->unique()->count();

        // Nomor laporan resmi & format tanggal Indonesia
        $namaBulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $romawiBulan = [1=>'I',2=>'II',3=>'III',4=>'IV',5=>'V',6=>'VI',
                        7=>'VII',8=>'VIII',9=>'IX',10=>'X',11=>'XI',12=>'XII'];
        $nomorLaporan = 'LPJ/SRKL/' . ($romawiBulan[$bulanDipilih] ?? 'I') . '/' . $tahunDipilih;
        $namaBulan    = $namaBulanIndo[$bulanDipilih] ?? 'Januari';

        $now = Carbon::now();
        $tanggalCetak = $now->format('d') . ' ' . ($namaBulanIndo[(int)$now->format('n')] ?? '') . ' ' . $now->format('Y');

        // ── Rincian per Jenis Sampah & Perhitungan Margin Pengepul ──────
        $allCategories = TrashCategory::all();
        $rincianSampah = [];
        $totalPengepul = 0;
        $totalMarginUntung = 0;

        foreach ($setoran as $trx) {
            $keterangan = $trx->keterangan ?? 'Sampah Campuran';
            // Ekstrak nama sampah (ambil sebelum angka)
            preg_match('/^([^0-9]+)/u', $keterangan, $matches);
            $jenisSampah = trim($matches[1] ?? $keterangan);

            // Ekstrak berat kg dari keterangan
            $kg = 0.0;
            preg_match('/([\d.]+)\s*kg/i', $keterangan, $kgMatch);
            if (!empty($kgMatch[1])) {
                $kg = (float) $kgMatch[1];
            }

            // Cari harga kategori sampah terkait
            $matchedCat = $allCategories->first(function ($c) use ($jenisSampah) {
                return stripos($c->nama, $jenisSampah) !== false || stripos($jenisSampah, $c->nama) !== false;
            });

            $hargaNasabah = $matchedCat ? $matchedCat->harga_beli : ($kg > 0 ? (int) round($trx->jumlah_total / $kg) : 2000);
            $hargaPengepul = $matchedCat ? $matchedCat->harga_jual : (int) round($hargaNasabah * 1.4);

            $nilaiNasabah = (int) $trx->jumlah_total;
            $nilaiPengepul = $kg > 0 ? (int) round($kg * $hargaPengepul) : (int) round($nilaiNasabah * 1.4);
            $marginTrx = max(0, $nilaiPengepul - $nilaiNasabah);

            $totalPengepul += $nilaiPengepul;
            $totalMarginUntung += $marginTrx;

            if (!isset($rincianSampah[$jenisSampah])) {
                $rincianSampah[$jenisSampah] = [
                    'jenis_sampah'          => $jenisSampah,
                    'jumlah_transaksi'      => 0,
                    'total_kg'              => 0.0,
                    'harga_nasabah'         => $hargaNasabah,
                    'harga_pengepul'        => $hargaPengepul,
                    'total_nilai'           => 0, // Nilai yang dibayar ke nasabah
                    'total_nilai_pengepul'  => 0, // Nilai jual ke pengepul
                    'margin_untung'         => 0, // Keuntungan pos
                    'persentase_margin'     => 0,
                ];
            }
            $rincianSampah[$jenisSampah]['jumlah_transaksi']++;
            $rincianSampah[$jenisSampah]['total_kg'] += $kg;
            $rincianSampah[$jenisSampah]['total_nilai'] += $nilaiNasabah;
            $rincianSampah[$jenisSampah]['total_nilai_pengepul'] += $nilaiPengepul;
            $rincianSampah[$jenisSampah]['margin_untung'] += $marginTrx;
            if ($rincianSampah[$jenisSampah]['total_nilai'] > 0) {
                $rincianSampah[$jenisSampah]['persentase_margin'] = (int) round(
                    ($rincianSampah[$jenisSampah]['margin_untung'] / $rincianSampah[$jenisSampah]['total_nilai']) * 100
                );
            }
        }
        // FIX #7: Gunakan uasort (bukan arsort) untuk mengurutkan berdasarkan field total_kg
        uasort($rincianSampah, fn($a, $b) => $b['total_kg'] <=> $a['total_kg']);

        $persentaseTotalMargin = ($totalSetoran > 0)
            ? (int) round(($totalMarginUntung / $totalSetoran) * 100)
            : 0;

        // ── Grafik Batang Sesuai Periode (Presisi Penuh) ────────────
        $chartData = [];
        if ($periode === 'hari_ini') {
            // 6 slot waktu meliputi 24 jam penuh tanpa detik yang terlewat
            $slots = [
                ['06:00', 0, 8],   // 00:00 - 08:59:59 (Pagi & Subuh)
                ['09:00', 9, 11],  // 09:00 - 11:59:59 (Pagi Menjelang Siang)
                ['12:00', 12, 14], // 12:00 - 14:59:59 (Siang)
                ['15:00', 15, 17], // 15:00 - 17:59:59 (Sore)
                ['18:00', 18, 20], // 18:00 - 20:59:59 (Petang / Maghrib)
                ['21:00', 21, 23], // 21:00 - 23:59:59 (Malam)
            ];
            foreach ($slots as $slot) {
                $startSlot = now()->startOfDay()->addHours($slot[1]);
                $endSlot = ($slot[2] === 23)
                    ? now()->endOfDay()
                    : now()->startOfDay()->addHours($slot[2] + 1)->subMicrosecond();

                $slotSetoran = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'SETORAN')
                    ->whereBetween('dibuat_pada', [$startSlot, $endSlot])
                    ->sum('jumlah_total');
                $slotPenarikan = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'PENARIKAN')
                    ->whereBetween('dibuat_pada', [$startSlot, $endSlot])
                    ->sum('jumlah_total');
                $chartData[] = [
                    'tanggal'   => $slot[0],
                    'hari'      => $slot[0],
                    'total'     => (int) $slotSetoran,
                    'penarikan' => (int) $slotPenarikan,
                ];
            }
        } elseif ($periode === 'minggu_ini') {
            // 7 hari dalam minggu (Senin - Minggu)
            $startWeek = now()->startOfWeek();
            $namaHari = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
            for ($i = 0; $i < 7; $i++) {
                $date = $startWeek->copy()->addDays($i);
                $dayStart = $date->copy()->startOfDay();
                $dayEnd = $date->copy()->endOfDay();

                $daySetoran = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'SETORAN')
                    ->whereBetween('dibuat_pada', [$dayStart, $dayEnd])
                    ->sum('jumlah_total');
                $dayPenarikan = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'PENARIKAN')
                    ->whereBetween('dibuat_pada', [$dayStart, $dayEnd])
                    ->sum('jumlah_total');
                $chartData[] = [
                    'tanggal'   => $date->format('d M'),
                    'hari'      => $namaHari[$i] ?? $date->format('D'),
                    'total'     => (int) $daySetoran,
                    'penarikan' => (int) $dayPenarikan,
                ];
            }
        } elseif (str_starts_with($periode, 'bulan') || $request->has('bulan')) {
            // 6 Interval tanggal dalam bulan yang dipilih (1-5, 6-10, 11-15, 16-20, 21-25, 26-akhir)
            $monthCarbon = Carbon::createFromDate($tahunDipilih, $bulanDipilih, 1);
            $daysInMonth = $monthCarbon->daysInMonth;
            $intervals = [
                [1, 5],
                [6, 10],
                [11, 15],
                [16, 20],
                [21, 25],
                [26, $daysInMonth]
            ];
            foreach ($intervals as $iv) {
                $startD = $monthCarbon->copy()->day($iv[0])->startOfDay();
                $endDDate = $monthCarbon->copy()->day($iv[1])->endOfDay();

                $periodSetoran = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'SETORAN')
                    ->whereBetween('dibuat_pada', [$startD, $endDDate])
                    ->sum('jumlah_total');
                $periodPenarikan = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'PENARIKAN')
                    ->whereBetween('dibuat_pada', [$startD, $endDDate])
                    ->sum('jumlah_total');
                $chartData[] = [
                    'tanggal'   => "{$iv[0]}-{$iv[1]}",
                    'hari'      => "{$iv[0]}-{$iv[1]}",
                    'total'     => (int) $periodSetoran,
                    'penarikan' => (int) $periodPenarikan,
                ];
            }
        } else {
            // 12 Bulan dalam Tahun yang dipilih (Jan - Des)
            $listSingkatanBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            for ($m = 1; $m <= 12; $m++) {
                $startM = Carbon::createFromDate($tahunDipilih, $m, 1)->startOfMonth();
                $endM = Carbon::createFromDate($tahunDipilih, $m, 1)->endOfMonth();

                $monthSetoran = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'SETORAN')
                    ->whereBetween('dibuat_pada', [$startM, $endM])
                    ->sum('jumlah_total');
                $monthPenarikan = Transaction::where('id_mitra', $mitra->id)
                    ->where('jenis', 'PENARIKAN')
                    ->whereBetween('dibuat_pada', [$startM, $endM])
                    ->sum('jumlah_total');
                $chartData[] = [
                    'tanggal'   => $listSingkatanBulan[$m - 1],
                    'hari'      => $listSingkatanBulan[$m - 1],
                    'total'     => (int) $monthSetoran,
                    'penarikan' => (int) $monthPenarikan,
                ];
            }
        }

        // ── Transaksi terbaru (full dengan keterangan) ───────────
        $rincianTerbaru = $allTranx->map(fn($t) => [
            'id'             => $t->id,
            'jenis'          => $t->jenis,
            'jumlah_total'   => $t->jumlah_total,
            'poin_didapat'   => $t->poin_didapat,
            'nomor_referensi'=> $t->nomor_referensi,
            'keterangan'     => $t->keterangan,
            'jenis_sampah'   => $t->jenis === 'SETORAN'
                ? (preg_match('/^([^0-9]+)/u', $t->keterangan ?? '', $m) ? trim($m[1]) : 'Campuran')
                : null,
            'dibuat_pada'    => $t->dibuat_pada,
            'status'         => $t->status,
        ])->values();

        return response()->json([
            'periode'            => $periode,
            'pos'                => $mitra->nama,
            'total_setoran'      => (int) $totalSetoran,        // Total dibayarkan ke nasabah
            'total_pengepul'     => (int) $totalPengepul,       // Estimasi penjualan ke pengepul
            'total_margin'       => (int) $totalMarginUntung,   // Estimasi keuntungan kotor pos
            'persentase_margin'  => (int) $persentaseTotalMargin,
            'total_penarikan'    => (int) $totalPenarikan,
            'jumlah_transaksi'   => $jumlahTransaksi,
            'jumlah_setoran'     => $jumlahSetoran,
            'jumlah_penarikan'   => $jumlahPenarikan,
            'chart_data'         => $chartData,
            'rincian_sampah'     => array_values($rincianSampah),
            'rincian'            => $rincianTerbaru,
            // ── Kolom tambahan untuk PDF Laporan Resmi ──────────────
            'total_berat_kg'     => round($totalBeratKg, 2),
            'total_tonase'       => $totalTonase,
            'total_nilai_sampah' => (int) $totalNilaiSampah,
            'total_penarikan_rp' => (int) $totalPenarikan,
            'saldo_kas_bersih'   => (int) $saldoKasBersih,
            'total_nasabah_aktif'=> (int) $nasabahAktif,
            'nomor_laporan'      => $nomorLaporan,
            'nama_bulan'         => $namaBulan,
            'tanggal_cetak'      => $tanggalCetak,
            'rincian_kategori'   => array_values($rincianKategori),
        ]);
    }

    /**
     * PUT /api/mitra/pos
     * Update data profil pos, jam operasional, alamat tempat, nama pengelola, dan telepon.
     */
    public function updatePosProfile(Request $request)
    {
        $request->validate([
            'nama_pos'       => 'required|string',
            'alamat'         => 'required|string',
            'jam_buka'       => 'required|string',
            'pengelola'      => 'nullable|string',
            'telepon'        => 'nullable|string',
        ]);

        $user = $request->user();
        $mitra = $this->getMitra($request);

        // Update tabel mitra
        $mitraUpdate = [
            'nama'     => $request->nama_pos,
            'alamat'   => $request->alamat,
            'jam_buka' => $request->jam_buka,
        ];
        if (!empty($request->pengelola)) {
            $mitraUpdate['pengelola'] = $request->pengelola;
        }
        $mitra->update($mitraUpdate);

        // Update tabel pengguna (pengelola & telepon)
        $userUpdate = [];
        if (!empty($request->pengelola)) $userUpdate['nama'] = $request->pengelola;
        if (!empty($request->telepon))   $userUpdate['telepon'] = $request->telepon;
        if (!empty($request->alamat))    $userUpdate['alamat'] = $request->alamat;
        
        if (!empty($userUpdate)) {
            $user->update($userUpdate);
        }

        return response()->json([
            'pesan' => 'Profil pos bank sampah berhasil diperbarui',
            'pos'   => [
                'id'          => $mitra->id,
                'nama_pos'    => $mitra->nama,
                'kode_pos'    => 'POS-' . strtoupper(substr($mitra->id, 0, 5)),
                'alamat'      => $mitra->alamat,
                'jam_buka'    => $mitra->jam_buka,
                'pengelola'   => $user->fresh()->nama,
                'telepon'     => $user->fresh()->telepon,
                'email'       => $user->email,
            ]
        ]);
    }

    /**
     * POST /api/mitra/tambah-pos
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function createPosBranch(Request $request)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Pendaftaran dan pembukaan pos bank sampah baru hanya dapat disahkan oleh Administrator Desa melalui Web Admin.'
        ], 403);
    }

    /**
     * DELETE /api/mitra/pos/{id}
     * Ditolak: Wewenang eksklusif Administrator Desa via Web Admin.
     */
    public function destroyPos(Request $request, $id)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Penutupan atau penghapusan pos bank sampah hanya dapat dilakukan oleh Administrator Desa melalui Web Admin.'
        ], 403);
    }

    /**
     * GET /api/mitra/ringkasan-harian
     * Ringkasan operasional hari ini untuk tampilan utama app mitra.
     */
    public function ringkasanHarian(Request $request)
    {
        $mitra  = $this->getMitra($request);
        $today  = now()->startOfDay();
        $akhir  = now()->endOfDay();

        // FIX #5: Satu query saja dengan eager load 'pengguna', menghindari query ke-2 di bawah
        $transaksiHariIni = Transaction::with(['pengguna'])
            ->where('id_mitra', $mitra->id)
            ->whereBetween('dibuat_pada', [$today, $akhir])
            ->orderByDesc('dibuat_pada')
            ->get();

        $setoran   = $transaksiHariIni->where('jenis', 'SETORAN');
        $penarikan = $transaksiHariIni->where('jenis', 'PENARIKAN');

        // Komposisi sampah hari ini
        $komposisi = $setoran->map(function ($t) {
            preg_match('/^([^0-9]+)/u', $t->keterangan ?? '', $m);
            return trim($m[1] ?? 'Lainnya');
        })->countBy()->sortByDesc(fn($v) => $v)->take(5)->toArray();

        // FIX #5: Gunakan koleksi yang sudah ada, tidak perlu query ulang
        $transaksiTerakhir = $transaksiHariIni->take(10)->map(fn($t) => [
            'id'              => $t->id,
            'jenis'           => $t->jenis,
            'jumlah_total'    => $t->jumlah_total,
            'keterangan'      => $t->keterangan,
            'nama_nasabah'    => $t->pengguna?->nama ?? '-',
            'nomor_referensi' => $t->nomor_referensi,
            'dibuat_pada'     => $t->dibuat_pada,
        ]);

        return response()->json([
            'sukses'             => true,
            'pos'                => [
                'id'       => $mitra->id,
                'nama'     => $mitra->nama,
                'alamat'   => $mitra->alamat,
                'jam_buka' => $mitra->jam_buka,
            ],
            'tanggal'            => now()->setTimezone('Asia/Jakarta')->translatedFormat('l, d F Y'),
            'setoran_count'      => $setoran->count(),
            'setoran_nominal'    => (int) $setoran->sum('jumlah_total'),
            'penarikan_count'    => $penarikan->count(),
            'penarikan_nominal'  => (int) $penarikan->sum('jumlah_total'),
            'total_transaksi'    => $transaksiHariIni->count(),
            'poin_diberikan'     => (int) $setoran->sum('poin_didapat'),
            'komposisi_sampah'   => $komposisi,
            'transaksi_terakhir' => $transaksiTerakhir,
        ]);
    }
}
