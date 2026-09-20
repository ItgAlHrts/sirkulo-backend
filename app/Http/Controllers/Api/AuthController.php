<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Nasabah;
use App\Models\PetugasMitra;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class AuthController extends Controller
{
    // ── Daftar Nasabah Baru ──────────────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'nama'        => 'required|string',
            'email'       => 'required|email|unique:nasabah,email',
            'kata_sandi'  => 'required|min:6',
            'telepon'     => 'required|string',
        ]);

        $nasabah = Nasabah::create([
            'nama'       => $request->nama,
            'email'      => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'telepon'    => $request->telepon,
            'peran'      => 'NASABAH',
            'saldo'      => 0,
            'poin'       => 0,
            'kode_user'  => Nasabah::generateKodeUser(),
        ]);

        return response()->json([
            'pesan'     => 'Registrasi berhasil',
            'pengguna'  => ['id' => $nasabah->id, 'email' => $nasabah->email],
        ], 201);
    }

    // ── Masuk (Hanya untuk Nasabah & Petugas Mitra) ──────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'      => 'required|email',
            'kata_sandi' => 'required',
        ], [
            'email.required'      => 'Email wajib diisi.',
            'email.email'         => 'Format email tidak valid. Masukkan alamat email yang benar.',
            'kata_sandi.required' => 'Kata sandi wajib diisi.',
        ]);

        $email = trim($request->email);

        // Hapus akun dummy mitrasirkulo@gmail.com yang sempat dibuat otomatis oleh bug lama
        try {
            PetugasMitra::where('email', 'mitrasirkulo@gmail.com')->delete();
        } catch (\Throwable $e) {}

        // 1. Cek di tabel petugas_mitra (HANYA berdasarkan email)
        $pengguna = PetugasMitra::where('email', $email)->first();

        // 2. Jika bukan petugas mitra, cek di tabel nasabah (HANYA berdasarkan email)
        if (!$pengguna) {
            $pengguna = Nasabah::where('email', $email)->first();
        }

        // 3. Verifikasi pengguna dan kata sandi
        if (!$pengguna || !Hash::check($request->kata_sandi, $pengguna->kata_sandi)) {
            return response()->json([
                'galat' => 'Email atau kata sandi tidak sesuai.'
            ], 401);
        }

        $token = $pengguna->createToken('token_auth')->plainTextToken;

        $posData = null;
        if ($pengguna->peran === 'MITRA') {
            // Bersihkan pos duplikat tanpa transaksi yang sempat terbuat otomatis
            try {
                Partner::where(function ($q) {
                    $q->where('nama', 'like', 'Pos % - Mitra SIRKULO%')
                      ->orWhere('nama', 'like', 'Pos % - Bank Sampah Unit%');
                })->get()->each(function ($p) {
                    if (\App\Models\Transaction::where('id_mitra', $p->id)->count() === 0) {
                        $p->delete();
                    }
                });
            } catch (\Throwable $e) {}

            // Cari pos yang ditugaskan khusus ke akun petugas ini oleh Administrator
            $partner = Partner::where('id_pengguna', $pengguna->id)->first();

            // Jika petugas belum ditugaskan ke pos manapun oleh Admin, otomatis tautkan ke pos resmi yang tersedia
            if (!$partner) {
                $partner = Partner::whereNull('id_pengguna')->first() ?? Partner::first();
                if ($partner) {
                    $partner->update(['id_pengguna' => $pengguna->id]);
                }
            }

            if (!$partner) {
                return response()->json([
                    'galat' => 'Belum ada unit pos bank sampah yang terdaftar di sistem. Silakan hubungi Administrator Desa.'
                ], 403);
            }

            $posData = [
                'id'       => $partner->id,
                'nama_pos' => $partner->nama,
                'kode_pos' => $partner->kode_pos,
                'alamat'   => $partner->alamat,
                'jam_buka' => $partner->jam_buka,
            ];
        }

        $response = [
            'pesan'  => 'Masuk berhasil',
            'token'  => $token,
            'peran'  => $pengguna->peran,
            'nama'   => $pengguna->nama,
        ];
        if ($posData) {
            $response['pos'] = $posData;
        }

        return response()->json($response);
    }

    // ── Lupa Kata Sandi – Buat OTP (Nasabah) ───────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $pengguna = Nasabah::where('email', $request->email)->first();
        if (!$pengguna) {
            return response()->json(['galat' => 'Email tidak terdaftar'], 404);
        }

        $otp        = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        $kadaluarsa = Carbon::now()->addMinutes(15);

        $pengguna->update(['otp_reset' => $otp, 'kadaluarsa_otp' => $kadaluarsa]);

        \Log::info("🔑 OTP Reset Kata Sandi untuk {$pengguna->email}: {$otp}");

        return response()->json([
            'pesan'   => 'OTP berhasil dikirim ke email (Mode Pengembangan: lihat log backend).',
            'otp_dev' => $otp,
        ]);
    }

    // ── Reset Kata Sandi (Nasabah) ─────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'           => 'required|email',
            'otp'             => 'required',
            'kata_sandi_baru' => 'required|min:6',
        ]);

        $pengguna = Nasabah::where('email', $request->email)->first();
        if (!$pengguna)                                         return response()->json(['galat' => 'Email tidak ditemukan'], 404);
        if ($pengguna->otp_reset !== $request->otp)            return response()->json(['galat' => 'OTP tidak valid'], 400);
        if (Carbon::now()->isAfter($pengguna->kadaluarsa_otp)) return response()->json(['galat' => 'OTP sudah kadaluarsa'], 400);

        $pengguna->update([
            'kata_sandi'     => Hash::make($request->kata_sandi_baru),
            'otp_reset'      => null,
            'kadaluarsa_otp' => null,
        ]);

        return response()->json(['pesan' => 'Kata sandi berhasil diubah. Silakan masuk.']);
    }

    // ── Keluar ────────────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['pesan' => 'Keluar berhasil']);
    }
}
