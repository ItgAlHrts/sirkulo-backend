@extends('admin.layout')

@section('title', 'Pos Bank Sampah')
@section('page-title', 'Pos Bank Sampah RW')
@section('page-subtitle', 'Monitoring unit loket penimbangan desa, petugas penanggung jawab, dan jadwal operasional')

@section('content')

{{-- ── SEARCH & HEADER ──────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-4 mb-5 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 shadow-xs">
    <form method="GET" action="{{ route('admin.mitra.index') }}" class="w-full sm:w-auto flex-1 flex items-center gap-2">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama pos atau dusun..."
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
        </div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex-shrink-0">
            Cari
        </button>
        @if(request('q'))
            <a href="{{ route('admin.mitra.index') }}" class="border border-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 transition flex-shrink-0">
                Reset
            </a>
        @endif
    </form>

    <div class="flex items-center gap-2 self-end sm:self-center text-xs">
        <span class="px-3 py-2 rounded-xl bg-slate-100 font-semibold text-slate-600">
            Total: {{ $mitra->total() }} Unit Pos
        </span>
        <button type="button" onclick="openModal('modalTambahPos')"
                class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pos Baru</span>
        </button>
    </div>
</div>

{{-- ── TABEL MITRA & POS ────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <p class="text-xs text-slate-500">
            Daftar <strong>{{ $mitra->total() }}</strong> titik pos loket penimbangan sampah aktif di desa
        </p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3">Kode Pos</th>
                    <th class="px-5 py-3">Nama Pos Loket</th>
                    <th class="px-5 py-3">Pengelola / Petugas</th>
                    <th class="px-5 py-3">Jenis Sampah Diterima</th>
                    <th class="px-5 py-3">Jadwal Operasional</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($mitra as $m)
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-blue-50 text-blue-800 border border-blue-200/60">
                            {{ $m->kode_pos }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <span class="font-semibold text-slate-900 block text-xs">{{ $m->nama }}</span>
                                <span class="text-[10.5px] text-slate-400 font-mono">ID: {{ substr($m->id, 0, 8) }}...</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <p class="font-bold text-slate-800">{{ $m->pengelola }}</p>
                        @if($m->petugas || $m->pengguna)
                            <p class="text-[11px] text-slate-400">Akun: {{ $m->petugas?->nama ?? $m->pengguna?->nama }} &bull; {{ $m->petugas?->telepon ?? ($m->pengguna?->telepon ?? '-') }}</p>
                        @else
                            <span class="text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded text-[10.5px] font-medium border border-amber-200/60 inline-block mt-0.5">
                                Belum Ditautkan Akun
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        @php
                            $cats = $m->kategoriSampah ?? collect();
                        @endphp
                        @if($cats->isEmpty())
                            <span class="text-[10.5px] text-slate-400 italic">Semua jenis</span>
                        @else
                            <div class="flex flex-wrap gap-1">
                                @foreach($cats->take(3) as $cat)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                        {{ $cat->nama }}
                                    </span>
                                @endforeach
                                @if($cats->count() > 3)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9.5px] font-semibold bg-slate-100 text-slate-500">+{{ $cats->count() - 3 }} lagi</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        @php
                            $cleanJadwal = str_replace(['[BUKA] | ', '[TUTUP: '], '', $m->jam_buka ?? '');
                            $isClosed = str_contains($m->jam_buka ?? '', '[TUTUP:');
                        @endphp
                        <div class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $isClosed ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
                            <span class="text-slate-600 truncate max-w-[150px]" title="{{ $cleanJadwal }}">
                                {{ $cleanJadwal ?: '08:00 - 16:00 WIB' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                        <div class="inline-flex items-center gap-1.5">
                            <a href="{{ route('admin.mitra.show', $m->id) }}"
                               class="inline-flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                                <span>Detail</span>
                            </a>
                            <button type="button"
                                    data-id="{{ $m->id }}"
                                    data-nama="{{ $m->nama }}"
                                    data-pengelola="{{ $m->pengelola }}"
                                    data-alamat="{{ $m->alamat }}"
                                    data-jam="{{ $m->jam_buka }}"
                                    data-petugas="{{ $m->id_pengguna ?? '' }}"
                                    data-kategori='@json(($m->kategoriSampah ?? collect())->pluck("id"))'
                                    onclick="bukaModalEditPos(this)"
                                    class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-2.5 py-1 rounded-lg text-xs font-semibold transition">
                                Edit
                            </button>
                            <form method="POST" action="{{ route('admin.mitra.destroy', $m->id) }}" onsubmit="return confirm('Hapus unit pos \'{{ $m->nama }}\'? Akun petugas terkait tetap aman.');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-medium px-1.5 py-1">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-8 text-center text-slate-400">
                        Belum ada pos bank sampah terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($mitra->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
        {{ $mitra->links() }}
    </div>
    @endif
</div>

{{-- ── MODAL TAMBAH POS BARU ────────────────────────────────────────── --}}
<div id="modalTambahPos" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Tambah Unit Pos Baru</h4>
                <p class="text-xs text-slate-500 mt-0.5">Daftarkan titik loket penimbangan sampah baru di RT/RW desa.</p>
            </div>
            <button type="button" onclick="closeModal('modalTambahPos')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.mitra.store') }}" class="space-y-3.5 mt-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pos Loket *</label>
                <input type="text" name="nama" required placeholder="Contoh: Pos 4 - Melati RW 03"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pengelola / Penanggung Jawab Pos</label>
                <input type="text" name="pengelola" placeholder="Contoh: Bpk. Bambang Sutrisno / Ibu Siti"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-[10.5px] text-slate-400 mt-1">Nama ini dicetak pada dokumen dan laporan resmi bank sampah.</p>
            </div>

            {{-- ── WILAYAH & ALAMAT POS (RT, RW, DESA, KECAMATAN) ── --}}
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Wilayah / Alamat Pos *</label>
                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">RT / RW Desa</span>
                </div>
                
                {{-- Baris 1: RT · RW · Desa · Kecamatan --}}
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">RT *</label>
                        <input type="text" id="tambahPosRt" placeholder="01" maxlength="5"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('tambah')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">RW *</label>
                        <input type="text" id="tambahPosRw" placeholder="03" maxlength="5"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('tambah')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Desa / Kel. *</label>
                        <input type="text" id="tambahPosDesa" placeholder="Ploso"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('tambah')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Kecamatan *</label>
                        <input type="text" id="tambahPosKecamatan" placeholder="Jati"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('tambah')">
                    </div>
                </div>

                {{-- Baris 2: Kabupaten / Kota --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Kabupaten / Kota *</label>
                    <input type="text" id="tambahPosKabupaten" placeholder="Contoh: Kudus / Kota Semarang"
                           class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatOtomatis('tambah')">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Titik Lokasi / Patokan Jalan (Opsional)</label>
                    <input type="text" id="tambahPosJalan" placeholder="Contoh: Balai RW 03 / Depan Masjid"
                           class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatOtomatis('tambah')">
                </div>

                {{-- Hidden input alamat yang dikirim ke backend --}}
                <input type="hidden" id="tambahPosAlamat" name="alamat" required value="">
                <div class="text-[11px] text-slate-500 bg-white p-2 rounded-lg border border-slate-200/80 flex items-center gap-1.5">
                    <span class="font-bold text-slate-400">Format:</span>
                    <span id="previewTambahPosAlamat" class="font-medium text-emerald-800 italic truncate">Harap isi RT, RW, Desa, dan Kecamatan</span>
                </div>
            </div>

            {{-- ── JADWAL & JAM OPERASIONAL ── --}}
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Jadwal & Jam Operasional *</label>
                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Pilih Hari & Jam</span>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Hari Operasional *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Dari Hari</label>
                            <select id="tambahPosHariMulai" onchange="updateJamBukaOtomatis('tambah')"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="Senin" selected>Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                                <option value="Setiap Hari">Setiap Hari</option>
                            </select>
                        </div>
                        <div id="wrapperTambahHariSelesai">
                            <label class="block text-[10px] text-slate-400 mb-0.5">Sampai Hari</label>
                            <select id="tambahPosHariSelesai" onchange="updateJamBukaOtomatis('tambah')"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu" selected>Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Jam Operasional *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Jam Buka (Mulai)</label>
                            <input type="time" id="tambahPosJamMulai" value="08:00" onchange="updateJamBukaOtomatis('tambah')"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Jam Tutup (Selesai)</label>
                            <input type="time" id="tambahPosJamSelesai" value="16:00" onchange="updateJamBukaOtomatis('tambah')"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Hidden input jam_buka yang dikirim ke backend --}}
                <input type="hidden" id="tambahPosJamBuka" name="jam_buka" required value="Senin - Sabtu, 08:00 - 16:00 WIB">
                <div class="text-[11px] text-slate-500 bg-white p-2 rounded-lg border border-slate-200/80 flex items-center gap-1.5">
                    <span class="font-bold text-slate-400">Jadwal:</span>
                    <span id="previewTambahPosJamBuka" class="font-medium text-emerald-800 italic truncate">Senin - Sabtu, 08:00 - 16:00 WIB</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Petugas Penanggung Jawab</label>
                <select name="id_pengguna" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Belum Ada Petugas (Tugaskan Nanti) --</option>
                    @foreach($petugasList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Sampah Diterima *</label>
                <p class="text-[10.5px] text-slate-400 mb-2">Pilih jenis sampah yang boleh disetor di pos ini. Kosongkan untuk menerima semua jenis.</p>
                <div class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto border border-slate-200 rounded-xl p-2.5 bg-slate-50">
                    @foreach($kategoriList as $kat)
                        <label class="flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer">
                            <input type="checkbox" name="kategori_ids[]" value="{{ $kat->id }}" class="rounded text-emerald-600 focus:ring-emerald-500">
                            {{ $kat->nama }}
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-2 mt-1.5">
                    <button type="button" onclick="pilihSemuaKategori('modalTambahPos', true)" class="text-[10.5px] text-emerald-600 hover:underline">Pilih Semua</button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="pilihSemuaKategori('modalTambahPos', false)" class="text-[10.5px] text-slate-500 hover:underline">Hapus Semua</button>
                </div>
            </div>
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalTambahPos')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan Pos Baru</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT POS ────────────────────────────────────────────────── --}}
<div id="modalEditPos" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Edit Data Pos Bank Sampah</h4>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui nama pos, alamat RT/RW, jadwal, dan petugas yang bertugas.</p>
            </div>
            <button type="button" onclick="closeModal('modalEditPos')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formEditPos" method="POST" action="" class="space-y-3.5 mt-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pos Loket *</label>
                <input type="text" id="editPosNama" name="nama" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Pengelola / Penanggung Jawab Pos</label>
                <input type="text" id="editPosPengelola" name="pengelola" placeholder="Contoh: Bpk. Bambang Sutrisno / Ibu Siti"
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-[10.5px] text-slate-400 mt-1">Nama ini dicetak pada dokumen dan laporan resmi bank sampah.</p>
            </div>

            {{-- ── WILAYAH & ALAMAT POS (RT, RW, DESA, KECAMATAN, KABUPATEN) ── --}}
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Wilayah / Alamat Pos *</label>
                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">RT / RW · Desa · Kecamatan · Kabupaten</span>
                </div>
                
                {{-- Baris 1: RT · RW · Desa · Kecamatan --}}
                <div class="grid grid-cols-4 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">RT *</label>
                        <input type="text" id="editPosRt" placeholder="01" maxlength="5"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('edit')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">RW *</label>
                        <input type="text" id="editPosRw" placeholder="03" maxlength="5"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('edit')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Desa / Kel. *</label>
                        <input type="text" id="editPosDesa" placeholder="Ploso"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('edit')">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Kecamatan *</label>
                        <input type="text" id="editPosKecamatan" placeholder="Jati"
                               class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                               oninput="updateAlamatOtomatis('edit')">
                    </div>
                </div>

                {{-- Baris 2: Kabupaten / Kota --}}
                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Kabupaten / Kota *</label>
                    <input type="text" id="editPosKabupaten" placeholder="Contoh: Kudus / Kota Semarang"
                           class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatOtomatis('edit')">
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-0.5">Titik Lokasi / Patokan Jalan (Opsional)</label>
                    <input type="text" id="editPosJalan" placeholder="Contoh: Balai Warga / Depan Lapangan RT 01"
                           class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:ring-1 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatOtomatis('edit')">
                </div>

                {{-- Hidden input alamat yang dikirim ke backend --}}
                <input type="hidden" id="editPosAlamat" name="alamat" required value="">
                <div class="text-[11px] text-slate-500 bg-white p-2 rounded-lg border border-slate-200/80 flex items-center gap-1.5">
                    <span class="font-bold text-slate-400">Format:</span>
                    <span id="previewEditPosAlamat" class="font-medium text-emerald-800 italic truncate">—</span>
                </div>
            </div>

            {{-- ── JADWAL & JAM OPERASIONAL ── --}}
            <div class="p-3 bg-slate-50 border border-slate-200/80 rounded-xl space-y-2.5">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold text-slate-700">Jadwal & Jam Operasional *</label>
                    <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Pilih Hari & Jam</span>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Hari Operasional *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Dari Hari</label>
                            <select id="editPosHariMulai" onchange="updateJamBukaOtomatis('edit')"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                                <option value="Setiap Hari">Setiap Hari</option>
                            </select>
                        </div>
                        <div id="wrapperEditHariSelesai">
                            <label class="block text-[10px] text-slate-400 mb-0.5">Sampai Hari</label>
                            <select id="editPosHariSelesai" onchange="updateJamBukaOtomatis('edit')"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                                <option value="Minggu">Minggu</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold text-slate-500 mb-1">Jam Operasional *</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Jam Buka (Mulai)</label>
                            <input type="time" id="editPosJamMulai" value="08:00" onchange="updateJamBukaOtomatis('edit')"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] text-slate-400 mb-0.5">Jam Tutup (Selesai)</label>
                            <input type="time" id="editPosJamSelesai" value="16:00" onchange="updateJamBukaOtomatis('edit')"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs font-medium focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Hidden input jam_buka yang dikirim ke backend --}}
                <input type="hidden" id="editPosJamBuka" name="jam_buka" required value="">
                <div class="text-[11px] text-slate-500 bg-white p-2 rounded-lg border border-slate-200/80 flex items-center gap-1.5">
                    <span class="font-bold text-slate-400">Jadwal:</span>
                    <span id="previewEditPosJamBuka" class="font-medium text-emerald-800 italic truncate">—</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Petugas Penanggung Jawab</label>
                <select id="editPosPetugas" name="id_pengguna" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Belum Ada Petugas --</option>
                    @foreach($petugasList as $p)
                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->email }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Jenis Sampah Diterima *</label>
                <p class="text-[10.5px] text-slate-400 mb-2">Pilih jenis sampah yang boleh disetor di pos ini.</p>
                <div id="editKategoriContainer" class="grid grid-cols-2 gap-1.5 max-h-36 overflow-y-auto border border-slate-200 rounded-xl p-2.5 bg-slate-50">
                    @foreach($kategoriList as $kat)
                        <label class="flex items-center gap-1.5 text-xs text-slate-700 cursor-pointer edit-kat-item">
                            <input type="checkbox" name="kategori_ids[]" value="{{ $kat->id }}" class="rounded text-emerald-600 focus:ring-emerald-500 edit-kat-check">
                            {{ $kat->nama }}
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-2 mt-1.5">
                    <button type="button" onclick="pilihSemuaKategori('modalEditPos', true)" class="text-[10.5px] text-emerald-600 hover:underline">Pilih Semua</button>
                    <span class="text-slate-300">|</span>
                    <button type="button" onclick="pilihSemuaKategori('modalEditPos', false)" class="text-[10.5px] text-slate-500 hover:underline">Hapus Semua</button>
                </div>
            </div>
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditPos')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
        if (id === 'modalTambahPos') {
            updateAlamatOtomatis('tambah');
            updateJamBukaOtomatis('tambah');
        }
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function updateAlamatOtomatis(prefix) {
        const rt = (document.getElementById(prefix + 'PosRt').value || '').trim();
        const rw = (document.getElementById(prefix + 'PosRw').value || '').trim();
        const desa = (document.getElementById(prefix + 'PosDesa').value || '').trim();
        const kec = (document.getElementById(prefix + 'PosKecamatan').value || '').trim();
        const kabEl = document.getElementById(prefix + 'PosKabupaten');
        const kab = kabEl ? (kabEl.value || '').trim() : '';
        const jalan = (document.getElementById(prefix + 'PosJalan').value || '').trim();

        let parts = [];
        if (jalan) parts.push(jalan);

        let rtrw = [];
        if (rt) {
            const cleanRt = rt.replace(/^rt\.?\s*/i, '');
            rtrw.push('RT ' + (cleanRt.length === 1 ? '0' + cleanRt : cleanRt));
        }
        if (rw) {
            const cleanRw = rw.replace(/^rw\.?\s*/i, '');
            rtrw.push('RW ' + (cleanRw.length === 1 ? '0' + cleanRw : cleanRw));
        }
        if (rtrw.length > 0) {
            parts.push(rtrw.join(' / '));
        }

        if (desa) {
            const cleanDesa = desa.replace(/^(desa|kelurahan|kel\.?)\s*/i, '');
            parts.push('Desa ' + cleanDesa);
        }
        if (kec) {
            const cleanKec = kec.replace(/^(kecamatan|kec\.?)\s*/i, '');
            parts.push('Kec. ' + cleanKec);
        }
        if (kab) {
            const cleanKab = kab.replace(/^(kabupaten|kab\.?|kota)\s*/i, '');
            const isKota = /^kota\b/i.test(kab);
            parts.push((isKota ? 'Kota ' : 'Kab. ') + cleanKab);
        }

        const finalAlamat = parts.join(', ');
        document.getElementById(prefix + 'PosAlamat').value = finalAlamat;
        const previewEl = document.getElementById('preview' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'PosAlamat');
        if (previewEl) {
            previewEl.textContent = finalAlamat || 'Harap isi RT, RW, Desa, Kecamatan, Kabupaten';
        }
    }

    function updateJamBukaOtomatis(prefix) {
        const hariMulai = document.getElementById(prefix + 'PosHariMulai').value;
        const hariSelesaiEl = document.getElementById(prefix + 'PosHariSelesai');
        const wrapperSelesai = document.getElementById('wrapper' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'HariSelesai');
        
        let hariStr = '';
        if (hariMulai === 'Setiap Hari') {
            if (wrapperSelesai) wrapperSelesai.classList.add('hidden');
            hariStr = 'Setiap Hari';
        } else {
            if (wrapperSelesai) wrapperSelesai.classList.remove('hidden');
            const hariSelesai = hariSelesaiEl ? hariSelesaiEl.value : 'Sabtu';
            if (hariMulai === hariSelesai) {
                hariStr = hariMulai;
            } else {
                hariStr = hariMulai + ' - ' + hariSelesai;
            }
        }

        const jamMulai = document.getElementById(prefix + 'PosJamMulai').value || '08:00';
        const jamSelesai = document.getElementById(prefix + 'PosJamSelesai').value || '16:00';

        const finalJam = `${hariStr}, ${jamMulai} - ${jamSelesai} WIB`;
        document.getElementById(prefix + 'PosJamBuka').value = finalJam;
        const previewEl = document.getElementById('preview' + prefix.charAt(0).toUpperCase() + prefix.slice(1) + 'PosJamBuka');
        if (previewEl) {
            previewEl.textContent = finalJam;
        }
    }

    function parseAlamatToFields(prefix, alamatStr) {
        let rt = '', rw = '', desa = '', kec = '', kab = '', jalan = '';
        if (alamatStr) {
            // Support format: RT 01 / RW 02 dan RT/RW 01/02
            const rtMatch = alamatStr.match(/\brt[\s\/]*([0-9]+[a-z]?)/i);
            if (rtMatch) rt = rtMatch[1];

            const rwMatch = alamatStr.match(/\brw[\s\/]*([0-9]+[a-z]?)/i);
            if (rwMatch) rw = rwMatch[1];

            const desaMatch = alamatStr.match(/\b(?:desa|kelurahan|kel\.?)\s*([^,()]+)/i);
            if (desaMatch) desa = desaMatch[1].trim();

            const kecMatch = alamatStr.match(/\b(?:kecamatan|kec\.?)\s*([^,()]+)/i);
            if (kecMatch) kec = kecMatch[1].trim();

            const kabMatch = alamatStr.match(/\b(?:kabupaten|kab\.?|kota)\s*([^,()]+)/i);
            if (kabMatch) kab = kabMatch[1].trim();

            // Keterangan dalam kurung → jalan/patokan
            const kurungMatch = alamatStr.match(/\(([^)]+)\)/);
            if (kurungMatch) {
                jalan = kurungMatch[1].trim();
            } else if (!desa && !kec && !rt && !rw && !kab) {
                desa = alamatStr.trim();
            } else {
                const parts = alamatStr.split(',');
                for (let part of parts) {
                    const p = part.trim();
                    if (!p.match(/\brt\b/i) && !p.match(/\brw\b/i) && !p.match(/\bdesa\b/i)
                        && !p.match(/\bkec/i) && !p.match(/\bkab/i) && !p.match(/\bkota\b/i)) {
                        jalan = p;
                        break;
                    }
                }
            }
        }

        document.getElementById(prefix + 'PosRt').value = rt;
        document.getElementById(prefix + 'PosRw').value = rw;
        document.getElementById(prefix + 'PosDesa').value = desa;
        document.getElementById(prefix + 'PosKecamatan').value = kec;
        const kabEl = document.getElementById(prefix + 'PosKabupaten');
        if (kabEl) kabEl.value = kab;
        document.getElementById(prefix + 'PosJalan').value = jalan;

        updateAlamatOtomatis(prefix);
    }

    function parseJamBukaToFields(prefix, jamStr) {
        let hariMulai = 'Senin';
        let hariSelesai = 'Sabtu';
        let jamMulai = '08:00';
        let jamSelesai = '16:00';

        if (jamStr) {
            const timeMatch = jamStr.match(/(\d{1,2}[:.]\d{2})\s*[-–]\s*(\d{1,2}[:.]\d{2})/);
            if (timeMatch) {
                let jm = timeMatch[1].replace('.', ':');
                if (jm.length === 4) jm = '0' + jm;
                let js = timeMatch[2].replace('.', ':');
                if (js.length === 4) js = '0' + js;
                jamMulai = jm;
                jamSelesai = js;
            }

            if (/setiap hari/i.test(jamStr)) {
                hariMulai = 'Setiap Hari';
            } else {
                const dayRangeMatch = jamStr.match(/([a-zA-Z]+)\s*[-–]\s*([a-zA-Z]+)/);
                if (dayRangeMatch) {
                    const hm = capitalizeFirst(dayRangeMatch[1]);
                    const hs = capitalizeFirst(dayRangeMatch[2]);
                    const validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    if (validDays.includes(hm)) hariMulai = hm;
                    if (validDays.includes(hs)) hariSelesai = hs;
                } else {
                    const singleDayMatch = jamStr.match(/\b(Senin|Selasa|Rabu|Kamis|Jumat|Sabtu|Minggu)\b/i);
                    if (singleDayMatch) {
                        const d = capitalizeFirst(singleDayMatch[1]);
                        hariMulai = d;
                        hariSelesai = d;
                    }
                }
            }
        }

        document.getElementById(prefix + 'PosHariMulai').value = hariMulai;
        const selEl = document.getElementById(prefix + 'PosHariSelesai');
        if (selEl) selEl.value = hariSelesai;
        document.getElementById(prefix + 'PosJamMulai').value = jamMulai;
        document.getElementById(prefix + 'PosJamSelesai').value = jamSelesai;

        updateJamBukaOtomatis(prefix);
    }

    function capitalizeFirst(str) {
        if (!str) return '';
        return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
    }

    function bukaModalEditPos(btn) {
        const id = btn.getAttribute('data-id');
        const nama = btn.getAttribute('data-nama');
        const pengelola = btn.getAttribute('data-pengelola');
        const alamat = btn.getAttribute('data-alamat');
        const jamBuka = btn.getAttribute('data-jam');
        const idPengguna = btn.getAttribute('data-petugas');
        let selectedIds = [];
        try {
            selectedIds = JSON.parse(btn.getAttribute('data-kategori') || '[]');
        } catch (e) {
            selectedIds = [];
        }

        document.getElementById('editPosNama').value = nama || '';
        document.getElementById('editPosPengelola').value = pengelola || '';
        document.getElementById('editPosPetugas').value = idPengguna || '';
        document.getElementById('formEditPos').action = '/admin/mitra/' + id;

        parseAlamatToFields('edit', alamat || '');
        parseJamBukaToFields('edit', jamBuka || '');

        // Pre-check kategori sampah yang sudah dikonfigurasi untuk pos ini
        document.querySelectorAll('.edit-kat-check').forEach(function(cb) {
            cb.checked = selectedIds.includes(cb.value);
        });

        openModal('modalEditPos');
    }

    function pilihSemuaKategori(modalId, check) {
        document.querySelectorAll('#' + modalId + ' input[type="checkbox"][name="kategori_ids[]"]').forEach(function(cb) {
            cb.checked = check;
        });
    }

    // Inisialisasi awal saat load
    document.addEventListener('DOMContentLoaded', function() {
        updateAlamatOtomatis('tambah');
        updateJamBukaOtomatis('tambah');
    });
</script>
@endpush
