<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Education;
use Illuminate\Http\Request;

class AdminEducationController extends Controller
{
    public function index()
    {
        $edukasi = Education::orderBy('dibuat_pada', 'desc')->get();
        return view('admin.edukasi.index', compact('edukasi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'     => 'required|string|max:200',
            'kategori'  => 'required|string|max:50',
            'konten'    => 'required|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'url_video' => 'nullable|string|max:500',
        ]);

        $urlGambar = null;
        if ($request->hasFile('foto') && $request->input('jenis_media') !== 'video') {
            $path = $request->file('foto')->store('edukasi', 'public');
            $urlGambar = url('storage/' . $path);
        }

        $urlVideo = $request->url_video ?: null;
        if ($request->input('jenis_media') === 'foto') {
            $urlVideo = null;
        }

        $edu = Education::create([
            'judul'      => $request->judul,
            'kategori'   => $request->kategori,
            'konten'     => $request->konten,
            'url_gambar' => $urlGambar,
            'url_video'  => $urlVideo,
            'dibuat_pada'=> now(),
        ]);

        \App\Services\AuditLogger::log(
            'TAMBAH_EDUKASI',
            "Menerbitkan konten edukasi baru '{$edu->judul}' (Kategori: {$edu->kategori}).",
            'EDUKASI',
            'CREATE',
            'SUKSES',
            ['id' => $edu->id, 'judul' => $edu->judul, 'kategori' => $edu->kategori]
        );

        return redirect()->route('admin.edukasi.index')->with('success', 'Artikel edukasi warga berhasil diterbitkan.');
    }

    public function update(Request $request, string $id)
    {
        $edu = Education::findOrFail($id);

        $request->validate([
            'judul'     => 'required|string|max:200',
            'kategori'  => 'required|string|max:50',
            'konten'    => 'required|string',
            'foto'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'url_video' => 'nullable|string|max:500',
        ]);

        $urlVideo = $request->url_video ?: null;
        if ($request->input('jenis_media_edit') === 'foto') {
            $urlVideo = null;
        }

        $data = [
            'judul'    => $request->judul,
            'kategori' => $request->kategori,
            'konten'   => $request->konten,
            'url_video'=> $urlVideo,
        ];

        if ($request->input('jenis_media_edit') === 'video' && !empty($urlVideo)) {
            // Jika konten video, foto sampul otomatis dari thumbnail YouTube
            $data['url_gambar'] = null;
        } elseif ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('edukasi', 'public');
            $data['url_gambar'] = url('storage/' . $path);
        }

        $edu->update($data);

        \App\Services\AuditLogger::log(
            'UPDATE_EDUKASI',
            "Memperbarui artikel edukasi warga '{$edu->judul}'.",
            'EDUKASI',
            'UPDATE',
            'SUKSES',
            ['id' => $edu->id, 'judul' => $edu->judul, 'kategori' => $edu->kategori]
        );

        return redirect()->route('admin.edukasi.index')->with('success', 'Artikel edukasi berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $edu = Education::findOrFail($id);
        $judul = $edu->judul;
        $edu->delete();

        \App\Services\AuditLogger::log(
            'HAPUS_EDUKASI',
            "Menghapus artikel edukasi warga '{$judul}'.",
            'EDUKASI',
            'DELETE',
            'PERINGATAN',
            ['id' => $id, 'judul' => $judul]
        );

        return redirect()->route('admin.edukasi.index')->with('success', 'Artikel edukasi berhasil dihapus.');
    }
}
