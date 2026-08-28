<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrashCategory;
use App\Models\Education;
use App\Models\Partner;
use App\Models\Notification;
use Illuminate\Http\Request;

class DataController extends Controller
{
    // ── Harga Sampah ───────────────────────────────────────────────
    public function getTrashPrices(Request $request)
    {
        try {
            // Pastikan master sampah tidak pernah kosong (Auto-Seed jika kosong)
            if (TrashCategory::count() === 0) {
                $defaultCategories = [
                    ['nama' => 'Plastik',           'harga_per_kg' => 3000,  'harga_pengepul' => 4500,  'ikon' => 'ic_plastic'],
                    ['nama' => 'Kertas',            'harga_per_kg' => 2000,  'harga_pengepul' => 3000,  'ikon' => 'ic_paper'],
                    ['nama' => 'Kardus',            'harga_per_kg' => 1500,  'harga_pengepul' => 2500,  'ikon' => 'ic_cardboard'],
                    ['nama' => 'Kaca / Botol Kaca', 'harga_per_kg' => 1000,  'harga_pengepul' => 1800,  'ikon' => 'ic_glass'],
                    ['nama' => 'Logam & Besi',      'harga_per_kg' => 8000,  'harga_pengepul' => 12000, 'ikon' => 'ic_metal'],
                    ['nama' => 'Botol Plastik PET', 'harga_per_kg' => 2500,  'harga_pengepul' => 4000,  'ikon' => 'ic_bottle'],
                    ['nama' => 'Elektronik Bekas',  'harga_per_kg' => 15000, 'harga_pengepul' => 22000, 'ikon' => 'ic_electronic'],
                    ['nama' => 'Minyak Jelantah',   'harga_per_kg' => 5000,  'harga_pengepul' => 7500,  'ikon' => 'ic_oil'],
                ];
                foreach ($defaultCategories as $cat) {
                    TrashCategory::create($cat);
                }
            }
        } catch (\Exception $e) {
            // ignore
        }
        return response()->json(TrashCategory::orderBy('nama', 'asc')->get());
    }

    public function storeTrashPrice(Request $request)
    {
        try {
            $data = $request->validate([
                'nama'           => 'required|string',
                'harga_per_kg'   => 'required|numeric|min:1',
                'harga_pengepul' => 'nullable|numeric|min:1',
                'ikon'           => 'nullable|string',
            ]);
            $data['harga_per_kg'] = (int) $data['harga_per_kg'];
            if (!empty($data['harga_pengepul'])) {
                $data['harga_pengepul'] = (int) $data['harga_pengepul'];
            } else {
                // Default harga jual ke pengepul = harga beli nasabah + 40%
                $data['harga_pengepul'] = (int) max($data['harga_per_kg'] + 500, round($data['harga_per_kg'] * 1.4));
            }
            if (empty($data['ikon'])) {
                $data['ikon'] = 'ic_trash';
            }
            $item = TrashCategory::create($data);
            return response()->json($item, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['galat' => 'Validasi gagal: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))], 422);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal menambah kategori sampah: ' . $e->getMessage()], 500);
        }
    }

    public function updateTrashPrice(Request $request, $id)
    {
        try {
            $item = TrashCategory::find($id);
            if (!$item) {
                return response()->json(['galat' => 'Kategori sampah tidak ditemukan'], 404);
            }
            $dataToUpdate = array_filter($request->only('nama', 'harga_per_kg', 'harga_pengepul', 'ikon'), fn($val) => !is_null($val));
            if (isset($dataToUpdate['harga_per_kg'])) {
                $dataToUpdate['harga_per_kg'] = (int) $dataToUpdate['harga_per_kg'];
            }
            if (isset($dataToUpdate['harga_pengepul'])) {
                $dataToUpdate['harga_pengepul'] = (int) $dataToUpdate['harga_pengepul'];
            }
            $item->update($dataToUpdate);
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal mengubah harga sampah: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTrashPrice($id)
    {
        try {
            $item = TrashCategory::find($id);
            if ($item) {
                $item->delete();
            }
            return response()->json(['pesan' => 'Kategori sampah berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal menghapus kategori: ' . $e->getMessage()], 500);
        }
    }

    // ── Mitra ──────────────────────────────────────────────────────
    public function getPartners()
    {
        return response()->json(Partner::with('pengguna')->get());
    }

    public function storePartner(Request $request)
    {
        $data = $request->validate([
            'id_pengguna' => 'required|exists:pengguna,id',
            'nama'        => 'required',
            'alamat'      => 'required',
            'lintang'     => 'required|numeric',
            'bujur'       => 'required|numeric',
            'jam_buka'    => 'required',
        ]);
        return response()->json(Partner::create($data), 201);
    }

    public function destroyPartner($id)
    {
        Partner::findOrFail($id)->delete();
        return response()->json(['pesan' => 'Berhasil dihapus']);
    }

    // ── Edukasi ────────────────────────────────────────────────────
    public function getEducations()
    {
        try {
            if (Education::count() === 0) {
                $defaultEdu = [
                    [
                        'judul'      => 'Cara Memilah Sampah yang Benar dari Rumah',
                        'kategori'   => 'Tips',
                        'konten'     => "Memilah sampah sejak dari rumah adalah langkah awal yang sangat penting. Pisahkan sampah organik (sisa makanan, dedaunan) dan anorganik (plastik, kertas, logam). Sampah anorganik yang bersih dan kering memiliki nilai jual lebih tinggi di bank sampah SIRKULO.",
                        'url_gambar' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600&auto=format&fit=crop&q=60',
                    ],
                    [
                        'judul'      => 'Mengenal Kode Daur Ulang Plastik (PET, HDPE, PVC)',
                        'kategori'   => 'Daur Ulang',
                        'konten'     => "Setiap plastik memiliki kode segitiga daur ulang bernomor 1-7. Nomor 1 (PET/PETE) umum pada botol air mineral dan sangat mudah didaur ulang. Nomor 2 (HDPE) terdapat pada botol sampo dan detergen. Pastikan botol dikosongkan dan dibilas sebelum disetorkan.",
                        'url_gambar' => 'https://images.unsplash.com/photo-1605600659908-0ef719419d41?w=600&auto=format&fit=crop&q=60',
                    ],
                    [
                        'judul'      => 'Manfaat Ekonomi dan Lingkungan Bank Sampah',
                        'kategori'   => 'Lingkungan',
                        'konten'     => "Dengan menabung sampah di SIRKULO, Anda tidak hanya menjaga kebersihan lingkungan dan mencegah banjir, tetapi juga mendapatkan penghasilan tambahan dan poin reward yang dapat ditukarkan.",
                        'url_gambar' => 'https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?w=600&auto=format&fit=crop&q=60',
                    ],
                    [
                        'judul'      => 'Tips Mengolah Sampah Organik Menjadi Kompos',
                        'kategori'   => 'Organik',
                        'konten'     => "Sampah dapur seperti sisa sayuran dan buah dapat diolah menjadi pupuk kompos alami yang sangat bernutrisi untuk tanaman di pekarangan rumah Anda menggunakan komposter sederhana.",
                        'url_gambar' => 'https://images.unsplash.com/photo-1584447128309-b66b7a4d1b63?w=600&auto=format&fit=crop&q=60',
                    ],
                ];
                foreach ($defaultEdu as $e) {
                    Education::create($e);
                }
            }
        } catch (\Exception $e) {
            // ignore
        }
        return response()->json(Education::orderBy('dibuat_pada', 'desc')->get());
    }

    public function uploadEducationPhoto(Request $request)
    {
        $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar') ?? $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['galat' => 'File gambar tidak valid atau tidak ditemukan'], 422);
        }
        $path = $file->store('edukasi', 'public');
        $url = url('storage/' . $path);
        return response()->json([
            'url_gambar' => $url,
            'pesan'      => 'Foto edukasi berhasil diunggah'
        ]);
    }

    public function storeEducation(Request $request)
    {
        try {
            $data = $request->validate([
                'judul'      => 'required|string|max:200',
                'kategori'   => 'required|string|max:50',
                'konten'     => 'required|string',
                'url_gambar' => 'nullable|string',
            ]);

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('edukasi', 'public');
                $data['url_gambar'] = url('storage/' . $path);
            }

            if (empty($data['url_gambar'])) {
                $data['url_gambar'] = 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600&auto=format&fit=crop&q=60';
            }

            $item = Education::create($data);
            return response()->json($item, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['galat' => 'Validasi gagal: ' . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()))], 422);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal menambah edukasi: ' . $e->getMessage()], 500);
        }
    }

    public function updateEducation(Request $request, $id)
    {
        try {
            $item = Education::find($id);
            if (!$item) {
                return response()->json(['galat' => 'Artikel edukasi tidak ditemukan'], 404);
            }
            $dataToUpdate = array_filter($request->only('judul', 'kategori', 'konten', 'url_gambar'), fn($val) => !is_null($val));

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('edukasi', 'public');
                $dataToUpdate['url_gambar'] = url('storage/' . $path);
            }

            $item->update($dataToUpdate);
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal mengubah edukasi: ' . $e->getMessage()], 500);
        }
    }

    public function destroyEducation($id)
    {
        try {
            $item = Education::find($id);
            if ($item) {
                $item->delete();
            }
            return response()->json(['pesan' => 'Artikel edukasi berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal menghapus edukasi: ' . $e->getMessage()], 500);
        }
    }

    // ── Notifikasi ────────────────────────────────────────────────
    public function getNotifications(Request $request)
    {
        return response()->json(
            Notification::where('id_pengguna', $request->user()->id)->orderBy('dibuat_pada', 'desc')->get()
        );
    }
}
