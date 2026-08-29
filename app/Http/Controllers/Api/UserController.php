<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ── Ambil Profil ───────────────────────────────────────────────
    public function profile(Request $request)
    {
        $pengguna = $request->user();
        $poinDihitung = (int) floor(($pengguna->saldo ?? 0) / 100);
        return response()->json([
            'id'        => $pengguna->id,
            'kode_user' => $pengguna->kode_user,
            'nama'      => $pengguna->nama,
            'email'     => $pengguna->email,
            'telepon'   => $pengguna->telepon,
            'alamat'    => $pengguna->alamat,
            'saldo'     => $pengguna->saldo,
            'poin'      => $poinDihitung, // 1 Poin = Rp 100
            'peran'     => $pengguna->peran,
            'foto_url'  => $pengguna->foto_url,
        ]);
    }

    // ── Perbarui Profil ────────────────────────────────────────────
    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama'    => 'required|string',
            'telepon' => 'required|string',
            'alamat'  => 'nullable|string',
        ]);

        $pengguna = $request->user();
        $pengguna->update($request->only('nama', 'telepon', 'alamat'));

        return response()->json([
            'id'        => $pengguna->id,
            'kode_user' => $pengguna->kode_user,
            'nama'      => $pengguna->nama,
            'email'     => $pengguna->email,
            'telepon'   => $pengguna->telepon,
            'alamat'    => $pengguna->alamat,
            'saldo'     => $pengguna->saldo,
            'poin'      => $pengguna->poin,
            'peran'     => $pengguna->peran,
            'foto_url'  => $pengguna->foto_url,
        ]);
    }

    // ── Unggah Foto Profil ─────────────────────────────────────────
    public function updatePhoto(Request $request)
    {
        $file = $request->file('foto') ?? $request->file('photo');

        if (!$file || !$file->isValid()) {
            return response()->json(['galat' => 'File foto tidak valid atau tidak ditemukan'], 422);
        }

        $pengguna = $request->user();
        $jalur = $file->store('foto_profil', 'public');
        $urlFoto = $request->getSchemeAndHttpHost() . '/storage/' . $jalur;
        $pengguna->update(['foto_url' => $urlFoto]);

        return response()->json([
            'id'        => $pengguna->id,
            'kode_user' => $pengguna->kode_user,
            'nama'      => $pengguna->nama,
            'email'     => $pengguna->email,
            'telepon'   => $pengguna->telepon,
            'alamat'    => $pengguna->alamat,
            'saldo'     => $pengguna->saldo,
            'poin'      => $pengguna->poin,
            'peran'     => $pengguna->peran,
            'foto_url'  => $pengguna->foto_url,
        ]);
    }

    // ── Ubah Kata Sandi ────────────────────────────────────────────
    public function changePassword(Request $request)
    {
        $request->validate([
            'kata_sandi_lama' => 'required',
            'kata_sandi_baru' => 'required|min:6',
        ]);

        $pengguna = $request->user();
        if (!Hash::check($request->kata_sandi_lama, $pengguna->kata_sandi)) {
            return response()->json(['galat' => 'Kata sandi lama tidak sesuai'], 401);
        }

        $pengguna->update(['kata_sandi' => Hash::make($request->kata_sandi_baru)]);
        return response()->json(['pesan' => 'Kata sandi berhasil diubah']);
    }
}
