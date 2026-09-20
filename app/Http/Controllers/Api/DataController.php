<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrashCategory;
use App\Models\Education;
use App\Models\Partner;
use App\Models\Notification;
use App\Models\Feedback;
use Illuminate\Http\Request;

class DataController extends Controller
{
    // ── Harga Sampah ───────────────────────────────────────────────
    public function getTrashPrices(Request $request)
    {
        try {
            // Auto migration safe check untuk kolom foto_contoh
            if (\Illuminate\Support\Facades\Schema::hasTable('kategori_sampah') && !\Illuminate\Support\Facades\Schema::hasColumn('kategori_sampah', 'foto_contoh')) {
                \Illuminate\Support\Facades\Schema::table('kategori_sampah', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->json('foto_contoh')->nullable()->after('ikon');
                });
            }

            // Pastikan pemisahan 3 tabel (admin, petugas_mitra, nasabah) sudah berjalan
            if (!\Illuminate\Support\Facades\Schema::hasTable('nasabah')) {
                \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            }

            // Hapus route & config cache agar route terbaru aktif
            $routeCache = base_path('bootstrap/cache/routes-v7.php');
            if (file_exists($routeCache)) {
                @unlink($routeCache);
            }
            $configCache = base_path('bootstrap/cache/config.php');
            if (file_exists($configCache)) {
                @unlink($configCache);
            }

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

        // Jika ada id_mitra atau header X-Pos-Id, kembalikan hanya kategori yang diterima pos tersebut
        $posId = $request->input('id_mitra') ?: $request->query('id_mitra') ?: $request->header('X-Pos-Id');
        if (!empty($posId)) {
            $pos = Partner::find($posId);
            if (!$pos) {
                // Coba cari berdasarkan kode_pos (cth "POS-002") atau nama
                $pos = Partner::all()->first(function ($p) use ($posId) {
                    return $p->id === $posId || $p->kode_pos === $posId || strcasecmp($p->nama, $posId) === 0 || stripos($p->nama, $posId) !== false;
                });
            }
            if ($pos) {
                $kategori = $pos->kategoriSampah()->get();
                if ($kategori->isNotEmpty()) {
                    return response()->json($kategori->sortBy('nama')->values());
                }
            }
        }

        return response()->json(TrashCategory::orderBy('nama', 'asc')->get());
    }

    public function uploadTrashPhoto(Request $request)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Hanya Administrator Desa yang dapat mengunggah foto master sampah.'], 403);
        }

        $request->validate([
            'foto' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'photo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'gambar' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar') ?? $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['galat' => 'File gambar tidak valid atau format tidak didukung (Maks 2MB)'], 422);
        }
        $path = $file->store('sampah', 'public');
        $url = $request->getSchemeAndHttpHost() . '/storage/' . $path;
        return response()->json([
            'url_gambar' => $url,
            'pesan'      => 'Foto sampah berhasil diunggah'
        ]);
    }

    public function storeTrashPrice(Request $request)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json([
                'galat' => 'Akses Ditolak: Penetapan master harga sampah resmi adalah wewenang eksklusif Kantor Desa melalui Web Admin.'
            ], 403);
        }

        try {
            $data = $request->validate([
                'nama'           => 'required|string',
                'harga_per_kg'   => 'required|numeric|min:1',
                'harga_pengepul' => 'nullable|numeric|min:1',
                'ikon'           => 'nullable|string',
                'foto_contoh'    => 'nullable',
            ]);
            $data['harga_per_kg'] = (int) $data['harga_per_kg'];
            if (!empty($data['harga_pengepul'])) {
                $data['harga_pengepul'] = (int) $data['harga_pengepul'];
            } else {
                // Default harga jual ke pengepul = harga beli nasabah + 40%
                $data['harga_pengepul'] = (int) max($data['harga_per_kg'] + 500, round($data['harga_per_kg'] * 1.4));
            }

            // Normalisasi foto_contoh jika berupa string JSON / array
            if (!empty($data['foto_contoh'])) {
                if (is_string($data['foto_contoh'])) {
                    $decoded = json_decode($data['foto_contoh'], true);
                    $data['foto_contoh'] = is_array($decoded) ? $decoded : array_filter(explode(',', $data['foto_contoh']));
                }
            } else {
                $data['foto_contoh'] = [];
            }

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('sampah', 'public');
                $uploadedUrl = $request->getSchemeAndHttpHost() . '/storage/' . $path;
                $data['ikon'] = $uploadedUrl;
                if (!in_array($uploadedUrl, $data['foto_contoh'])) {
                    $data['foto_contoh'][] = $uploadedUrl;
                }
            }

            if (empty($data['ikon'])) {
                if (!empty($data['foto_contoh']) && count($data['foto_contoh']) > 0) {
                    $data['ikon'] = $data['foto_contoh'][0];
                } else {
                    $data['ikon'] = 'ic_trash';
                }
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
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json([
                'galat' => 'Akses Ditolak: Perubahan master harga sampah desa hanya dapat diubah oleh Administrator Desa melalui Web Admin.'
            ], 403);
        }

        try {
            $item = TrashCategory::find($id);
            if (!$item) {
                return response()->json(['galat' => 'Kategori sampah tidak ditemukan'], 404);
            }
            $dataToUpdate = array_filter($request->only('nama', 'harga_per_kg', 'harga_pengepul', 'ikon', 'foto_contoh'), fn($val) => !is_null($val));
            if (isset($dataToUpdate['harga_per_kg'])) {
                $dataToUpdate['harga_per_kg'] = (int) $dataToUpdate['harga_per_kg'];
            }
            if (isset($dataToUpdate['harga_pengepul'])) {
                $dataToUpdate['harga_pengepul'] = (int) $dataToUpdate['harga_pengepul'];
            }

            if (isset($dataToUpdate['foto_contoh'])) {
                if (is_string($dataToUpdate['foto_contoh'])) {
                    $decoded = json_decode($dataToUpdate['foto_contoh'], true);
                    $dataToUpdate['foto_contoh'] = is_array($decoded) ? $decoded : array_filter(explode(',', $dataToUpdate['foto_contoh']));
                }
            }

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('sampah', 'public');
                $uploadedUrl = $request->getSchemeAndHttpHost() . '/storage/' . $path;
                $dataToUpdate['ikon'] = $uploadedUrl;
                $currentPhotos = $item->foto_contoh ?? [];
                if (!in_array($uploadedUrl, $currentPhotos)) {
                    $currentPhotos[] = $uploadedUrl;
                    $dataToUpdate['foto_contoh'] = $currentPhotos;
                }
            }

            // Update primary ikon jika foto_contoh diperbarui dan ikon belum diset/perlu diselaraskan
            if (isset($dataToUpdate['foto_contoh']) && is_array($dataToUpdate['foto_contoh']) && count($dataToUpdate['foto_contoh']) > 0) {
                if (empty($dataToUpdate['ikon']) || $dataToUpdate['ikon'] === 'ic_trash') {
                    $dataToUpdate['ikon'] = $dataToUpdate['foto_contoh'][0];
                }
            }

            $item->update($dataToUpdate);
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal mengubah harga sampah: ' . $e->getMessage()], 500);
        }
    }

    public function destroyTrashPrice($id)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json([
                'galat' => 'Akses Ditolak: Penghapusan kategori sampah hanya dapat dilakukan oleh Administrator Desa melalui Web Admin.'
            ], 403);
        }

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
        return response()->json(Partner::with(['pengguna', 'kategoriSampah'])->get());
    }

    public function storePartner(Request $request)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Penambahan pos bank sampah hanya dapat disahkan oleh Administrator Desa melalui Web Admin.'], 403);
        }

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
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Penutupan atau penghapusan pos bank sampah adalah wewenang mutlak Administrator Desa.'], 403);
        }

        Partner::findOrFail($id)->delete();
        return response()->json(['pesan' => 'Pos bank sampah berhasil dihapus']);
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
        $educations = Education::orderBy('dibuat_pada', 'desc')->get();
        // Sertakan atribut computed youtube_id dan has_video agar aplikasi mobile bisa render player
        $educations->each(function ($item) {
            $item->append(['youtube_id', 'has_video']);
        });
        return response()->json($educations);
    }

    public function uploadEducationPhoto(Request $request)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Hanya Administrator Desa yang dapat mengunggah foto edukasi.'], 403);
        }

        $request->validate([
            'foto'   => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'photo'  => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'gambar' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'file'   => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar') ?? $request->file('file');
        if (!$file || !$file->isValid()) {
            return response()->json(['galat' => 'File gambar tidak valid atau format tidak didukung (Maks 2MB)'], 422);
        }
        $path = $file->store('edukasi', 'public');
        $url = $request->getSchemeAndHttpHost() . '/storage/' . $path;
        return response()->json([
            'url_gambar' => $url,
            'pesan'      => 'Foto edukasi berhasil diunggah'
        ]);
    }

    public function storeEducation(Request $request)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Penambahan artikel edukasi adalah wewenang Administrator Desa.'], 403);
        }

        try {
            $data = $request->validate([
                'judul'      => 'required|string|max:200',
                'kategori'   => 'required|string|max:50',
                'konten'     => 'required|string',
                'url_gambar' => 'nullable|string',
                'url_video'  => 'nullable|string|max:500',
            ]);

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('edukasi', 'public');
                $data['url_gambar'] = $request->getSchemeAndHttpHost() . '/storage/' . $path;
            }

            if (empty($data['url_gambar']) && empty($data['url_video'])) {
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
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Pengubahan artikel edukasi adalah wewenang Administrator Desa.'], 403);
        }

        try {
            $item = Education::find($id);
            if (!$item) {
                return response()->json(['galat' => 'Artikel edukasi tidak ditemukan'], 404);
            }
            $dataToUpdate = array_filter($request->only('judul', 'kategori', 'konten', 'url_gambar', 'url_video'), fn($val) => !is_null($val));

            // Jika ada file foto langsung diunggah dalam form
            $file = $request->file('foto') ?? $request->file('photo') ?? $request->file('gambar');
            if ($file && $file->isValid()) {
                $path = $file->store('edukasi', 'public');
                $dataToUpdate['url_gambar'] = $request->getSchemeAndHttpHost() . '/storage/' . $path;
            }

            $item->update($dataToUpdate);
            return response()->json($item);
        } catch (\Exception $e) {
            return response()->json(['galat' => 'Gagal mengubah edukasi: ' . $e->getMessage()], 500);
        }
    }

    public function destroyEducation($id)
    {
        if (auth()->user()?->peran !== 'ADMIN') {
            return response()->json(['galat' => 'Akses Ditolak: Penghapusan artikel edukasi adalah wewenang Administrator Desa.'], 403);
        }

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

    // ── Kritik & Saran (Feedback) ─────────────────────────────────
    private function ensureFeedbackTableExists()
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('kritik_saran')) {
                \Illuminate\Support\Facades\Schema::create('kritik_saran', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->uuid('id')->primary();
                    $table->uuid('id_pengguna');
                    $table->string('kategori');
                    $table->text('pesan');
                    $table->text('jawaban')->nullable();
                    $table->string('status')->default('MENUNGGU');
                    $table->string('pengirim')->default('NASABAH');
                    $table->uuid('id_mitra')->nullable();
                    $table->timestamp('dijawab_pada')->nullable();
                    $table->timestamp('dibuat_pada')->nullable();
                    $table->timestamp('diperbarui_pada')->nullable();

                    $table->foreign('id_pengguna')->references('id')->on('pengguna')->onDelete('cascade');
                });
            } else {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('kritik_saran', 'status')) {
                    \Illuminate\Support\Facades\Schema::table('kritik_saran', function ($table) {
                        $table->string('status')->default('MENUNGGU')->after('jawaban');
                    });
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('kritik_saran', 'pengirim')) {
                    \Illuminate\Support\Facades\Schema::table('kritik_saran', function ($table) {
                        $table->string('pengirim')->default('NASABAH')->after('kategori');
                    });
                }

                // Auto-sync status untuk data yang sudah memiliki jawaban
                \Illuminate\Support\Facades\DB::table('kritik_saran')
                    ->whereNotNull('jawaban')
                    ->where('jawaban', '!=', '')
                    ->where(function ($q) {
                        $q->whereNull('status')->orWhere('status', '!=', 'DIJAWAB');
                    })
                    ->update(['status' => 'DIJAWAB']);
            }
        } catch (\Exception $e) {
            // ignore if already exists or fails gracefully
        }
    }

    public function submitFeedback(Request $request)
    {
        $this->ensureFeedbackTableExists();
        $validated = $request->validate([
            'pesan'    => 'required|string',
            'kategori' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'id_pengguna' => $request->user()->id,
            'kategori'    => $validated['kategori'],
            'pesan'       => $validated['pesan'],
            'status'      => 'MENUNGGU',
            'pengirim'    => 'NASABAH',
        ]);

        return response()->json($feedback->load('pengguna'), 201);
    }

    public function getFeedbacks(Request $request)
    {
        $this->ensureFeedbackTableExists();
        $user = $request->user();

        // Admin dan Super Admin melihat semua feedback
        if (in_array($user->role ?? '', ['ADMIN', 'SUPER_ADMIN'])) {
            $feedbacks = Feedback::with('pengguna')
                ->orderBy('dibuat_pada', 'desc')
                ->get();
        } else {
            // Nasabah melihat feedback miliknya sendiri
            $feedbacks = Feedback::where('id_pengguna', $user->id)
                ->with('pengguna')
                ->orderBy('dibuat_pada', 'desc')
                ->get();
        }

        return response()->json($feedbacks);
    }

    public function getMitraFeedbacks(Request $request)
    {
        $this->ensureFeedbackTableExists();
        // Mitra hanya bisa lihat feedback yang dia kirim sendiri
        $feedbacks = Feedback::with('pengguna')
            ->where('id_pengguna', $request->user()->id)
            ->where('pengirim', 'MITRA')
            ->orderBy('dibuat_pada', 'desc')
            ->get();

        return response()->json($feedbacks);
    }

    public function submitMitraFeedback(Request $request)
    {
        $this->ensureFeedbackTableExists();

        // Pastikan kolom pengirim ada di tabel feedback
        try {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('kritik_saran', 'pengirim')) {
                \Illuminate\Support\Facades\Schema::table('kritik_saran', function ($table) {
                    $table->string('pengirim')->default('NASABAH')->after('kategori');
                });
            }
        } catch (\Exception $e) { /* ignore */ }

        $validated = $request->validate([
            'pesan'    => 'required|string',
            'kategori' => 'required|string',
        ]);

        $feedback = Feedback::create([
            'id_pengguna' => $request->user()->id,
            'kategori'    => $validated['kategori'],
            'pesan'       => $validated['pesan'],
            'pengirim'    => 'MITRA',
        ]);

        return response()->json($feedback->load('pengguna'), 201);
    }

    public function replyFeedback(Request $request, $id)
    {
        $this->ensureFeedbackTableExists();
        $validated = $request->validate([
            'jawaban' => 'required|string',
        ]);

        $feedback = Feedback::find($id);
        if (!$feedback) {
            return response()->json(['galat' => 'Data kritik & saran tidak ditemukan'], 404);
        }

        $feedback->update([
            'jawaban'      => $validated['jawaban'],
            'status'       => 'DIJAWAB',
            'dijawab_pada' => now(),
            'id_mitra'     => $request->user()->id,
        ]);

        // Buat notifikasi untuk pengirim (nasabah atau mitra)
        \App\Http\Controllers\Api\NotificationController::kirim(
            $feedback->id_pengguna,
            'Balasan Kritik & Saran 💬',
            'Admin Desa membalas pesan Anda: "' . \Illuminate\Support\Str::limit($validated['jawaban'], 70) . '"',
            'FEEDBACK'
        );

        return response()->json($feedback->load('pengguna'));
    }
}
