<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Nasabah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminNasabahController extends Controller
{
    public function index(Request $request)
    {
        $query = Nasabah::orderByDesc('dibuat_pada');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($builder) use ($q) {
                $builder->where('nama', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('telepon', 'like', "%$q%")
                    ->orWhere('kode_user', 'like', "%$q%")
                    ->orWhere('id', 'like', "%$q%");
            });
        }

        $nasabah = $query->paginate(15)->withQueryString();
        return view('admin.nasabah.index', compact('nasabah'));
    }

    public function create()
    {
        return view('admin.nasabah.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'       => 'required|string|max:100',
            'email'      => 'required|email|unique:nasabah,email',
            'kata_sandi' => 'required|string|min:6',
            'telepon'    => 'required|string|max:20|unique:nasabah,telepon',
            'alamat'     => 'nullable|string|max:255',
            'saldo'      => 'nullable|numeric|min:0',
        ]);

        $saldo = (int) ($request->saldo ?? 0);
        $kodeUser = Nasabah::generateKodeUser();
        $finalAlamat = self::compileAlamat($request);

        $nasabah = Nasabah::create([
            'nama'       => $request->nama,
            'email'      => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'telepon'    => $request->telepon,
            'alamat'     => $finalAlamat,
            'saldo'      => $saldo,
            'poin'       => (int) floor($saldo / 100),
            'peran'      => 'NASABAH',
            'kode_user'  => $kodeUser,
        ]);

        \App\Services\AuditLogger::log(
            'TAMBAH_NASABAH',
            "Mendaftarkan akun nasabah baru '{$nasabah->nama}' ({$nasabah->kode_user}, Telp: {$nasabah->telepon}) dengan saldo awal Rp " . number_format($saldo, 0, ',', '.'),
            'NASABAH',
            'CREATE',
            'SUKSES',
            [
                'id'        => $nasabah->id,
                'kode_user' => $nasabah->kode_user,
                'nama'      => $nasabah->nama,
                'email'     => $nasabah->email,
                'telepon'   => $nasabah->telepon,
                'saldo'     => $saldo,
            ]
        );

        return redirect()->route('admin.nasabah.index')
            ->with('success', "Akun nasabah warga '{$nasabah->nama}' ({$nasabah->kode_user}) berhasil didaftarkan.");
    }

    public function show(string $id)
    {
        $nasabah  = Nasabah::findOrFail($id);
        $transaksi = Transaction::where('id_pengguna', $id)
            ->orderByDesc('dibuat_pada')
            ->limit(20)
            ->get();
        return view('admin.nasabah.show', compact('nasabah', 'transaksi'));
    }

    public function edit(string $id)
    {
        $nasabah = Nasabah::findOrFail($id);
        $alamatData = self::parseAlamat($nasabah->alamat);
        return view('admin.nasabah.edit', compact('nasabah', 'alamatData'));
    }

    public function update(Request $request, string $id)
    {
        $nasabah = Nasabah::findOrFail($id);

        $request->validate([
            'nama'    => 'required|string|max:100',
            'email'   => 'required|email|unique:nasabah,email,' . $id,
            'telepon' => 'nullable|string|max:20',
            'alamat'  => 'nullable|string|max:255',
        ]);

        $sebelum = [
            'nama'    => $nasabah->nama,
            'email'   => $nasabah->email,
            'telepon' => $nasabah->telepon,
            'alamat'  => $nasabah->alamat,
        ];

        $finalAlamat = self::compileAlamat($request);

        $nasabah->update([
            'nama'    => $request->nama,
            'email'   => $request->email,
            'telepon' => $request->telepon,
            'alamat'  => $finalAlamat,
        ]);

        $passwordDiubah = false;
        if ($request->filled('password_baru')) {
            $nasabah->update(['kata_sandi' => Hash::make($request->password_baru)]);
            $passwordDiubah = true;
        }

        $sesudah = [
            'nama'    => $nasabah->nama,
            'email'   => $nasabah->email,
            'telepon' => $nasabah->telepon,
            'alamat'  => $nasabah->alamat,
        ];

        if ($passwordDiubah) {
            $sebelum['kata_sandi'] = '•••••••• (Sandi Lama)';
            $sesudah['kata_sandi'] = '•••••••• (Sandi Baru Diubah)';
        }

        $perubahan = \App\Services\AuditLogger::formatPerubahan($sebelum, $sesudah);

        \App\Services\AuditLogger::log(
            'UPDATE_NASABAH',
            "Memperbarui informasi data akun nasabah '{$nasabah->nama}' ({$nasabah->kode_user})" . ($passwordDiubah ? ' beserta kata sandi baru.' : '.'),
            'NASABAH',
            'UPDATE',
            'SUKSES',
            [
                'id'              => $nasabah->id,
                'kode_user'       => $nasabah->kode_user,
                'data_sebelum'    => $sebelum,
                'data_sesudah'    => $sesudah,
                'perubahan'       => $perubahan,
                'password_diubah' => $passwordDiubah,
            ]
        );

        return redirect()->route('admin.nasabah.show', $id)
            ->with('success', 'Data nasabah berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $nasabah = Nasabah::findOrFail($id);
        $nama = $nasabah->nama;
        $kode = $nasabah->kode_user;
        $saldo = $nasabah->saldo;

        $nasabah->delete();

        \App\Services\AuditLogger::log(
            'HAPUS_NASABAH',
            "Menghapus akun nasabah '{$nama}' ({$kode}) dengan saldo terakhir Rp " . number_format($saldo, 0, ',', '.'),
            'NASABAH',
            'DELETE',
            'PERINGATAN',
            ['id' => $id, 'nama' => $nama, 'kode_user' => $kode, 'saldo_terakhir' => $saldo]
        );

        return redirect()->route('admin.nasabah.index')
            ->with('success', 'Akun nasabah berhasil dihapus.');
    }

    public function resetPassword(Request $request, string $id)
    {
        $nasabah = Nasabah::findOrFail($id);
        $defaultPass = 'password123';

        $nasabah->update([
            'kata_sandi' => Hash::make($defaultPass),
        ]);

        \App\Services\AuditLogger::log(
            'RESET_PASSWORD_NASABAH',
            "Mereset kata sandi nasabah '{$nasabah->nama}' ({$nasabah->kode_user}) ke kata sandi default ({$defaultPass}).",
            'NASABAH',
            'SECURITY',
            'SUKSES',
            [
                'id'        => $nasabah->id,
                'kode_user' => $nasabah->kode_user,
                'nama'      => $nasabah->nama,
                'perubahan' => [
                    'kata_sandi' => [
                        'label'   => 'Kata Sandi Nasabah',
                        'sebelum' => '•••••••• (Sandi Kustom Warga)',
                        'sesudah' => "{$defaultPass} (Reset ke Default Sistem)",
                    ]
                ]
            ]
        );

        return redirect()->route('admin.nasabah.index')
            ->with('success', "Password nasabah '{$nasabah->nama}' ({$nasabah->kode_user}) berhasil direset ke default: {$defaultPass}");
    }

    /**
     * Membaca string alamat tersimpan menjadi pecahan terstruktur (RT, RW, Desa, Kecamatan, Jalan)
     */
    public static function parseAlamat(?string $alamatStr): array
    {
        $res = [
            'rt'        => '',
            'rw'        => '',
            'desa'      => '',
            'kecamatan' => '',
            'kabupaten' => '',
            'jalan'     => '',
        ];

        if (empty($alamatStr) || trim($alamatStr) === '-') {
            return $res;
        }

        $str = trim($alamatStr);

        // Ekstraksi RT (format "RT 01" atau "RT/RW 01/02")
        if (preg_match('/\brt\s*([0-9]+[a-z]?)\b/i', $str, $m)) {
            $res['rt'] = $m[1];
        } elseif (preg_match('/rt\/rw\s*([0-9]+[a-z]?)\s*[\/]/i', $str, $m)) {
            $res['rt'] = $m[1];
        }

        // Ekstraksi RW
        if (preg_match('/\brw\s*([0-9]+[a-z]?)\b/i', $str, $m)) {
            $res['rw'] = $m[1];
        } elseif (preg_match('/rt\/rw\s*[0-9]+[a-z]?\s*[\/]\s*([0-9]+[a-z]?)/i', $str, $m)) {
            $res['rw'] = $m[1];
        }

        // Ekstraksi Desa / Kelurahan
        if (preg_match('/\b(?:desa|kelurahan|kel\.?)\s*([^,()]+)/i', $str, $m)) {
            $res['desa'] = trim($m[1]);
        }

        // Ekstraksi Kecamatan
        if (preg_match('/\b(?:kecamatan|kec\.?)\s*([^,()]+)/i', $str, $m)) {
            $res['kecamatan'] = trim($m[1]);
        }

        // Ekstraksi Kabupaten / Kota
        if (preg_match('/\b(?:kabupaten|kab\.?|kota)\s*([^,()]+)/i', $str, $m)) {
            $res['kabupaten'] = trim($m[1]);
        }

        // Ekstraksi Patokan Jalan / Keterangan tambahan di tanda kurung misal (Balai RW)
        if (preg_match('/\(([^)]+)\)/', $str, $m)) {
            $res['jalan'] = trim($m[1]);
        } else {
            // Ekstraksi Nama Jalan dari bagian koma
            $parts = explode(',', $str);
            $jalanParts = [];
            foreach ($parts as $part) {
                $p = trim($part);
                if (!preg_match('/\brt\b/i', $p) && 
                    !preg_match('/\brw\b/i', $p) && 
                    !preg_match('/\b(?:desa|kelurahan|kel\.?)\b/i', $p) && 
                    !preg_match('/\b(?:kecamatan|kec\.?)\b/i', $p) && 
                    !preg_match('/\b(?:kabupaten|kab\.?|kota)\b/i', $p)) {
                    $jalanParts[] = $p;
                }
            }
            if (!empty($jalanParts)) {
                $res['jalan'] = implode(', ', $jalanParts);
            }
        }

        // Fallback jika tidak terformat sama sekali
        if (empty($res['rt']) && empty($res['rw']) && empty($res['desa']) && empty($res['kecamatan']) && empty($res['kabupaten']) && empty($res['jalan'])) {
            $res['desa'] = $str;
        }

        return $res;
    }

    /**
     * Menggabungkan input RT, RW, Desa, Kecamatan, Kabupaten, dan Jalan menjadi string alamat baku
     */
    public static function compileAlamat(Request $request): string
    {
        $rt   = trim($request->rt ?? '');
        $rw   = trim($request->rw ?? '');
        $desa = trim($request->desa ?? '');
        $kec  = trim($request->kecamatan ?? '');
        $kab  = trim($request->kabupaten ?? '');
        $jalan= trim($request->jalan ?? '');

        $parts = [];
        if ($rt !== '' || $rw !== '') {
            $parts[] = 'RT ' . ($rt !== '' ? $rt : '-') . ' / RW ' . ($rw !== '' ? $rw : '-');
        }
        if ($desa !== '') {
            $cleanDesa = preg_replace('/^(desa|kelurahan|kel\.?)\s*/i', '', $desa);
            $parts[] = 'Desa ' . $cleanDesa;
        }
        if ($kec !== '') {
            $cleanKec = preg_replace('/^(kecamatan|kec\.?)\s*/i', '', $kec);
            $parts[] = 'Kec. ' . $cleanKec;
        }
        if ($kab !== '') {
            $cleanKab = preg_replace('/^(kabupaten|kab\.?|kota)\s*/i', '', $kab);
            $isKota = preg_match('/^kota\b/i', $kab);
            $parts[] = ($isKota ? 'Kota ' : 'Kab. ') . $cleanKab;
        }
        if ($jalan !== '') {
            $parts[] = $jalan;
        }

        if (empty($parts)) {
            return $request->alamat ?: '-';
        }

        return implode(', ', $parts);
    }
}
