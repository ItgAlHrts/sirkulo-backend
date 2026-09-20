<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = Transaction::with(['mitra', 'pengguna'])
            ->where('id_pengguna', $request->user()->id)
            ->orderBy('dibuat_pada', 'desc')
            ->get();
        return response()->json($transaksi);
    }

    public function show(Request $request, $id)
    {
        $transaksi = Transaction::with(['mitra', 'pengguna'])
            ->where('id', $id)
            ->where('id_pengguna', $request->user()->id)
            ->first();

        if (!$transaksi) return response()->json(['galat' => 'Transaksi tidak ditemukan'], 404);
        return response()->json($transaksi);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna'    => 'required|exists:nasabah,id',
            'id_mitra'       => 'required|exists:mitra,id',
            'jenis'          => 'required|in:SETORAN,PENARIKAN',
            'jumlah_total'   => 'required|integer|min:1',
            'poin_didapat'   => 'nullable|integer',
        ]);

        $nomor_referensi = 'TRX-' . time() . rand(100, 999);

        try {
            $transaksi = DB::transaction(function () use ($request, $nomor_referensi) {
                $trx = Transaction::create([
                    'id_pengguna'     => $request->id_pengguna,
                    'id_mitra'        => $request->id_mitra,
                    'jenis'           => $request->jenis,
                    'status'          => 'SELESAI',
                    'jumlah_total'    => $request->jumlah_total,
                    'poin_didapat'    => ($request->jenis === 'SETORAN') ? ($request->poin_didapat ?? (int) floor($request->jumlah_total / 100)) : -(int) floor($request->jumlah_total / 100),
                    'nomor_referensi' => $nomor_referensi,
                ]);

                $pengguna = Nasabah::find($request->id_pengguna);
                if ($request->jenis === 'SETORAN') {
                    $pengguna->increment('saldo', $request->jumlah_total);
                } elseif ($request->jenis === 'PENARIKAN') {
                    $pengguna->decrement('saldo', $request->jumlah_total);
                }
                // Konversi Poin: 1 Poin = Rp 100
                $pengguna->update(['poin' => (int) floor($pengguna->saldo / 100)]);

                \App\Services\AuditLogger::log(
                    $trx->jenis === 'SETORAN' ? 'TRANSAKSI_SETOR_SAMPAH' : 'TRANSAKSI_PENARIKAN_SALDO',
                    "Transaksi {$trx->jenis} {$trx->nomor_referensi} senilai Rp " . number_format($trx->jumlah_total, 0, ',', '.') . " oleh nasabah '{$pengguna->nama}'.",
                    'TRANSAKSI',
                    'TRANSACTION',
                    'SUKSES',
                    [
                        'nomor_referensi' => $nomor_referensi,
                        'jenis'           => $trx->jenis,
                        'nominal'         => $trx->jumlah_total,
                        'poin'            => $trx->poin_didapat,
                        'id_nasabah'      => $pengguna->id,
                        'nama_nasabah'    => $pengguna->nama,
                        'id_mitra'        => $request->id_mitra,
                    ],
                    $request->user()?->id,
                    $request->user()?->nama,
                    $request->user()?->peran
                );

                return $trx;
            });

            return response()->json(['pesan' => 'Transaksi berhasil', 'transaksi' => $transaksi], 201);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal memproses transaksi'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        return response()->json([
            'galat' => 'Akses Ditolak: Seluruh transaksi keuangan bersifat mutlak dan tidak dapat dihapus untuk menjaga keabsahan pembukuan dan audit kas desa.'
        ], 403);
    }
}
