@extends('admin.layout')

@section('title', 'Edukasi Warga')
@section('header', 'Edukasi Daur Ulang Warga')

@section('content')
<div class="space-y-6">

    {{-- ── HEADER ACTION ─────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 rounded-2xl shadow-sm border border-slate-200/80">
        <div>
            <h3 class="text-lg font-bold text-slate-800">Katalog Artikel & Video Edukasi</h3>
            <p class="text-xs text-slate-500 mt-1">Artikel dan video ini akan tampil secara real-time di layar utama aplikasi warga desa.</p>
        </div>
        <button onclick="document.getElementById('modalTambah').classList.remove('hidden')"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl flex items-center gap-2 shadow-sm transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Konten Baru
        </button>
    </div>

    {{-- ── CARDS GRID ─────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($edukasi as $item)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden flex flex-col justify-between hover:shadow-md transition group">
            <div>
                {{-- Media: Video YouTube atau Foto Sampul --}}
                <div class="relative bg-slate-100 overflow-hidden" style="aspect-ratio:16/9">
                    @if($item->has_video)
                        {{-- Foto sampul otomatis diambil dari video YouTube --}}
                        <div class="relative w-full h-full">
                            <img src="https://img.youtube.com/vi/{{ $item->youtube_id }}/hqdefault.jpg" alt="{{ $item->judul }}" class="w-full h-full object-cover absolute inset-0 group-hover:scale-105 transition-transform duration-300">
                            <div class="absolute inset-0 bg-black/25 flex items-center justify-center pointer-events-none">
                                <div class="w-11 h-11 rounded-full bg-rose-600/95 text-white flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                        </div>
                    @else
                        <img src="{{ $item->url_gambar }}" alt="{{ $item->judul }}" class="w-full h-full object-cover absolute inset-0 group-hover:scale-105 transition-transform duration-300">
                    @endif

                    {{-- Badge Kategori --}}
                    <span class="absolute top-2.5 left-2.5 bg-white/90 backdrop-blur px-2.5 py-1 rounded-full text-xs font-bold text-emerald-700 shadow-sm z-10">
                        {{ $item->kategori }}
                    </span>

                    {{-- Badge Video jika ada --}}
                    @if($item->has_video)
                        <span class="absolute top-2.5 right-2.5 bg-rose-600 text-white px-2 py-0.5 rounded-full text-[10px] font-bold flex items-center gap-1 shadow-sm z-10">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/>
                            </svg>
                            YouTube
                        </span>
                    @endif
                </div>

                {{-- Konten Teks --}}
                <div class="p-5">
                    <h4 class="font-bold text-slate-800 text-sm leading-snug line-clamp-2 mb-2">{{ $item->judul }}</h4>
                    <p class="text-xs text-slate-500 line-clamp-3 leading-relaxed">{{ $item->konten }}</p>
                </div>
            </div>

            {{-- Footer Aksi --}}
            <div class="px-5 py-3 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <span class="text-[11px] text-slate-400">
                    {{ \Carbon\Carbon::parse($item->dibuat_pada)->translatedFormat('d M Y') }}
                </span>
                <div class="flex items-center gap-1.5">
                    <button type="button"
                        onclick="bukaModalEdit(this)"
                        data-id="{{ $item->id }}"
                        data-judul="{{ $item->judul }}"
                        data-kategori="{{ $item->kategori }}"
                        data-konten="{{ $item->konten }}"
                        data-url-video="{{ $item->url_video ?? '' }}"
                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Edit">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form action="{{ route('admin.edukasi.destroy', $item->id) }}" method="POST"
                          onsubmit="return confirm('Hapus konten edukasi ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white p-12 text-center rounded-2xl border border-slate-100 text-slate-400">
            Belum ada konten edukasi. Klik tombol di atas untuk menambah yang pertama.
        </div>
        @endforelse
    </div>
</div>

{{-- ── MODAL TAMBAH ──────────────────────────────────────────────────────── --}}
<div id="modalTambah" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 pt-5 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Tambah Konten Edukasi</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form action="{{ route('admin.edukasi.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Judul *</label>
                <input type="text" name="judul" required placeholder="Contoh: Cara Memilah Sampah Plastik Rumah Tangga"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50 focus:bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                <select name="kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                    <option value="Tips">Tips</option>
                    <option value="Daur Ulang">Daur Ulang</option>
                    <option value="Lingkungan">Lingkungan</option>
                    <option value="Organik">Organik</option>
                    <option value="Video">Video</option>
                </select>
            </div>

            {{-- Toggle media: foto atau YouTube --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Media Konten</label>
                <div class="flex items-center gap-3 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_media" value="foto" checked onchange="toggleMedia(this.value, 'tambah')"
                               class="accent-emerald-600">
                        <span class="text-xs font-medium text-slate-700">Foto Sampul</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="jenis_media" value="video" onchange="toggleMedia(this.value, 'tambah')"
                               class="accent-rose-600">
                        <span class="text-xs font-medium text-slate-700">Video YouTube</span>
                    </label>
                </div>

                {{-- Input Foto --}}
                <div id="mediaTambahFoto">
                    <label class="block text-xs text-slate-500 mb-1">Foto Sampul (JPG/PNG/WebP, Maks 2MB)</label>
                    <input type="file" name="foto" accept="image/*"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700">
                </div>

                {{-- Input YouTube --}}
                <div id="mediaTambahVideo" class="hidden">
                    <label class="block text-xs text-slate-500 mb-1">URL Video YouTube *</label>
                    <input type="url" name="url_video" placeholder="Contoh: https://youtu.be/dQw4w9WgXcQ atau https://www.youtube.com/watch?v=..."
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50 focus:bg-white">
                    <div class="mt-2 p-2.5 bg-emerald-50 rounded-xl border border-emerald-200/80 flex items-center gap-2 text-emerald-800 text-xs">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>Otomatis:</strong> Foto sampul diambil langsung dari video YouTube, tidak perlu upload foto manual.</span>
                    </div>
                    <div id="previewYoutubeTambah" class="hidden mt-2 rounded-xl overflow-hidden border border-slate-200" style="aspect-ratio:16/9">
                        <iframe id="iframeTambah" src="" class="w-full h-full border-0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Konten / Deskripsi *</label>
                <textarea name="konten" rows="4" required placeholder="Tuliskan penjelasan edukasi di sini..."
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50 focus:bg-white"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                    Terbitkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT ────────────────────────────────────────────────────────── --}}
<div id="modalEdit" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
    <div class="bg-white rounded-2xl max-w-xl w-full shadow-xl max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center px-6 pt-5 pb-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-800">Edit Konten Edukasi</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formEdit" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Judul *</label>
                <input type="text" id="editJudul" name="judul" required
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50 focus:bg-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori *</label>
                <select id="editKategori" name="kategori" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-white">
                    <option value="Tips">Tips</option>
                    <option value="Daur Ulang">Daur Ulang</option>
                    <option value="Lingkungan">Lingkungan</option>
                    <option value="Organik">Organik</option>
                    <option value="Video">Video</option>
                </select>
            </div>

            {{-- Toggle media edit --}}
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Media Konten</label>
                <div class="flex items-center gap-3 mb-3">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" id="editMediaFotoRadio" name="jenis_media_edit" value="foto" checked onchange="toggleMedia(this.value, 'edit')"
                               class="accent-emerald-600">
                        <span class="text-xs font-medium text-slate-700">Foto Sampul</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" id="editMediaVideoRadio" name="jenis_media_edit" value="video" onchange="toggleMedia(this.value, 'edit')"
                               class="accent-rose-600">
                        <span class="text-xs font-medium text-slate-700">Video YouTube</span>
                    </label>
                </div>

                {{-- Input Foto Edit --}}
                <div id="mediaEditFoto">
                    <label class="block text-xs text-slate-500 mb-1">Ganti Foto Sampul (Kosongkan jika tidak diganti)</label>
                    <input type="file" name="foto" accept="image/*"
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700">
                </div>

                {{-- Input YouTube Edit --}}
                <div id="mediaEditVideo" class="hidden">
                    <label class="block text-xs text-slate-500 mb-1">URL Video YouTube *</label>
                    <input type="url" id="editUrlVideo" name="url_video"
                           placeholder="Contoh: https://youtu.be/... atau https://youtube.com/watch?v=..."
                           oninput="previewYoutube(this.value, 'edit')"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-rose-500 focus:outline-none bg-slate-50 focus:bg-white">
                    <div class="mt-2 p-2.5 bg-emerald-50 rounded-xl border border-emerald-200/80 flex items-center gap-2 text-emerald-800 text-xs">
                        <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span><strong>Otomatis:</strong> Foto sampul artikel otomatis memakai thumbnail resmi dari video YouTube ini.</span>
                    </div>
                    <div id="previewYoutubeEdit" class="hidden mt-2 rounded-xl overflow-hidden border border-slate-200" style="aspect-ratio:16/9">
                        <iframe id="iframeEdit" src="" class="w-full h-full border-0" allowfullscreen></iframe>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Isi Konten / Deskripsi *</label>
                <textarea id="editKonten" name="konten" rows="4" required
                          class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50 focus:bg-white"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleMedia(val, ctx) {
        if (ctx === 'tambah') {
            document.getElementById('mediaTambahFoto').classList.toggle('hidden', val !== 'foto');
            document.getElementById('mediaTambahVideo').classList.toggle('hidden', val !== 'video');
        } else {
            document.getElementById('mediaEditFoto').classList.toggle('hidden', val !== 'foto');
            document.getElementById('mediaEditVideo').classList.toggle('hidden', val !== 'video');
        }
    }

    function extractYoutubeId(url) {
        if (!url) return null;
        const m = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        return m ? m[1] : null;
    }

    function previewYoutube(url, ctx) {
        const vid = extractYoutubeId(url);
        const suffix = ctx === 'edit' ? 'Edit' : 'Tambah';
        const preview = document.getElementById('previewYoutube' + suffix);
        const iframe  = document.getElementById('iframe' + suffix);
        if (preview && iframe) {
            if (vid) {
                iframe.src = 'https://www.youtube.com/embed/' + vid + '?rel=0&modestbranding=1';
                preview.classList.remove('hidden');
            } else {
                iframe.src = '';
                preview.classList.add('hidden');
            }
        }
    }

    // Live preview saat mengetik URL YouTube di form Tambah
    document.addEventListener('DOMContentLoaded', function() {
        const inputTambahVideo = document.querySelector('#mediaTambahVideo input[name="url_video"]');
        if (inputTambahVideo) {
            inputTambahVideo.addEventListener('input', function() {
                previewYoutube(this.value, 'tambah');
            });
        }
    });

    function bukaModalEdit(btn) {
        const id = btn.getAttribute('data-id');
        const judul = btn.getAttribute('data-judul');
        const kategori = btn.getAttribute('data-kategori');
        const konten = btn.getAttribute('data-konten');
        const urlVideo = btn.getAttribute('data-url-video');

        document.getElementById('formEdit').action = '/admin/edukasi/' + id;
        document.getElementById('editJudul').value   = judul || '';
        document.getElementById('editKategori').value = kategori || 'Tips';
        document.getElementById('editKonten').value  = konten || '';
        document.getElementById('editUrlVideo').value = urlVideo || '';

        // Tentukan toggle media
        if (urlVideo && urlVideo.trim() !== '') {
            document.getElementById('editMediaVideoRadio').checked = true;
            toggleMedia('video', 'edit');
            previewYoutube(urlVideo, 'edit');
        } else {
            document.getElementById('editMediaFotoRadio').checked = true;
            toggleMedia('foto', 'edit');
            previewYoutube('', 'edit');
        }

        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
@endsection
