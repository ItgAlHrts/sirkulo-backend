<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrashCategory;
use Illuminate\Http\Request;

class AdminHargaSampahController extends Controller
{
    public function index()
    {
        $kategori = TrashCategory::orderBy('nama')->get();
        return view('admin.harga-sampah.index', compact('kategori'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama'           => 'required|string|max:100',
            'harga_per_kg'   => 'required|integer|min:1',
            'harga_pengepul' => 'nullable|integer|min:1',
            'foto'           => 'nullable',
            'foto.*'         => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $fotoContoh = [];
        $ikon = 'ic_trash';

        // Tangani jika dikirim satu foto atau multiple foto (array)
        $files = $request->file('foto');
        if ($files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('sampah', 'public');
                    $url = url('storage/' . $path);
                    if (!in_array($url, $fotoContoh)) {
                        $fotoContoh[] = $url;
                    }
                }
            }
            if (!empty($fotoContoh)) {
                $ikon = $fotoContoh[0];
            }
        }

        $hargaPengepul = $request->harga_pengepul ?: (int) max($request->harga_per_kg + 500, round($request->harga_per_kg * 1.4));

        TrashCategory::create([
            'nama'           => $request->nama,
            'harga_per_kg'   => $request->harga_per_kg,
            'harga_pengepul' => $hargaPengepul,
            'ikon'           => $ikon,
            'foto_contoh'    => $fotoContoh,
        ]);

        $totalFoto = count($fotoContoh);
        $this->tulisCatatanAudit('TAMBAH_HARGA_SAMPAH', "Menambahkan komoditas sampah baru '{$request->nama}' dengan tarif Rp " . number_format($request->harga_per_kg, 0, ',', '.') . "/kg ({$totalFoto} foto).");

        return redirect()->route('admin.harga-sampah.index')
            ->with('success', "Kategori sampah '{$request->nama}' berhasil ditambahkan ({$totalFoto} foto terunggah).");
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'nama'           => 'required|string|max:100',
            'harga_per_kg'   => 'required|integer|min:0',
            'harga_pengepul' => 'nullable|integer|min:0',
            'foto'           => 'nullable',
            'foto.*'         => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'hapus_foto'     => 'nullable|array',
            'hapus_foto.*'   => 'string',
            'foto_utama'     => 'nullable|string',
        ]);

        $kategori = TrashCategory::findOrFail($id);
        $current = $kategori->foto_contoh ?? [];

        // 1. Hapus foto yang dicentang/dipilih untuk dihapus
        if ($request->has('hapus_foto') && is_array($request->hapus_foto)) {
            $hapusList = $request->hapus_foto;
            $hapusBasenames = array_map(function($u) {
                return basename(parse_url($u, PHP_URL_PATH) ?: $u);
            }, $hapusList);

            $current = array_values(array_filter($current, function($url) use ($hapusList, $hapusBasenames) {
                $base = basename(parse_url($url, PHP_URL_PATH) ?: $url);
                return !in_array($url, $hapusList) && !in_array($base, $hapusBasenames);
            }));
        }

        // 2. Tambahkan foto-foto baru jika ada yang diunggah
        $files = $request->file('foto');
        $jumlahFotoBaru = 0;
        if ($files) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('sampah', 'public');
                    $url = url('storage/' . $path);
                    if (!in_array($url, $current)) {
                        $current[] = $url;
                        $jumlahFotoBaru++;
                    }
                }
            }
        }

        // 3. Tentukan foto utama (ikon)
        $ikon = $kategori->ikon ?? 'ic_trash';
        if ($request->filled('foto_utama')) {
            $reqUtama = $request->foto_utama;
            $reqUtamaBase = basename(parse_url($reqUtama, PHP_URL_PATH) ?: $reqUtama);
            foreach ($current as $f) {
                $fBase = basename(parse_url($f, PHP_URL_PATH) ?: $f);
                if ($f === $reqUtama || $fBase === $reqUtamaBase) {
                    $ikon = $f;
                    break;
                }
            }
        }
        if (!empty($current)) {
            $currentBases = array_map(function($f) {
                return basename(parse_url($f, PHP_URL_PATH) ?: $f);
            }, $current);
            $ikonBase = basename(parse_url($ikon, PHP_URL_PATH) ?: $ikon);
            if (!in_array($ikon, $current) && !in_array($ikonBase, $currentBases)) {
                $ikon = $current[0];
            }
        } else {
            $ikon = 'ic_trash';
        }

        $data = [
            'nama'           => $request->nama,
            'harga_per_kg'   => $request->harga_per_kg,
            'harga_pengepul' => $request->harga_pengepul ?: (int) max($request->harga_per_kg + 500, round($request->harga_per_kg * 1.4)),
            'ikon'           => $ikon,
            'foto_contoh'    => $current,
        ];

        $sebelum = [
            'nama'           => $kategori->nama,
            'harga_per_kg'   => $kategori->harga_per_kg,
            'harga_pengepul' => $kategori->harga_pengepul,
        ];

        $kategori->update($data);

        $sesudah = [
            'nama'           => $kategori->nama,
            'harga_per_kg'   => (int) $request->harga_per_kg,
            'harga_pengepul' => (int) $data['harga_pengepul'],
        ];

        $detailPerubahan = \App\Services\AuditLogger::formatPerubahan($sebelum, $sesudah, [
            'harga_per_kg'   => 'Harga Beli Nasabah (per Kg)',
            'harga_pengepul' => 'Harga Jual Mitra (per Kg)',
            'nama'           => 'Nama Komoditas',
        ]);

        $catatanFoto = $jumlahFotoBaru > 0 ? " ({$jumlahFotoBaru} foto baru ditambahkan)" : '';
        $keteranganAudit = "Memperbarui tarif komoditas '{$kategori->nama}' (Beli: Rp " . number_format($request->harga_per_kg, 0, ',', '.') . "/kg){$catatanFoto}.";
        $this->tulisCatatanAudit('UPDATE_HARGA_SAMPAH', $keteranganAudit, [
            'id'           => $kategori->id,
            'nama'         => $kategori->nama,
            'data_sebelum' => $sebelum,
            'data_sesudah' => $sesudah,
            'perubahan'    => $detailPerubahan,
        ]);

        return redirect()->route('admin.harga-sampah.index')
            ->with('success', "Kategori sampah '{$kategori->nama}' berhasil diperbarui{$catatanFoto}.");
    }

    public function deletePhoto(Request $request, string $id)
    {
        $request->validate([
            'foto_url' => 'required|string',
        ]);

        $kategori = TrashCategory::findOrFail($id);
        $current = $kategori->foto_contoh ?? [];
        $targetUrl = $request->foto_url;
        $targetBase = basename(parse_url($targetUrl, PHP_URL_PATH) ?: $targetUrl);

        $newPhotos = array_values(array_filter($current, function($url) use ($targetUrl, $targetBase) {
            $base = basename(parse_url($url, PHP_URL_PATH) ?: $url);
            return $url !== $targetUrl && $base !== $targetBase;
        }));

        $ikon = $kategori->ikon;
        $ikonBase = basename(parse_url($ikon, PHP_URL_PATH) ?: $ikon);
        if ($ikon === $targetUrl || $ikonBase === $targetBase) {
            $ikon = !empty($newPhotos) ? $newPhotos[0] : 'ic_trash';
        }

        $kategori->update([
            'foto_contoh' => $newPhotos,
            'ikon'        => $ikon,
        ]);

        $this->tulisCatatanAudit('HAPUS_FOTO_SAMPAH', "Menghapus salah satu foto contoh dari komoditas '{$kategori->nama}'.");

        return back()->with('success', "Foto berhasil dihapus dari galeri komoditas '{$kategori->nama}'.");
    }

    public function destroy(string $id)
    {
        $kategori = TrashCategory::findOrFail($id);
        $nama = $kategori->nama;
        $kategori->delete();

        $this->tulisCatatanAudit('HAPUS_HARGA_SAMPAH', "Menghapus komoditas sampah '{$nama}'.");

        return redirect()->route('admin.harga-sampah.index')
            ->with('success', "Kategori sampah '{$nama}' berhasil dihapus.");
    }

    private function tulisCatatanAudit(string $aksi, string $keterangan, $detail = null): void
    {
        $tipe = 'UPDATE';
        if (str_contains($aksi, 'TAMBAH')) {
            $tipe = 'CREATE';
        } elseif (str_contains($aksi, 'HAPUS')) {
            $tipe = 'DELETE';
        }

        \App\Services\AuditLogger::log($aksi, $keterangan, 'HARGA_SAMPAH', $tipe, 'SUKSES', $detail);
    }
}
