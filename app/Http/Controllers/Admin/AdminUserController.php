<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\PetugasMitra;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    public function indexPetugas()
    {
        $this->pastikanConstraintMitraAman();

        $mitras = PetugasMitra::with('mitra')->orderBy('nama')->get();
        $allPos = Partner::with('petugas')->orderBy('nama')->get();

        return view('admin.petugas.index', compact('mitras', 'allPos'));
    }

    public function indexAdmin()
    {
        $admins = Admin::orderBy('nama')->get();

        return view('admin.administrator.index', compact('admins'));
    }

    public function index()
    {
        return redirect()->route('admin.petugas.index');
    }

    public function storeAdmin(Request $request)
    {
        abort(403, 'Penambahan akun administrator baru tidak diizinkan.');
    }

    public function destroyAdmin(string $id)
    {
        if ($id === session('admin_id')) {
            return redirect()->route('admin.administrator.index')->with('error', 'Anda tidak dapat menghapus akun admin Anda sendiri saat sedang aktif login.');
        }

        $admin = Admin::findOrFail($id);
        $nama = $admin->nama;

        // Cegah penghapusan jika ini satu-satunya Super Admin
        if ($admin->peran === 'SUPER_ADMIN') {
            $superCount = Admin::where('peran', 'SUPER_ADMIN')->count();
            if ($superCount <= 1) {
                return redirect()->route('admin.administrator.index')->with('error', 'Tidak dapat menghapus satu-satunya Super Administrator pada sistem.');
            }
        }

        $admin->delete();
        $this->tulisCatatanAudit('HAPUS_ADMIN', "Akun Administrator '{$nama}' dihapus dari sistem.");

        return redirect()->route('admin.administrator.index')->with('success', "Akun Administrator '{$nama}' berhasil dihapus dari sistem.");
    }

    public function storeMitra(Request $request)
    {
        $this->pastikanConstraintMitraAman();

        $request->validate([
            'nama'       => 'required|string|max:100',
            'email'      => 'required|email|unique:petugas_mitra,email',
            'kata_sandi' => 'required|string|min:6',
            'telepon'    => 'required|string|max:20',
            'alamat'     => 'nullable|string|max:255',
            'opsi_pos'   => 'nullable|string|in:none,existing,new',
            'pos_id'     => 'nullable|required_if:opsi_pos,existing|string|exists:mitra,id',
            'nama_pos'   => 'nullable|required_if:opsi_pos,new|string|max:150',
            'alamat_pos' => 'nullable|required_if:opsi_pos,new|string|max:255',
            'jam_buka'   => 'nullable|string|max:100',
        ]);

        $petugas = PetugasMitra::create([
            'nama'       => $request->nama,
            'email'      => $request->email,
            'kata_sandi' => Hash::make($request->kata_sandi),
            'telepon'    => $request->telepon,
            'alamat'     => $request->alamat ?: ($request->alamat_pos ?: '-'),
            'saldo'      => 0,
            'peran'      => 'MITRA',
        ]);

        $pesanTambahan = '';

        if ($request->opsi_pos === 'existing' && $request->pos_id) {
            $pos = Partner::find($request->pos_id);
            if ($pos) {
                $pos->update(['id_pengguna' => $petugas->id]);
                $pesanTambahan = " dan ditugaskan di '{$pos->nama}'";
            }
        } elseif ($request->opsi_pos === 'new' && $request->nama_pos) {
            $pos = Partner::create([
                'id_pengguna' => $petugas->id,
                'nama'        => $request->nama_pos,
                'alamat'      => $request->alamat_pos ?: ($request->alamat ?: '-'),
                'jam_buka'    => $request->jam_buka ?: 'Senin - Sabtu, 08:00 - 15:00 WIB',
                'lintang'     => -6.9932,
                'bujur'       => 110.4203,
            ]);
            $pesanTambahan = " serta dibuatkan unit pos baru '{$pos->nama}'";
        }

        $this->tulisCatatanAudit('TAMBAH_MITRA', "Akun Petugas Mitra '{$petugas->nama}' ({$petugas->email}) ditambahkan{$pesanTambahan}.");

        return redirect()->route('admin.petugas.index')
            ->with('success', "Akun Petugas Mitra '{$petugas->nama}' berhasil didaftarkan{$pesanTambahan}.");
    }

    public function updateMitra(Request $request, string $id)
    {
        $petugas = PetugasMitra::findOrFail($id);

        $request->validate([
            'nama'    => 'required|string|max:100',
            'email'   => 'required|email|unique:petugas_mitra,email,' . $id,
            'telepon' => 'required|string|max:20',
            'alamat'  => 'nullable|string|max:255',
        ]);

        $sebelum = [
            'nama'    => $petugas->nama,
            'email'   => $petugas->email,
            'telepon' => $petugas->telepon,
            'alamat'  => $petugas->alamat,
        ];

        $petugas->update([
            'nama'    => $request->nama,
            'email'   => $request->email,
            'telepon' => $request->telepon,
            'alamat'  => $request->alamat ?: $petugas->alamat,
        ]);

        $sesudah = [
            'nama'    => $petugas->nama,
            'email'   => $petugas->email,
            'telepon' => $petugas->telepon,
            'alamat'  => $petugas->alamat,
        ];

        $diff = \App\Services\AuditLogger::formatPerubahan($sebelum, $sesudah);

        $this->tulisCatatanAudit('UPDATE_MITRA', "Data profil Petugas Mitra '{$petugas->nama}' ({$petugas->email}) diperbarui.", [
            'id'           => $petugas->id,
            'data_sebelum' => $sebelum,
            'data_sesudah' => $sesudah,
            'perubahan'    => $diff,
        ]);

        return redirect()->route('admin.petugas.index')
            ->with('success', "Profil Petugas Mitra '{$petugas->nama}' berhasil diperbarui.");
    }

    public function assignPos(Request $request, string $id)
    {
        $this->pastikanConstraintMitraAman();

        $petugas = PetugasMitra::findOrFail($id);
        $posLama = Partner::where('id_pengguna', $id)->first();
        $namaPosLama = $posLama ? $posLama->nama : 'Belum Ditugaskan';

        $request->validate([
            'opsi'       => 'required|in:existing,new,unlink',
            'pos_id'     => 'nullable|required_if:opsi,existing|string|exists:mitra,id',
            'nama_pos'   => 'nullable|required_if:opsi,new|string|max:150',
            'alamat_pos' => 'nullable|required_if:opsi,new|string|max:255',
            'jam_buka'   => 'nullable|string|max:100',
        ]);

        if ($request->opsi === 'unlink') {
            Partner::where('id_pengguna', $id)->update(['id_pengguna' => null]);
            $this->tulisCatatanAudit('LEPAS_POS_MITRA', "Penugasan pos dilepas dari Petugas Mitra '{$petugas->nama}'.", [
                'id'        => $petugas->id,
                'nama'      => $petugas->nama,
                'perubahan' => [
                    'pos_penugasan' => [
                        'label'   => 'Unit Pos Bank Sampah',
                        'sebelum' => $namaPosLama,
                        'sesudah' => 'Dilepas (Tidak Ada)',
                    ]
                ]
            ]);
            return redirect()->route('admin.petugas.index')->with('success', "Penugasan pos berhasil dilepas dari petugas '{$petugas->nama}'.");
        }

        if ($request->opsi === 'existing') {
            // Lepas penugasan pos lama dari petugas ini
            Partner::where('id_pengguna', $id)->update(['id_pengguna' => null]);
            $pos = Partner::findOrFail($request->pos_id);
            $pos->update(['id_pengguna' => $petugas->id]);

            $this->tulisCatatanAudit('TAUTKAN_POS_MITRA', "Petugas '{$petugas->nama}' ditugaskan ke pos '{$pos->nama}'.", [
                'id'        => $petugas->id,
                'nama'      => $petugas->nama,
                'perubahan' => [
                    'pos_penugasan' => [
                        'label'   => 'Unit Pos Bank Sampah',
                        'sebelum' => $namaPosLama,
                        'sesudah' => $pos->nama,
                    ]
                ]
            ]);
            return redirect()->route('admin.petugas.index')->with('success', "Petugas '{$petugas->nama}' berhasil ditugaskan ke '{$pos->nama}'.");
        }

        if ($request->opsi === 'new') {
            // Lepas penugasan pos lama jika ada
            Partner::where('id_pengguna', $id)->update(['id_pengguna' => null]);
            $pos = Partner::create([
                'id_pengguna' => $petugas->id,
                'nama'        => $request->nama_pos,
                'alamat'      => $request->alamat_pos ?: ($petugas->alamat ?: '-'),
                'jam_buka'    => $request->jam_buka ?: 'Senin - Sabtu, 08:00 - 15:00 WIB',
                'lintang'     => -6.9932,
                'bujur'       => 110.4203,
            ]);

            $this->tulisCatatanAudit('BUAT_DAN_TAUTKAN_POS', "Petugas '{$petugas->nama}' dibuatkan pos baru '{$pos->nama}'.", [
                'id'        => $petugas->id,
                'nama'      => $petugas->nama,
                'perubahan' => [
                    'pos_penugasan' => [
                        'label'   => 'Unit Pos Bank Sampah Baru',
                        'sebelum' => $namaPosLama,
                        'sesudah' => $pos->nama,
                    ]
                ]
            ]);
            return redirect()->route('admin.petugas.index')->with('success', "Pos baru '{$pos->nama}' berhasil dibuat dan ditugaskan ke '{$petugas->nama}'.");
        }

        return redirect()->route('admin.petugas.index');
    }

    public function unlinkPos(string $id)
    {
        $petugas = PetugasMitra::findOrFail($id);
        $posLama = Partner::where('id_pengguna', $id)->first();
        $namaPosLama = $posLama ? $posLama->nama : 'Belum Ditugaskan';

        Partner::where('id_pengguna', $id)->update(['id_pengguna' => null]);

        $this->tulisCatatanAudit('LEPAS_POS_MITRA', "Penugasan pos dilepas dari Petugas Mitra '{$petugas->nama}'.", [
            'id'        => $petugas->id,
            'nama'      => $petugas->nama,
            'perubahan' => [
                'pos_penugasan' => [
                    'label'   => 'Unit Pos Bank Sampah',
                    'sebelum' => $namaPosLama,
                    'sesudah' => 'Dilepas (Tidak Ada)',
                ]
            ]
        ]);

        return redirect()->route('admin.petugas.index')->with('success', "Penugasan pos berhasil dilepas dari petugas '{$petugas->nama}'.");
    }

    public function destroyMitra(string $id)
    {
        $mitra = PetugasMitra::findOrFail($id);
        $nama = $mitra->nama;

        // Lepas pos yang dioperasikan oleh akun mitra ini agar pos & riwayat transaksi tetap aman
        Partner::where('id_pengguna', $id)->update(['id_pengguna' => null]);
        $mitra->delete();

        $this->tulisCatatanAudit('HAPUS_MITRA', "Akun Petugas Mitra '{$nama}' ({$mitra->email}) dihapus dari sistem.", [
            'id'      => $mitra->id,
            'nama'    => $mitra->nama,
            'email'   => $mitra->email,
            'telepon' => $mitra->telepon,
            'alamat'  => $mitra->alamat,
        ]);

        return redirect()->route('admin.petugas.index')->with('success', "Akun Petugas Mitra '{$nama}' berhasil dihapus. Unit pos dan riwayat transaksi tetap tersimpan aman.");
    }

    public function updatePasswordPetugas(Request $request, string $id)
    {
        $request->validate([
            'password_baru' => 'required|string|min:6',
        ]);

        $petugas = PetugasMitra::findOrFail($id);
        $petugas->update([
            'kata_sandi' => Hash::make($request->password_baru),
        ]);

        $this->tulisCatatanAudit('GANTI_PASSWORD_MITRA', "Kata sandi untuk Petugas Mitra '{$petugas->nama}' ({$petugas->email}) diperbarui.", [
            'id'        => $petugas->id,
            'nama'      => $petugas->nama,
            'email'     => $petugas->email,
            'perubahan' => [
                'kata_sandi' => [
                    'label'   => 'Kata Sandi Petugas Mitra',
                    'sebelum' => '•••••••• (Sandi Lama)',
                    'sesudah' => '•••••••• (Sandi Baru Diperbarui)',
                ]
            ]
        ]);

        return redirect()->route('admin.petugas.index')->with('success', "Password untuk akun Petugas Mitra '{$petugas->nama}' ({$petugas->email}) berhasil diperbarui.");
    }

    public function updatePasswordAdmin(Request $request, string $id)
    {
        $request->validate([
            'password_baru' => 'required|string|min:6',
        ]);

        $isSuperAdmin = session('admin_peran') === 'SUPER_ADMIN';
        $user = Admin::findOrFail($id);

        if (!$isSuperAdmin && $id !== session('admin_id')) {
            return redirect()->route('admin.administrator.index')->with('error', 'Hanya Super Admin yang dapat me-reset kata sandi akun admin lain.');
        }

        $user->update([
            'kata_sandi' => Hash::make($request->password_baru),
        ]);

        $this->tulisCatatanAudit('GANTI_PASSWORD_ADMIN', "Kata sandi untuk Admin '{$user->nama}' ({$user->email}) diperbarui.", [
            'id'        => $user->id,
            'nama'      => $user->nama,
            'email'     => $user->email,
            'perubahan' => [
                'kata_sandi' => [
                    'label'   => 'Kata Sandi Akun Admin',
                    'sebelum' => '•••••••• (Sandi Lama)',
                    'sesudah' => '•••••••• (Sandi Baru Diperbarui)',
                ]
            ]
        ]);

        return redirect()->route('admin.administrator.index')->with('success', "Password untuk akun Admin '{$user->nama}' berhasil diperbarui.");
    }

    public function updatePassword(Request $request, string $id)
    {
        $user = Admin::find($id);
        if ($user) {
            return $this->updatePasswordAdmin($request, $id);
        }

        return $this->updatePasswordPetugas($request, $id);
    }

    public function viewGantiSandi()
    {
        $adminId = session('admin_id');
        $admin = Admin::find($adminId);
        if (!$admin) {
            return redirect()->route('admin.dashboard')->with('error', 'Sesi admin tidak valid.');
        }

        return view('admin.pengaturan.sandi', compact('admin'));
    }

    public function updateGantiSandi(Request $request)
    {
        $adminId = session('admin_id');
        $admin = Admin::findOrFail($adminId);

        $request->validate([
            'password_sekarang' => 'required|string',
            'password_baru'     => 'required|string|min:6|confirmed',
        ], [
            'password_sekarang.required' => 'Kata sandi saat ini wajib diisi.',
            'password_baru.required'     => 'Kata sandi baru wajib diisi.',
            'password_baru.min'          => 'Kata sandi baru minimal 6 karakter.',
            'password_baru.confirmed'    => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if (!Hash::check($request->password_sekarang, $admin->kata_sandi)) {
            return back()->withErrors(['password_sekarang' => 'Kata sandi saat ini tidak cocok.'])->withInput();
        }

        $admin->update([
            'kata_sandi' => Hash::make($request->password_baru),
        ]);

        $this->tulisCatatanAudit('GANTI_PASSWORD_PRIBADI', "Admin '{$admin->nama}' ({$admin->email}) memperbarui kata sandi akunnya sendiri.", [
            'id'        => $admin->id,
            'nama'      => $admin->nama,
            'email'     => $admin->email,
            'perubahan' => [
                'kata_sandi' => [
                    'label'   => 'Kata Sandi Akun Pribadi',
                    'sebelum' => '•••••••• (Sandi Lama)',
                    'sesudah' => '•••••••• (Sandi Baru Diperbarui)',
                ]
            ]
        ]);

        return back()->with('success', 'Kata sandi akun Anda berhasil diperbarui.');
    }

    /**
     * PUT /admin/administrator/{id}/promote
     */
    public function promoteAdmin(string $id)
    {
        $target = Admin::findOrFail($id);

        if ($target->peran === 'SUPER_ADMIN') {
            return redirect()->route('admin.administrator.index')->with('error', "Akun '{$target->nama}' sudah berstatus Super Admin.");
        }

        $target->update(['peran' => 'SUPER_ADMIN']);
        $this->tulisCatatanAudit('PROMOTE_ADMIN', "Akun Admin '{$target->nama}' ({$target->email}) dipromosikan menjadi Super Admin.", [
            'id'        => $target->id,
            'nama'      => $target->nama,
            'email'     => $target->email,
            'perubahan' => [
                'peran' => [
                    'label'   => 'Hak Akses / Peran',
                    'sebelum' => 'ADMIN (Operasional)',
                    'sesudah' => 'SUPER_ADMIN (Hak Akses Penuh)',
                ]
            ]
        ]);

        return redirect()->route('admin.administrator.index')->with('success', "Akun '{$target->nama}' berhasil dipromosikan menjadi Super Administrator.");
    }

    /**
     * PUT /admin/administrator/{id}/demote
     */
    public function demoteAdmin(string $id)
    {
        if ($id === session('admin_id')) {
            return redirect()->route('admin.administrator.index')->with('error', 'Anda tidak dapat menurunkan peran akun Anda sendiri saat sedang aktif login.');
        }

        $target = Admin::findOrFail($id);

        if ($target->peran !== 'SUPER_ADMIN') {
            return redirect()->route('admin.administrator.index')->with('error', "Akun '{$target->nama}' bukan Super Admin.");
        }

        $superCount = Admin::where('peran', 'SUPER_ADMIN')->count();
        if ($superCount <= 1) {
            return redirect()->route('admin.administrator.index')->with('error', 'Tidak dapat mendemote satu-satunya Super Administrator pada sistem. Promosikan admin lain terlebih dahulu.');
        }

        $target->update(['peran' => 'ADMIN']);
        $this->tulisCatatanAudit('DEMOTE_ADMIN', "Akun Super Admin '{$target->nama}' ({$target->email}) diturunkan menjadi Admin Operasional.", [
            'id'        => $target->id,
            'nama'      => $target->nama,
            'email'     => $target->email,
            'perubahan' => [
                'peran' => [
                    'label'   => 'Hak Akses / Peran',
                    'sebelum' => 'SUPER_ADMIN (Hak Akses Penuh)',
                    'sesudah' => 'ADMIN (Operasional Desa)',
                ]
            ]
        ]);

        return redirect()->route('admin.administrator.index')->with('success', "Akun '{$target->nama}' berhasil diturunkan menjadi Admin Operasional.");
    }

    // ── Private Helper ────────────────────────────────────────────────────

    private function tulisCatatanAudit(string $aksi, string $keterangan, $detail = null): void
    {
        $tipe = 'UPDATE';
        if (str_contains($aksi, 'TAMBAH') || str_contains($aksi, 'BUAT')) {
            $tipe = 'CREATE';
        } elseif (str_contains($aksi, 'HAPUS')) {
            $tipe = 'DELETE';
        } elseif (str_contains($aksi, 'PASSWORD') || str_contains($aksi, 'PROMOTE') || str_contains($aksi, 'DEMOTE')) {
            $tipe = 'SECURITY';
        }

        \App\Services\AuditLogger::log($aksi, $keterangan, 'PENGGUNA', $tipe, 'SUKSES', $detail);
    }

    private function pastikanConstraintMitraAman(): void
    {
        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `mitra` DROP FOREIGN KEY `mitra_id_pengguna_foreign`");
        } catch (\Throwable $e) {
            // ignore jika sudah tidak ada
        }

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE `mitra` MODIFY `id_pengguna` CHAR(36) NULL");
        } catch (\Throwable $e) {
            // ignore jika sudah nullable
        }
    }
}
