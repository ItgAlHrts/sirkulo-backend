<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transaksi = Transaction::with('mitra')
            ->where('id_pengguna', $request->user()->id)
            ->orderBy('dibuat_pada', 'desc')
            ->get();
        return response()->json($transaksi);
    }

    public function show(Request $request, $id)
    {
        $transaksi = Transaction::with('mitra')
            ->where('id', $id)
            ->where('id_pengguna', $request->user()->id)
            ->first();

        if (!$transaksi) return response()->json(['galat' => 'Transaksi tidak ditemukan'], 404);
        return response()->json($transaksi);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_pengguna'    => 'required|exists:pengguna,id',
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
                    'poin_didapat'    => ($request->jenis === 'SETORAN') ? ($request->poin_didapat ?? $request->jumlah_total) : (-$request->jumlah_total),
                    'nomor_referensi' => $nomor_referensi,
                ]);

                $pengguna = User::find($request->id_pengguna);
                if ($request->jenis === 'SETORAN') {
                    $pengguna->increment('saldo', $request->jumlah_total);
                } elseif ($request->jenis === 'PENARIKAN') {
                    $pengguna->decrement('saldo', $request->jumlah_total);
                }
                // Poin selalu sama dengan saldo rupiah (1 Poin = Rp 1)
                $pengguna->update(['poin' => $pengguna->saldo]);

                return $trx;
            });

            return response()->json(['pesan' => 'Transaksi berhasil', 'transaksi' => $transaksi], 201);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal memproses transaksi'], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $trx = Transaction::find($id);
        if (!$trx) return response()->json(['galat' => 'Transaksi tidak ditemukan'], 404);

        DB::transaction(function () use ($trx) {
            $pengguna = User::find($trx->id_pengguna);
            if ($trx->jenis === 'SETORAN') {
                $pengguna->decrement('saldo', $trx->jumlah_total);
            } elseif ($trx->jenis === 'PENARIKAN') {
                $pengguna->increment('saldo', $trx->jumlah_total);
            }
            // Kembalikan & sinkronkan poin persis sama dengan saldo
            $pengguna->update(['poin' => $pengguna->saldo]);
            $trx->delete();
        });

        return response()->json(['pesan' => 'Transaksi berhasil dihapus dan saldo dikembalikan']);
    }
}
