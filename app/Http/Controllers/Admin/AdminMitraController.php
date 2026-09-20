<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrashCategory;
use App\Models\Partner;
use App\Models\PetugasMitra;
use App\Models\Transaction;
use Illuminate\Http\Request;

class AdminMitraController extends Controller
{
    public function index(Request $request)
    {
        $this->pastikanKolomPengelolaAda();

        $query = Partner::with(['pengguna', 'kategoriSampah'])->orderByDesc('dibuat_pada');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('nama', 'like', "%$q%")
                  ->orWhere('pengelola', 'like', "%$q%")
                  ->orWhere('alamat', 'like', "%$q%");
            });
        }

        $mitra = $query->paginate(15)->withQueryString();
        $petugasList = PetugasMitra::orderBy('nama')->get();
        $kategoriList = TrashCategory::orderBy('nama')->get();

        return view('admin.mitra.index', compact('mitra', 'petugasList', 'kategoriList'));
    }

    public function store(Request $request)
    {
        $this->pastikanKolomPengelolaAda();

        $request->validate([
            'nama'           => 'required|string|max:150',
            'pengelola'      => 'nullable|string|max:150',
            'alamat'         => 'required|string|max:255',
            'jam_buka'       => 'required|string|max:100',
            'id_pengguna'    => 'nullable|string|exists:petugas_mitra,id',
            'kategori_ids'   => 'nullable|array',
            'kategori_ids.*' => 'exists:kategori_sampah,id',
        ]);

        if ($request->filled('id_pengguna')) {
            // Lepas penugasan pos lama dari petugas ini agar 1 petugas = 1 pos
            Partner::where('id_pengguna', $request->id_pengguna)->update(['id_pengguna' => null]);
        }

        $pos = Partner::create([
            'nama'        => $request->nama,
            'pengelola'   => $request->pengelola,
            'alamat'      => $request->alamat,
            'jam_buka'    => $request->jam_buka,
            'id_pengguna' => $request->id_pengguna ?: null,
            'lintang'     => -6.9932,
            'bujur'       => 110.4203,
        ]);

        $kategoriIds = $request->input('kategori_ids');
        if (empty($kategoriIds)) {
            $kategoriIds = TrashCategory::pluck('id')->toArray();
        }
        $pos->kategoriSampah()->sync($kategoriIds);

        \App\Services\AuditLogger::log(
            'TAMBAH_POS',
            "Menambahkan unit pos bank sampah baru '{$pos->nama}' di '{$pos->alamat}' (Jam: {$pos->jam_buka}).",
            'POS_MITRA',
            'CREATE',
            'SUKSES',
            [
                'id'          => $pos->id,
                'nama'        => $pos->nama,
                'alamat'      => $pos->alamat,
                'jam_buka'    => $pos->jam_buka,
                'pengelola'   => $pos->pengelola,
                'id_pengguna' => $pos->id_pengguna,
            ]
        );

        return redirect()->route('admin.mitra.index')
            ->with('success', "Unit Pos Bank Sampah '{$pos->nama}' berhasil ditambahkan.");
    }

    public function show(string $id)
    {
        $pos       = Partner::with(['pengguna', 'kategoriSampah'])->findOrFail($id);
        $transaksi = Transaction::where('id_mitra', $id)
            ->with('pengguna')
            ->orderByDesc('dibuat_pada')
            ->limit(20)
            ->get();

        $totalSetoran    = Transaction::where('id_mitra', $id)->where('jenis', 'SETORAN')->count();
        $totalPenarikan  = Transaction::where('id_mitra', $id)->where('jenis', 'PENARIKAN')->count();
        $totalOmset      = Transaction::where('id_mitra', $id)->where('jenis', 'SETORAN')->sum('jumlah_total');

        return view('admin.mitra.show', compact('pos', 'transaksi', 'totalSetoran', 'totalPenarikan', 'totalOmset'));
    }

    public function update(Request $request, string $id)
    {
        $this->pastikanKolomPengelolaAda();

        $pos = Partner::findOrFail($id);

        $request->validate([
            'nama'           => 'required|string|max:150',
            'pengelola'      => 'nullable|string|max:150',
            'alamat'         => 'required|string|max:255',
            'jam_buka'       => 'required|string|max:100',
            'id_pengguna'    => 'nullable|string|exists:petugas_mitra,id',
            'kategori_ids'   => 'nullable|array',
            'kategori_ids.*' => 'exists:kategori_sampah,id',
        ]);

        if ($request->filled('id_pengguna')) {
            // Lepas penugasan pos lain dari petugas ini agar 1 petugas = 1 pos
            Partner::where('id_pengguna', $request->id_pengguna)
                ->where('id', '!=', $pos->id)
                ->update(['id_pengguna' => null]);
        }

        $petugasLama = $pos->pengguna ? $pos->pengguna->nama : 'Belum Ada Petugas';

        $sebelum = [
            'nama'        => $pos->nama,
            'pengelola'   => $pos->pengelola,
            'alamat'      => $pos->alamat,
            'jam_buka'    => $pos->jam_buka,
            'petugas'     => $petugasLama,
        ];

        $pos->update([
            'nama'        => $request->nama,
            'pengelola'   => $request->pengelola,
            'alamat'      => $request->alamat,
            'jam_buka'    => $request->jam_buka,
            'id_pengguna' => $request->id_pengguna ?: null,
        ]);

        $pos->kategoriSampah()->sync($request->input('kategori_ids', []));

        $posBaru = $pos->fresh(['pengguna']);
        $petugasBaru = $posBaru->pengguna ? $posBaru->pengguna->nama : 'Belum Ada Petugas';

        $sesudah = [
            'nama'        => $posBaru->nama,
            'pengelola'   => $posBaru->pengelola,
            'alamat'      => $posBaru->alamat,
            'jam_buka'    => $posBaru->jam_buka,
            'petugas'     => $petugasBaru,
        ];

        $diff = \App\Services\AuditLogger::formatPerubahan($sebelum, $sesudah, [
            'nama'      => 'Nama Unit Pos',
            'pengelola' => 'Pengelola / Penanggung Jawab',
            'alamat'    => 'Alamat Lokasi Pos',
            'jam_buka'  => 'Jam Buka & Jadwal',
            'petugas'   => 'Petugas Mitra',
        ]);

        \App\Services\AuditLogger::log(
            'UPDATE_POS',
            "Memperbarui rincian unit pos bank sampah '{$pos->nama}'.",
            'POS_MITRA',
            'UPDATE',
            'SUKSES',
            [
                'id'           => $pos->id,
                'data_sebelum' => $sebelum,
                'data_sesudah' => $sesudah,
                'perubahan'    => $diff,
            ]
        );

        return redirect()->route('admin.mitra.index')
            ->with('success', "Data Pos Bank Sampah '{$pos->nama}' berhasil diperbarui.");
    }

    public function destroy(string $id)
    {
        $pos = Partner::findOrFail($id);
        $nama = $pos->nama;
        $alamat = $pos->alamat;

        // Hapus pos tanpa menghapus akun petugas
        $pos->delete();

        \App\Services\AuditLogger::log(
            'HAPUS_POS',
            "Menghapus unit pos bank sampah '{$nama}' ({$alamat}).",
            'POS_MITRA',
            'DELETE',
            'PERINGATAN',
            ['id' => $id, 'nama' => $nama, 'alamat' => $alamat]
        );

        return redirect()->route('admin.mitra.index')
            ->with('success', "Pos Bank Sampah '{$nama}' berhasil dihapus. Akun petugas terkait tetap aman.");
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
