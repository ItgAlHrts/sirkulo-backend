@extends('admin.layout')

@section('title', 'Katalog & Harga Sampah')
@section('page-title', 'Master Harga Sampah')
@section('page-subtitle', 'Penetapan harga beli tabungan dari warga, harga jual ke pengepul, dan galeri foto komoditas')

@section('content')

{{-- ── PEDOMAN PENETAPAN HARGA DESA ───────────────────────────────── --}}
<div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-emerald-50/50 border border-emerald-200/80 rounded-3xl p-5 mb-6 text-sm text-emerald-950 flex items-start gap-4 shadow-xs">
    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center flex-shrink-0 shadow-xs">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <div class="flex-1">
        <h3 class="font-display font-bold text-base text-emerald-950">Pedoman Kebijakan Tarif BUMDes & Bank Sampah:</h3>
        <p class="text-xs text-emerald-800/90 mt-1 leading-relaxed">
            • <strong>Harga Beli Warga (/kg)</strong>: Nilai rupiah yang otomatis masuk ke saldo rekening nasabah saat sampah disetor di loket pos.<br>
            • <strong>Harga Jual Pengepul (/kg)</strong>: Nilai kesepakatan jual desa ke industri daur ulang. Selisih margin otomatis menjadi kas operasional pos & desa.<br>
            • <strong>Galeri Multi-Foto</strong>: Anda dapat mengunggah <strong>beberapa foto sekaligus</strong> untuk setiap jenis sampah sebagai panduan visual warga dan petugas pos loket.
        </p>
    </div>
</div>

{{-- ── FORM TAMBAH KATEGORI SAMPAH BARU ─────────────────────────────── --}}
<div class="bg-white rounded-3xl p-6 shadow-xs border border-slate-200/80 mb-6">
    <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
        <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900">Tambah Komoditas Sampah Baru</h3>
            <p class="text-xs text-slate-500">Daftarkan jenis sampah baru beserta beberapa foto contoh yang diterima loket bank sampah</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.harga-sampah.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-start">
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Kategori Sampah *</label>
                <input type="text" name="nama" required placeholder="Contoh: Kardus Tebal / Botol Kaca Bening"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Harga Beli Nasabah (Rp/kg) *</label>
                <input type="number" name="harga_per_kg" required min="0" step="1" placeholder="Contoh: 2000"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Harga Jual Pengepul (Rp/kg)</label>
                <input type="number" name="harga_pengepul" min="0" step="1" placeholder="Contoh: 2500"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Foto Sampah (Bisa Beberapa)</label>
                <input type="file" name="foto[]" id="inputTambahFoto" multiple accept="image/*" onchange="previewTambahImages(this)"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2 py-2 text-xs focus:bg-white focus:outline-none file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[11px] file:font-semibold file:bg-emerald-50 file:text-emerald-700">
                <p class="text-[10px] text-slate-400 mt-1">Pilih 1 atau beberapa foto sekaligus. Klik X untuk batalkan.</p>
            </div>
        </div>

        {{-- Live Preview Multi-Foto Tambah --}}
        <div id="previewTambahContainer" class="hidden pt-2 border-t border-slate-100">
            <p class="text-[11px] font-bold text-slate-500 mb-2">Pratinjau Foto yang Dipilih:</p>
            <div id="previewTambahGrid" class="flex flex-wrap gap-2.5"></div>
        </div>

        <div class="flex justify-end pt-2 border-t border-slate-100">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 px-6 rounded-2xl text-xs sm:text-sm transition-all shadow-xs hover:-translate-y-0.5">
                + Tambah Kategori Sampah
            </button>
        </div>
    </form>
</div>

{{-- ── TABEL KATALOG HARGA SAMPAH ───────────────────────────────────── --}}
<div class="bg-white rounded-3xl shadow-xs border border-slate-200/80 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <div>
            <h3 class="font-display font-bold text-sm text-slate-800">Daftar Kategori Sampah Terdaftar ({{ count($kategori) }})</h3>
            <p class="text-xs text-slate-400 mt-0.5">Klik tombol <em>Edit</em> untuk menambah/mengelola beberapa foto dan tarif komoditas</p>
        </div>
    </div>

    <div class="table-responsive">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-6 py-3.5">Kategori & Galeri Foto</th>
                    <th class="px-6 py-3.5 text-right">Harga Beli Warga (/kg)</th>
                    <th class="px-6 py-3.5 text-right">Harga Jual Pengepul (/kg)</th>
                    <th class="px-6 py-3.5 text-right">Margin Kas Desa</th>
                    <th class="px-6 py-3.5 text-center">Aksi Pengelola</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($kategori as $k)
                @php
                    $fotoList = $k->foto_contoh ?? [];
                    $totalFoto = count($fotoList);
                    $hasFoto = !empty($k->ikon) && (str_starts_with($k->ikon, 'http') || str_starts_with($k->ikon, '/storage') || str_starts_with($k->ikon, 'storage/'));
                @endphp
                <tr class="hover:bg-slate-50/60 transition-colors">
                    {{-- Kategori & Foto --}}
                    <td class="px-6 py-4">
                        <form id="form-{{ $k->id }}" method="POST" action="{{ route('admin.harga-sampah.update', $k->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                        </form>

                        <div class="flex items-center gap-3">
                            {{-- Thumbnail Foto Utama dengan Badge Jumlah Foto --}}
                            <div class="relative flex-shrink-0 cursor-pointer" onclick="bukaModalEditSampah('{{ $k->id }}')">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-emerald-100 to-teal-50 border border-emerald-200/60 overflow-hidden relative shadow-2xs">
                                    @if($hasFoto)
                                        <img src="{{ $k->ikon }}" alt="{{ $k->nama }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-6 h-6 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Badge Jumlah Foto jika lebih dari 1 --}}
                                @if($totalFoto > 1)
                                    <span class="absolute -bottom-1 -right-1 bg-slate-900 text-white text-[9.5px] font-bold px-1.5 py-0.2 rounded-full border border-white shadow-xs" title="Terdapat {{ $totalFoto }} foto komoditas">
                                        {{ $totalFoto }}
                                    </span>
                                @endif
                            </div>

                            <div>
                                <input type="text" name="nama" form="form-{{ $k->id }}" value="{{ $k->nama }}" required
                                       class="font-display font-bold text-slate-900 text-sm bg-transparent border-b border-transparent hover:border-slate-300 focus:border-emerald-500 focus:outline-none px-1 py-0.5">
                                <div class="flex items-center gap-2 px-1 mt-0.5">
                                    <span class="text-[11px] font-mono text-slate-400">ID: {{ substr($k->id, 0, 8) }}...</span>
                                    <span class="text-[10.5px] font-medium text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">
                                        {{ $totalFoto }} foto
                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Harga Beli Nasabah --}}
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-2xl bg-slate-50 border border-slate-200">
                            <span class="text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="harga_per_kg" form="form-{{ $k->id }}" value="{{ $k->harga_per_kg }}" required min="0" step="1"
                                   class="w-24 text-right font-display font-bold text-slate-900 bg-transparent text-sm focus:outline-none">
                        </div>
                    </td>

                    {{-- Harga Jual Pengepul --}}
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-2xl bg-blue-50/50 border border-blue-200">
                            <span class="text-xs font-bold text-blue-400">Rp</span>
                            <input type="number" name="harga_pengepul" form="form-{{ $k->id }}" value="{{ $k->harga_pengepul ?? $k->harga_jual }}" min="0" step="1"
                                   class="w-24 text-right font-display font-bold text-blue-800 bg-transparent text-sm focus:outline-none">
                        </div>
                    </td>

                    {{-- Margin Kas Desa --}}
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-50 border border-emerald-200 text-emerald-800 font-display font-bold text-xs rounded-xl">
                            + Rp {{ number_format($k->margin_per_kg, 0, ',', '.') }}
                            <span class="text-[10px] text-emerald-600 font-sans font-semibold">({{ $k->persentase_margin }}%)</span>
                        </span>
                    </td>

                    {{-- Aksi --}}
                    <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            {{-- Simpan Inline --}}
                            <button type="submit" form="form-{{ $k->id }}" title="Simpan perubahan baris ini"
                                    class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-xl text-xs transition-all shadow-xs hover:-translate-y-0.5">
                                Simpan
                            </button>

                            {{-- Buka Modal Edit Lengkap (Foto & Data) --}}
                            <button type="button" onclick="bukaModalEditSampah('{{ $k->id }}')"
                                    title="Kelola foto galeri, nama, dan harga sampah"
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1.5 rounded-xl text-xs transition-all border border-slate-200/80">
                                Edit & Galeri
                            </button>

                            {{-- Hapus --}}
                            <form method="POST" action="{{ route('admin.harga-sampah.destroy', $k->id) }}" onsubmit="return confirm('Yakin ingin menghapus kategori sampah \'{{ $k->nama }}\'?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-bold hover:underline px-1.5 py-1">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-slate-400 text-sm">Belum ada kategori sampah terdaftar. Tambahkan di atas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── MODAL EDIT KOMODITAS & GALERI MULTI-FOTO ───────────────────────── --}}
<div id="modalEditSampah" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-xl p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm sm:text-base">Edit Komoditas & Galeri Foto</h4>
                <p class="text-xs text-slate-500 mt-0.5">Kelola foto-foto sampel sampah, nama, dan tarif per kilogram.</p>
            </div>
            <button type="button" onclick="closeModal('modalEditSampah')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formModalEditSampah" method="POST" action="" enctype="multipart/form-data" class="space-y-4 mt-4">
            @csrf
            @method('PUT')

            {{-- Hidden input foto utama --}}
            <input type="hidden" id="modalInputFotoUtama" name="foto_utama" value="">

            {{-- 1. Galeri Foto Saat Ini --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-bold text-slate-700">Foto Komoditas yang Terdaftar</label>
                    <span id="modalJumlahFotoBadge" class="text-[11px] font-semibold text-slate-500">0 foto</span>
                </div>

                <div id="modalGaleriGrid" class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 p-3 bg-slate-50 rounded-2xl border border-slate-200/80 min-h-[90px]">
                    {{-- Render dinamis via JS --}}
                </div>
                <p class="text-[10.5px] text-slate-400 mt-1">Klik tombol silang (x) pada foto untuk menghapusnya saat disimpan.</p>
            </div>

            {{-- 2. Tambah Beberapa Foto Baru Sekaligus --}}
            <div class="p-3.5 bg-emerald-50/50 rounded-2xl border border-emerald-200/60">
                <label class="block text-xs font-bold text-emerald-950 mb-1">Tambah Foto Baru (Bisa Pilih Beberapa Sekaligus)</label>
                <p class="text-[11px] text-emerald-800/80 mb-2">Anda dapat memilih lebih dari satu foto secara bersamaan (JPG, PNG, WebP max 2MB per file).</p>
                <input type="file" name="foto[]" id="modalInputMultiFoto" multiple accept="image/*" onchange="previewModalMultiFoto(this)"
                       class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 cursor-pointer">

                {{-- Preview Multi-Foto Baru --}}
                <div id="modalPreviewBaruContainer" class="hidden mt-3 pt-2.5 border-t border-emerald-200/60">
                    <p class="text-[11px] font-bold text-emerald-900 mb-1.5">Pratinjau Foto Baru yang Akan Ditambahkan:</p>
                    <div id="modalPreviewBaruGrid" class="flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- 3. Nama Kategori --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori Sampah *</label>
                <input type="text" id="modalEditNama" name="nama" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>

            {{-- 4. Harga Beli dan Jual --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Beli Warga (Rp/kg) *</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" id="modalEditHargaBeli" name="harga_per_kg" required min="0" step="1"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold focus:bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Pengepul (Rp/kg)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-xs font-bold text-blue-400">Rp</span>
                        <input type="number" id="modalEditHargaJual" name="harga_pengepul" min="0" step="1"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3.5 py-2.5 text-xs sm:text-sm font-semibold text-blue-800 focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditSampah')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Data seluruh kategori sampah untuk JavaScript
    const kategoriData = @json($kategori);

    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // ── Helper: Rebuild FileList dari array File (untuk hapus individual dari input) ──
    function buildFileList(files) {
        const dt = new DataTransfer();
        files.forEach(f => dt.items.add(f));
        return dt.files;
    }

    // ── Preview Multi-Foto saat Tambah Komoditas Baru ───────────────────
    let tambahFiles = [];

    function previewTambahImages(input) {
        tambahFiles = Array.from(input.files);
        renderTambahPreview();
    }

    function renderTambahPreview() {
        const container = document.getElementById('previewTambahContainer');
        const grid      = document.getElementById('previewTambahGrid');
        grid.innerHTML  = '';

        if (tambahFiles.length > 0) {
            container.classList.remove('hidden');
            tambahFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.className = 'relative w-16 h-16 rounded-xl overflow-hidden border border-emerald-300 shadow-2xs group flex-shrink-0';
                    card.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover">
                        <span class="absolute bottom-0 inset-x-0 bg-slate-900/60 text-white text-[8px] text-center py-0.5 truncate px-1">${file.name}</span>
                        <button type="button"
                            onclick="hapusTambahFile(${index})"
                            title="Hapus foto ini dari pilihan"
                            class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow transition-all opacity-0 group-hover:opacity-100 z-10">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    `;
                    grid.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
        } else {
            container.classList.add('hidden');
        }
    }

    function hapusTambahFile(index) {
        tambahFiles.splice(index, 1);
        const input = document.getElementById('inputTambahFoto');
        if (input && typeof DataTransfer !== 'undefined') {
            input.files = buildFileList(tambahFiles);
        }
        renderTambahPreview();
    }

    // ── Buka Modal Edit Sampah & Render Galeri Multi-Foto ───────────────
    function bukaModalEditSampah(id) {
        const item = kategoriData.find(k => k.id === id);
        if (!item) return;

        document.getElementById('formModalEditSampah').action = '/admin/harga-sampah/' + id;
        document.getElementById('modalEditNama').value = item.nama;
        document.getElementById('modalEditHargaBeli').value = item.harga_per_kg;
        document.getElementById('modalEditHargaJual').value = item.harga_pengepul || item.harga_jual || '';
        document.getElementById('modalInputMultiFoto').value = '';
        document.getElementById('modalInputFotoUtama').value = item.ikon || '';

        // Reset preview foto baru
        modalFiles = [];
        document.getElementById('modalPreviewBaruContainer').classList.add('hidden');
        document.getElementById('modalPreviewBaruGrid').innerHTML = '';

        // Render Galeri Foto Eksisting
        renderModalGaleri(item);

        openModal('modalEditSampah');
    }

    function renderModalGaleri(item) {
        const grid  = document.getElementById('modalGaleriGrid');
        const badge = document.getElementById('modalJumlahFotoBadge');
        grid.innerHTML = '';

        let photos = item.foto_contoh || [];
        if (!Array.isArray(photos)) {
            photos = photos ? [photos] : [];
        }

        if (photos.length === 0 && item.ikon && (item.ikon.startsWith('http') || item.ikon.startsWith('/storage') || item.ikon.startsWith('storage/'))) {
            photos = [item.ikon];
        }

        badge.innerText = photos.length + ' foto terdaftar';

        if (photos.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full py-4 text-center text-xs text-slate-400">
                    Belum ada foto yang diunggah untuk komoditas ini. Tambahkan di bawah.
                </div>
            `;
            return;
        }

        photos.forEach((url, index) => {
            const isUtama = (item.ikon === url) || (index === 0 && !item.ikon);
            const card = document.createElement('div');
            card.id        = 'galeri-card-' + index;
            card.className = 'relative rounded-xl overflow-hidden border ' + (isUtama ? 'border-emerald-500 ring-2 ring-emerald-400/50' : 'border-slate-200') + ' bg-white shadow-2xs group flex flex-col items-center justify-center aspect-square';

            card.innerHTML = `
                <img src="${url}" class="w-full h-full object-cover">

                ${isUtama ? '<span class="absolute top-1 left-1 bg-emerald-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded shadow-xs z-10">Utama</span>' : ''}

                {{-- Tombol X selalu terlihat di pojok kanan atas --}}
                <button type="button" onclick="tandaiHapusFoto('${url}', 'galeri-card-${index}')"
                    title="Hapus foto ini dari galeri"
                    class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-sm transition-all z-10 opacity-70 hover:opacity-100">
                    <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>

                <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-end gap-1 p-1.5 pt-6">
                    ${!isUtama ? `
                    <button type="button" onclick="setFotoUtama('${url}')" class="bg-white/90 hover:bg-white text-slate-800 text-[9px] font-bold px-1.5 py-0.5 rounded shadow-xs w-full text-center">
                        Jadikan Utama
                    </button>` : ''}
                </div>
            `;
            grid.appendChild(card);
        });
    }

    function setFotoUtama(url) {
        document.getElementById('modalInputFotoUtama').value = url;
        const grid  = document.getElementById('modalGaleriGrid');
        const cards = grid.querySelectorAll('div[id^="galeri-card-"]');
        cards.forEach(c => {
            const img = c.querySelector('img');
            if (img && img.src === url) {
                c.className = 'relative rounded-xl overflow-hidden border border-emerald-500 ring-2 ring-emerald-400/50 bg-white shadow-2xs group flex flex-col items-center justify-center aspect-square';
            } else {
                c.className = 'relative rounded-xl overflow-hidden border border-slate-200 bg-white shadow-2xs group flex flex-col items-center justify-center aspect-square';
            }
        });
    }

    function tandaiHapusFoto(url, cardId) {
        const card = document.getElementById(cardId);
        if (!card) return;

        // Cegah double-click hapus yang sama
        const existing = document.querySelector(`#formModalEditSampah input[name="hapus_foto[]"][value="${CSS.escape(url)}"]`);
        if (existing) return;

        const form        = document.getElementById('formModalEditSampah');
        const hiddenInput = document.createElement('input');
        hiddenInput.type  = 'hidden';
        hiddenInput.name  = 'hapus_foto[]';
        hiddenInput.value = url;
        form.appendChild(hiddenInput);

        // Visual: foto redup + overlay "Dihapus"
        card.style.opacity       = '0.25';
        card.style.pointerEvents = 'none';
        const overlay = document.createElement('div');
        overlay.className = 'absolute inset-0 flex items-center justify-center bg-rose-900/70 rounded-xl';
        overlay.innerHTML = '<span class="text-white text-[10px] font-bold">Dihapus</span>';
        card.appendChild(overlay);
    }

    // ── Pratinjau Multi-Foto Baru pada Modal Edit ────────────────────────
    let modalFiles = [];

    function previewModalMultiFoto(input) {
        modalFiles = Array.from(input.files);
        renderModalPreview();
    }

    function renderModalPreview() {
        const container = document.getElementById('modalPreviewBaruContainer');
        const grid      = document.getElementById('modalPreviewBaruGrid');
        grid.innerHTML  = '';

        if (modalFiles.length > 0) {
            container.classList.remove('hidden');
            modalFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const card = document.createElement('div');
                    card.className = 'relative w-14 h-14 rounded-xl overflow-hidden border border-emerald-400 shadow-2xs group flex-shrink-0';
                    card.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-full object-cover" title="${file.name}">
                        <button type="button"
                            onclick="hapusModalFile(${index})"
                            title="Hapus foto ini"
                            class="absolute top-0.5 right-0.5 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow transition-all opacity-0 group-hover:opacity-100 z-10">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    `;
                    grid.appendChild(card);
                };
                reader.readAsDataURL(file);
            });
        } else {
            container.classList.add('hidden');
        }
    }

    function hapusModalFile(index) {
        modalFiles.splice(index, 1);
        const input = document.getElementById('modalInputMultiFoto');
        if (input && typeof DataTransfer !== 'undefined') {
            input.files = buildFileList(modalFiles);
        }
        renderModalPreview();
    }
</script>
@endpush
