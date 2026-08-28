<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Partner;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\TrashCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MitraController extends Controller
{
    /**
     * Dapatkan data pos mitra dari user yang login atau berdasarkan X-Pos-Id.
     */
    private function getMitra(Request $request)
    {
        $user = $request->user();
        $posId = $request->header('X-Pos-Id') ?: ($request->input('id_pos') ?: $request->query('id_pos'));

        if ($posId) {
            $selectedPartner = Partner::find($posId);
            if ($selectedPartner) {
                return $selectedPartner;
            }
        }

        $mitra = Partner::where('id_pengguna', $user->id)->first();
        if (!$mitra) {
            $mitra = Partner::first();
        }
        if (!$mitra) {
            // Jika belum ada pos mitra, buatkan default untuk akun mitra
            $mitra = Partner::create([
                'id_pengguna' => $user->id,
                'nama'        => 'Pos 1 - ' . ($user->nama ?? 'Bank Sampah'),
                'alamat'      => $user->alamat ?? 'Jl. Pos Bank Sampah No. 1',
                'lintang'     => -6.9932,
                'bujur'       => 110.4203,
                'jam_buka'    => 'Senin - Sabtu, 08:00 - 16:00 WIB',
            ]);
        }
        return $mitra;
    }

    /**
     * GET /api/mitra/pos-list
     * Ambil seluruh daftar pos bank sampah yang tersedia untuk dipilih mitra.
     */
    public function listPos(Request $request)
    {
        // Auto-drop unique constraint pada id_pengguna agar satu Mitra bisa punya banyak Pos
        try {
            if (\DB::getDriverName() === 'mysql' || \DB::getDriverName() === 'mariadb') {
                $hasUnique = \DB::select(
                    "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
                     WHERE TABLE_SCHEMA = DATABASE()
                     AND TABLE_NAME = 'mitra'
                     AND INDEX_NAME = 'mitra_id_pengguna_unique'"
                );
                if (!empty($hasUnique) && $hasUnique[0]->cnt > 0) {
                    \DB::statement('ALTER TABLE mitra DROP INDEX mitra_id_pengguna_unique');
                }
            }
        } catch (\Exception $e) {
            // Ignore — constraint mungkin sudah dihapus
        }

        $allPos = Partner::with('pengguna')->get();
        return response()->json($allPos);
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
        $totalNasabah = User::where('peran', 'NASABAH')->count();

        // Riwayat 5 transaksi terakhir
        $transaksiTerbaru = Transaction::with('pengguna')
            ->where('id_mitra', $mitra->id)
            ->orderBy('dibuat_pada', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'pos' => [
                'id'          => $mitra->id,
                'nama_pos'    => $mitra->nama,
                'kode_pos'    => $mitra->kode_pos,
                'alamat'      => $mitra->alamat,
                'jam_buka'    => $mitra->jam_buka,
                'pengelola'   => $request->user()->nama,
                'telepon'     => $request->user()->telepon,
                'email'       => $request->user()->email,
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
        $query = User::where('peran', 'NASABAH');

        if ($request->has('q') && !empty($request->q)) {
            $keyword = '%' . $request->q . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'like', $keyword)
                  ->orWhere('telepon', 'like', $keyword)
                  ->orWhere('email', 'like', $keyword)
                  ->orWhere('id', 'like', $keyword);
            });
        }

        $nasabah = $query->orderBy('nama', 'asc')->get();
        if ($request->has('q') && !empty($request->q)) {
            $q = trim($request->q);
            $matchedByKode = User::where('peran', 'NASABAH')->get()->filter(function ($u) use ($q) {
                return stripos($u->kode_user, $q) !== false;
            });
            $nasabah = $nasabah->merge($matchedByKode)->unique('id');
        }
        return response()->json($nasabah->values());
    }

    /**
     * GET /api/mitra/nasabah/{id}
     * Ambil data lengkap nasabah berdasarkan ID / Kode User (SRKLxxx) / No. HP (untuk hasil scan barcode).
     */
    public function detailNasabah($id)
    {
        $nasabah = User::where('peran', 'NASABAH')
            ->where(function ($q) use ($id) {
                $q->where('id', $id)->orWhere('telepon', $id);
            })
            ->first();

        if (!$nasabah) {
            $nasabah = User::where('peran', 'NASABAH')->get()->first(function ($u) use ($id) {
                return strcasecmp($u->kode_user, $id) === 0 || strcasecmp($u->id, $id) === 0;
            });
        }

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
     * Registrasi Nasabah Baru oleh Mitra.
     */
    public function registrasiNasabah(Request $request)
    {
        $request->validate([
            'nama'     => 'required|string|max:100',
            'telepon'  => 'required|string|unique:pengguna,telepon',
            'email'    => 'nullable|email|unique:pengguna,email',
            'alamat'   => 'nullable|string',
        ]);

        $email = $request->email ?: 'nasabah_' . time() . '@sirkulo.id';
        $kataSandiDefault = 'password123';

        $nasabah = User::create([
            'nama'       => $request->nama,
            'email'      => $email,
            'kata_sandi' => Hash::make($kataSandiDefault),
            'telepon'    => $request->telepon,
            'alamat'     => $request->alamat ?? '-',
            'saldo'      => 0,
            'poin'       => 0,
            'peran'      => 'NASABAH',
        ]);

        // Buat notifikasi selamat datang
        Notification::create([
            'id_pengguna' => $nasabah->id,
            'judul'       => 'Selamat Datang di SIRKULO!',
            'deskripsi'   => 'Akun nasabah Anda telah didaftarkan oleh Mitra ' . $request->user()->nama,
            'jenis'       => 'INFO',
            'dibuat_pada' => now(),
        ]);

        return response()->json([
            'pesan'   => 'Nasabah berhasil didaftarkan',
            'nasabah' => $nasabah,
        ], 201);
    }

    /**
     * DELETE /api/mitra/nasabah/{id}
     * Hapus akun Nasabah secara permanen oleh Mitra.
     */
    public function hapusNasabah(Request $request, $id)
    {
        try {
            $nasabah = User::where('id', $id)->where('peran', 'NASABAH')->first();
            if (!$nasabah) {
                return response()->json(['galat' => 'Data nasabah tidak ditemukan'], 404);
            }

            $nama = $nasabah->nama;
            // Hapus data terkait (tokens, notifikasi, transaksi)
            DB::table('personal_access_tokens')->where('tokenable_id', $nasabah->id)->delete();
            Notification::where('id_pengguna', $nasabah->id)->delete();
            Transaction::where('id_pengguna', $nasabah->id)->delete();
            $nasabah->delete();

            return response()->json([
                'pesan' => "Nasabah '{$nama}' berhasil dihapus permanen"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'galat' => 'Gagal menghapus nasabah: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/mitra/setoran
     * Proses Setoran Sampah oleh Mitra (Bisa Masuk Saldo atau Bayar Tunai Cash).
     */
    public function prosesSetoran(Request $request)
    {
        $request->validate([
            'id_pengguna'       => 'required|exists:pengguna,id',
            'jenis_sampah'      => 'required|string',
            'berat_kg'          => 'required|numeric|min:0.1',
            'harga_per_kg'      => 'required|integer|min:1',
            'metode_pembayaran' => 'nullable|string|in:SALDO,TUNAI_CASH',
            'catatan'           => 'nullable|string',
        ]);

        $mitra = $this->getMitra($request);
        $totalNominal = (int) round($request->berat_kg * $request->harga_per_kg);
        $metode = $request->metode_pembayaran ?? 'SALDO';
        // Poin selalu sama dengan nominal saldo rupiah
        $poinDidapat = ($metode === 'SALDO') ? $totalNominal : 0;
        $noReferensi = 'SET-' . strtoupper(Str::random(8));

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('transaksi') && !\Illuminate\Support\Facades\Schema::hasColumn('transaksi', 'keterangan')) {
                \Illuminate\Support\Facades\Schema::table('transaksi', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('keterangan')->nullable()->after('nomor_referensi');
                });
            }
        } catch (\Exception $ex) {}

        $transaksi = DB::transaction(function () use ($request, $mitra, $totalNominal, $poinDidapat, $noReferensi, $metode) {
            // 1. Catat Transaksi SETORAN
            $trx = Transaction::create([
                'id_pengguna'     => $request->id_pengguna,
                'id_mitra'        => $mitra->id,
                'jenis'           => 'SETORAN',
                'status'          => 'SELESAI',
                'jumlah_total'    => $totalNominal,
                'poin_didapat'    => $poinDidapat,
                'nomor_referensi' => $noReferensi,
                'keterangan'      => $request->jenis_sampah . ' ' . $request->berat_kg . ' kg' . ($metode === 'TUNAI_CASH' ? ' (Setor Tunai)' : ''),
                'dibuat_pada'     => now(),
            ]);

            $nasabah = User::find($request->id_pengguna);
            $saldoSebelumnya = $nasabah->saldo;

            // Jika metode SALDO -> tabungan digital & poin bertambah sama persis
            if ($metode === 'SALDO') {
                $nasabah->increment('saldo', $totalNominal);
                $nasabah->update(['poin' => $nasabah->saldo]);
            } else if ($metode === 'TUNAI_CASH') {
                // 2. Jika Bayar Tunai Cash: Catat otomatis transaksi PENARIKAN sekalian (uang diserahkan tunai di tempat)
                $noRefTarik = 'WD-' . strtoupper(Str::random(8));
                Transaction::create([
                    'id_pengguna'     => $request->id_pengguna,
                    'id_mitra'        => $mitra->id,
                    'jenis'           => 'PENARIKAN',
                    'status'          => 'SELESAI',
                    'jumlah_total'    => $totalNominal,
                    'poin_didapat'    => 0,
                    'nomor_referensi' => $noRefTarik,
                    'keterangan'      => 'Tarik Tunai Langsung (' . $request->jenis_sampah . ' ' . $request->berat_kg . ' kg)',
                    'dibuat_pada'     => now()->addSecond(), // Beri jeda 1 detik agar urutan riwayat konsisten (Setoran dulu baru Penarikan)
                ]);
            }

            // Buat notifikasi untuk nasabah
            if ($metode === 'TUNAI_CASH') {
                Notification::create([
                    'id_pengguna' => $nasabah->id,
                    'judul'       => 'Setoran & Penarikan Tunai Selesai',
                    'deskripsi'   => "Setoran {$request->berat_kg} kg {$request->jenis_sampah} senilai Rp " . number_format($totalNominal, 0, ',', '.') . " berhasil dan uang tunai telah diserahkan di loket Pos {$mitra->nama}.",
                    'jenis'       => 'SETORAN',
                    'dibuat_pada' => now(),
                ]);
            } else {
                Notification::create([
                    'id_pengguna' => $nasabah->id,
                    'judul'       => 'Setoran Sampah Berhasil',
                    'deskripsi'   => "Setoran {$request->berat_kg} kg {$request->jenis_sampah} senilai Rp " . number_format($totalNominal, 0, ',', '.') . " (Masuk Saldo) berhasil (+ " . number_format($poinDidapat, 0, ',', '.') . " Poin).",
                    'jenis'       => 'SETORAN',
                    'dibuat_pada' => now(),
                ]);
            }

            return [
                'transaksi'        => $trx,
                'nasabah'          => $nasabah->fresh(),
                'saldo_sebelumnya' => $saldoSebelumnya,
                'saldo_baru'       => $nasabah->fresh()->saldo,
            ];
        });

        return response()->json([
            'pesan'             => 'Setoran berhasil dicatat',
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
     * Tarik Tunai Cash Saldo Nasabah oleh Mitra di Tempat.
     */
    public function cairkanSaldoNasabah(Request $request)
    {
        $request->validate([
            'id_pengguna' => 'required|exists:pengguna,id',
            'nominal'     => 'required|integer|min:1000',
            'catatan'     => 'nullable|string',
        ]);

        $nasabah = User::find($request->id_pengguna);
        if ($nasabah->saldo < $request->nominal) {
            return response()->json(['galat' => 'Saldo nasabah tidak mencukupi (Saldo: Rp ' . number_format($nasabah->saldo, 0, ',', '.') . ')'], 400);
        }

        $mitra = $this->getMitra($request);
        $noReferensi = 'TARIK-' . strtoupper(Str::random(8));

        $res = DB::transaction(function () use ($request, $nasabah, $mitra, $noReferensi) {
            $saldoSebelumnya = $nasabah->saldo;
            $poinSebelumnya = $nasabah->poin;
            $nasabah->decrement('saldo', $request->nominal);

            // Pengurangan poin: Poin selalu sama dengan saldo rupiah (1 Poin = Rp 1)
            $poinDipotong = $request->nominal;
            $nasabah->update(['poin' => $nasabah->saldo]);

            $trx = Transaction::create([
                'id_pengguna'     => $nasabah->id,
                'id_mitra'        => $mitra->id,
                'jenis'           => 'PENARIKAN',
                'status'          => 'SELESAI',
                'jumlah_total'    => $request->nominal,
                'poin_didapat'    => -$poinDipotong,
                'nomor_referensi' => $noReferensi,
                'dibuat_pada'     => now(),
            ]);

            Notification::create([
                'id_pengguna' => $nasabah->id,
                'judul'       => 'Tarik Tunai Berhasil',
                'deskripsi'   => 'Penarikan tunai sebesar Rp ' . number_format($request->nominal, 0, ',', '.') . ' di ' . $mitra->nama . " telah selesai (-" . number_format($poinDipotong, 0, ',', '.') . " Poin).",
                'jenis'       => 'PENARIKAN',
                'dibuat_pada' => now(),
            ]);

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
        ], 201);
    }

    /**
     * POST /api/mitra/penarikan
     * Pengajuan Penarikan Saldo Pos oleh Mitra.
     */
    public function ajukanPenarikan(Request $request)
    {
        $request->validate([
            'nominal'     => 'required|integer|min:10000',
            'metode'      => 'required|string',
            'no_rekening' => 'required|string',
            'keterangan'  => 'nullable|string',
        ]);

        $user = $request->user();
        if ($user->saldo < $request->nominal) {
            return response()->json(['galat' => 'Saldo pos tidak mencukupi'], 400);
        }

        $mitra = $this->getMitra($request);
        $noReferensi = 'TRF-' . strtoupper(Str::random(8));

        $trx = DB::transaction(function () use ($request, $user, $mitra, $noReferensi) {
            $user->decrement('saldo', $request->nominal);

            return Transaction::create([
                'id_pengguna'     => $user->id,
                'id_mitra'        => $mitra->id,
                'jenis'           => 'PENARIKAN',
                'status'          => 'MENUNGGU',
                'jumlah_total'    => $request->nominal,
                'poin_didapat'    => 0,
                'nomor_referensi' => $noReferensi,
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
        $query = Transaction::with('pengguna')->where('id_mitra', $mitra->id);

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

        $startDate = match ($periode) {
            'hari_ini'   => now()->startOfDay(),
            'minggu_ini' => now()->startOfWeek(),
            'tahun_ini'  => now()->startOfYear(),
            default      => now()->startOfMonth(),
        };

        // Ambil semua transaksi dalam periode ini dari pos yang dipilih
        $allTranx = Transaction::where('id_mitra', $mitra->id)
            ->where('dibuat_pada', '>=', $startDate)
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        $setoran   = $allTranx->where('jenis', 'SETORAN');
        $penarikan = $allTranx->where('jenis', 'PENARIKAN');

        $totalSetoran   = $setoran->sum('jumlah_total');
        $totalPenarikan = $penarikan->sum('jumlah_total');
        $jumlahTransaksi = $allTranx->count();
        $jumlahSetoran   = $setoran->count();
        $jumlahPenarikan = $penarikan->count();

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
        arsort($rincianSampah); // Urutkan dari terbesar

        $persentaseTotalMargin = ($totalSetoran > 0)
            ? (int) round(($totalMarginUntung / $totalSetoran) * 100)
            : 0;

        // ── Grafik Batang (7 hari terakhir) ─────────────────────
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $daySetoran = Transaction::where('id_mitra', $mitra->id)
                ->where('jenis', 'SETORAN')
                ->whereDate('dibuat_pada', $date->toDateString())
                ->sum('jumlah_total');
            $dayPenarikan = Transaction::where('id_mitra', $mitra->id)
                ->where('jenis', 'PENARIKAN')
                ->whereDate('dibuat_pada', $date->toDateString())
                ->sum('jumlah_total');

            $chartData[] = [
                'tanggal'   => $date->format('d M'),
                'hari'      => $date->isoFormat('ddd'),
                'total'     => (int) $daySetoran,
                'penarikan' => (int) $dayPenarikan,
            ];
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
            'total_setoran'      => (int) $totalSetoran,       // Total dibayarkan ke nasabah
            'total_pengepul'     => (int) $totalPengepul,      // Estimasi penjualan ke pengepul
            'total_margin'       => (int) $totalMarginUntung,  // Estimasi keuntungan kotor pos
            'persentase_margin'  => (int) $persentaseTotalMargin,
            'total_penarikan'    => (int) $totalPenarikan,
            'jumlah_transaksi'   => $jumlahTransaksi,
            'jumlah_setoran'     => $jumlahSetoran,
            'jumlah_penarikan'   => $jumlahPenarikan,
            'chart_data'         => $chartData,
            'rincian_sampah'     => array_values($rincianSampah),
            'rincian'            => $rincianTerbaru,
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
        $mitra->update([
            'nama'     => $request->nama_pos,
            'alamat'   => $request->alamat,
            'jam_buka' => $request->jam_buka,
        ]);

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
     * Daftarkan Pos Cabang Bank Sampah baru. Pos langsung dimiliki oleh Mitra yang sedang login.
     */
    public function createPosBranch(Request $request)
    {
        try {
            $request->validate([
                'nama_pos'  => 'required|string',
                'alamat'    => 'required|string',
                'jam_buka'  => 'required|string',
                'pengelola' => 'nullable|string',
                'telepon'   => 'nullable|string',
            ]);

            $user = $request->user();

            // Cari user Mitra — bisa dari token atau default mitrasirkulo
            if (!$user) {
                $user = User::where('email', 'mitrasirkulo@gmail.com')->first();
            }
            if (!$user) {
                return response()->json(['galat' => 'Tidak ada sesi Mitra aktif'], 401);
            }

            // Auto-drop unique constraint pada id_pengguna di tabel mitra
            // agar satu akun Mitra bisa memiliki lebih dari satu Pos.
            try {
                $driver = \DB::getDriverName();
                if ($driver === 'mysql' || $driver === 'mariadb') {
                    // Cek apakah constraint masih ada
                    $hasUnique = \DB::select(
                        "SELECT COUNT(*) as cnt FROM information_schema.STATISTICS
                         WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = 'mitra'
                         AND INDEX_NAME = 'mitra_id_pengguna_unique'"
                    );
                    if (!empty($hasUnique) && $hasUnique[0]->cnt > 0) {
                        \DB::statement('ALTER TABLE mitra DROP INDEX mitra_id_pengguna_unique');
                    }
                } elseif ($driver === 'sqlite') {
                    // SQLite: hanya bisa recreate table — skip, sudah handle lewat migration
                }
            } catch (\Exception $ex) {
                // Ignore jika constraint sudah tidak ada
            }

            $newPartner = Partner::create([
                'id_pengguna' => $user->id,
                'nama'        => $request->nama_pos,
                'alamat'      => $request->alamat,
                'lintang'     => -6.9932,
                'bujur'       => 110.4203,
                'jam_buka'    => $request->jam_buka,
            ]);

            return response()->json([
                'pesan' => 'Pos Bank Sampah ' . $newPartner->nama . ' berhasil didaftarkan!',
                'pos'   => $newPartner
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'galat'  => 'Validasi gagal',
                'detail' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'galat'  => 'Gagal membuat pos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/mitra/pos/{id}
     * Hapus Pos Bank Sampah dengan aman.
     */
    public function destroyPos(Request $request, $id)
    {
        $partner = Partner::find($id);
        if (!$partner) {
            return response()->json(['galat' => 'Pos Bank Sampah tidak ditemukan'], 404);
        }

        $namaPos = $partner->nama;
        // Hapus transaksi terkait pos jika ada
        Transaction::where('id_mitra', $id)->delete();
        $partner->delete();

        return response()->json([
            'pesan' => "Pos Bank Sampah '{$namaPos}' berhasil dihapus permanen"
        ]);
    }
}
