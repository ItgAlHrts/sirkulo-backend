@extends('admin.layout')

@section('title', 'Kelola Petugas Pos Lapangan')
@section('page-title', 'Petugas Pos Lapangan')
@section('page-subtitle', 'Manajemen akun mandiri petugas operasional pos bank sampah desa')

@section('content')

{{-- ── STATISTIK RINGKAS ─────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Petugas</p>
            <p class="text-xl font-bold text-slate-900 leading-tight">{{ $mitras->count() }} <span class="text-xs font-normal text-slate-500">Orang</span></p>
        </div>
    </div>

    @php
        $terhubungCount = $mitras->filter(fn($m) => $m->mitra !== null)->count();
        $belumTerhubungCount = $mitras->filter(fn($m) => $m->mitra === null)->count();
    @endphp

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Ditugaskan di Pos</p>
            <p class="text-xl font-bold text-slate-900 leading-tight">{{ $terhubungCount }} <span class="text-xs font-normal text-slate-500">Petugas</span></p>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center gap-3.5">
        <div class="w-11 h-11 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Belum Ada Pos</p>
            <p class="text-xl font-bold text-slate-900 leading-tight">{{ $belumTerhubungCount }} <span class="text-xs font-normal text-slate-500">Petugas</span></p>
        </div>
    </div>
</div>

{{-- ── HEADER & ACTION BUTTONS ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-5 mb-6 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
    <div>
        <h3 class="font-display font-bold text-lg text-slate-900">Daftar Petugas Pos Lapangan</h3>
        <p class="text-xs text-slate-500 mt-0.5">Petugas lapangan mengoperasikan timbangan, transaksi penimbangan, dan tabungan sampah warga di unit pos.</p>
    </div>
    
    <div class="flex items-center gap-2.5 self-end sm:self-center">
        <button type="button" onclick="openModal('modalTambahMitra')"
                class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold transition-all shadow-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            <span>Tambah Petugas Mitra</span>
        </button>
    </div>
</div>

{{-- ── TABEL PETUGAS MITRA & POS ───────────────────────────────────────── --}}
<div class="space-y-4">
    <div class="bg-white rounded-2xl border border-slate-200/80 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-3.5">Petugas Lapangan</th>
                        <th class="px-6 py-3.5">Unit Pos Ditugaskan</th>
                        <th class="px-6 py-3.5">Kontak</th>
                        <th class="px-6 py-3.5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($mitras as $m)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        {{-- Petugas --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-900 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                    {{ strtoupper(substr($m->nama, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-xs sm:text-sm truncate">{{ $m->nama }}</p>
                                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $m->email }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Pos --}}
                        <td class="px-6 py-4">
                            @if($m->mitra)
                                <div class="flex items-start justify-between gap-2 max-w-xs">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <p class="font-bold text-slate-800 text-xs sm:text-sm">{{ $m->mitra->nama }}</p>
                                        </div>
                                        <p class="text-xs text-slate-400 truncate mt-0.5">{{ $m->mitra->alamat ?: '—' }}</p>
                                    </div>
                                    <button type="button" onclick="bukaModalTautkanPos('{{ $m->id }}', '{{ addslashes($m->nama) }}', '{{ $m->mitra->id }}')"
                                            class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 hover:underline flex-shrink-0 pt-0.5">
                                        Ubah Pos
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-600 font-medium text-[11px] rounded-md border border-slate-200">
                                        Belum Ditugaskan
                                    </span>
                                    <button type="button" onclick="bukaModalTautkanPos('{{ $m->id }}', '{{ addslashes($m->nama) }}', '')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        <span>Tautkan Pos</span>
                                    </button>
                                </div>
                            @endif
                        </td>

                        {{-- Kontak --}}
                        <td class="px-6 py-4 text-xs text-slate-600 whitespace-nowrap">
                            <span class="font-mono">{{ $m->telepon ?: '—' }}</span>
                        </td>

                        {{-- Aksi --}}
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Tombol Ganti Sandi oleh Admin --}}
                                <button type="button" onclick="bukaModalGantiSandi('{{ $m->id }}', '{{ addslashes($m->nama) }}', '{{ addslashes($m->email) }}', 'Petugas Mitra')"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200/70 transition-colors">
                                    Ganti Sandi
                                </button>

                                {{-- Edit Profil Petugas --}}
                                <button type="button" onclick="bukaModalEditMitra('{{ $m->id }}', '{{ addslashes($m->nama) }}', '{{ addslashes($m->email) }}', '{{ addslashes($m->telepon) }}')"
                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200/70 transition-colors">
                                    Edit
                                </button>

                                {{-- Hapus Petugas --}}
                                <form method="POST" action="{{ route('admin.petugas.destroy', $m->id) }}"
                                      onsubmit="return confirm('Hapus akun petugas {{ $m->nama }}? Unit pos fisik dan data transaksi tetap tersimpan aman.');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 rounded-lg text-xs font-semibold text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200/80 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-xs text-slate-400">
                            Belum ada akun petugas mitra pos lapangan. Klik tombol "Tambah Petugas Mitra" di atas untuk menambahkan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── MODAL: TAMBAH PETUGAS MITRA ─────────────────────────────────────── --}}
<div id="modalTambahMitra" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 shadow-xl border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Tambah Akun Petugas Mitra</h4>
                <p class="text-xs text-slate-500 mt-0.5">Petugas memiliki akun login mandiri dan dapat ditugaskan ke unit pos.</p>
            </div>
            <button type="button" onclick="closeModal('modalTambahMitra')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form method="POST" action="{{ route('admin.petugas.store') }}" class="space-y-4 mt-4">
            @csrf
            
            {{-- Bagian 1: Data Akun Petugas --}}
            <div>
                <h5 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">1. Informasi Akun Petugas</h5>
                <div class="space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Nama Petugas *</label>
                            <input type="text" name="nama" required placeholder="Contoh: Pak Joko"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">No. HP / WhatsApp *</label>
                            <input type="text" name="telepon" required placeholder="08123456789"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Email Login HP *</label>
                            <input type="email" name="email" required placeholder="petugas@sirkulo.id"
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-600 mb-1">Password Baru *</label>
                            <div class="relative">
                                <input type="password" id="inputTambahMitraPassword" name="kata_sandi" required minlength="6" placeholder="Min. 6 karakter"
                                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <button type="button" onclick="togglePasswordVisibility('inputTambahMitraPassword')"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bagian 2: Penugasan Pos --}}
            <div class="pt-2 border-t border-slate-100">
                <h5 class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">2. Penugasan Pos Bank Sampah</h5>
                
                <div class="space-y-2 mb-3">
                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="opsi_pos" value="existing" checked onchange="toggleFormPos(this.value)"
                               class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-medium">Tautkan ke Pos yang sudah ada di desa (Disarankan)</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="opsi_pos" value="new" onchange="toggleFormPos(this.value)"
                               class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-medium">Buat unit pos baru sekaligus</span>
                    </label>

                    <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <input type="radio" name="opsi_pos" value="none" onchange="toggleFormPos(this.value)"
                               class="text-emerald-600 focus:ring-emerald-500">
                        <span class="text-xs text-slate-700 font-medium">Hanya buat akun petugas (Tautkan pos nanti)</span>
                    </label>
                </div>

                {{-- Opsi Pos yang Ada --}}
                <div id="sectionPosExisting" class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                    <label class="block text-xs font-bold text-slate-700">Pilih Unit Pos Bank Sampah</label>
                    <select name="pos_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">-- Pilih Pos --</option>
                        @foreach($allPos as $pos)
                            <option value="{{ $pos->id }}">
                                {{ $pos->nama }} ({{ $pos->alamat ?: 'Tanpa alamat' }}) 
                                @if($pos->petugas) - Petugas saat ini: {{ $pos->petugas->nama }} @else - (Belum ada petugas) @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Opsi Pos Baru --}}
                <div id="sectionPosNew" class="hidden p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pos Bank Sampah</label>
                        <input type="text" name="nama_pos" placeholder="Contoh: Pos 4 - RW 05"
                               class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Pos</label>
                            <input type="text" name="alamat_pos" placeholder="Jl. Melati No. 12"
                                   class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jam Operasional</label>
                            <input type="text" name="jam_buka" value="Senin - Sabtu, 08:00 - 15:00 WIB"
                                   class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalTambahMitra')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs">Simpan Akun Petugas</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: TAUTKAN / PINDAH POS ────────────────────────────────────── --}}
<div id="modalTautkanPos" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Atur Penugasan Pos</h4>
                <p class="text-xs text-slate-500 mt-0.5">Petugas: <strong id="namaPetugasTautkan" class="text-slate-800"></strong></p>
            </div>
            <button type="button" onclick="closeModal('modalTautkanPos')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formTautkanPos" method="POST" action="" class="space-y-4 mt-4">
            @csrf
            @method('PUT')

            <div class="space-y-2">
                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="opsi" value="existing" checked onchange="toggleFormTautkan(this.value)"
                           class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs text-slate-700 font-medium">Pilih dari unit pos yang ada</span>
                </label>

                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="opsi" value="new" onchange="toggleFormTautkan(this.value)"
                           class="text-emerald-600 focus:ring-emerald-500">
                    <span class="text-xs text-slate-700 font-medium">Buat unit pos baru untuk petugas ini</span>
                </label>

                <label class="flex items-center gap-2.5 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                    <input type="radio" name="opsi" value="unlink" onchange="toggleFormTautkan(this.value)"
                           class="text-rose-600 focus:ring-rose-500">
                    <span class="text-xs text-rose-700 font-medium">Lepas penugasan pos (Petugas tanpa pos)</span>
                </label>
            </div>

            {{-- Opsi Pos Ada --}}
            <div id="sectionTautkanExisting" class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                <label class="block text-xs font-bold text-slate-700">Pilih Pos Bank Sampah</label>
                <select id="selectTautkanPosId" name="pos_id" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Pilih Pos --</option>
                    @foreach($allPos as $pos)
                        <option value="{{ $pos->id }}">
                            {{ $pos->nama }} ({{ $pos->alamat ?: 'Tanpa alamat' }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Opsi Pos Baru --}}
            <div id="sectionTautkanNew" class="hidden p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pos Baru</label>
                    <input type="text" name="nama_pos" placeholder="Contoh: Pos Baru Dusun Krajan"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Pos</label>
                    <input type="text" name="alamat_pos" placeholder="Jl. Melati No. 10"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jam Operasional</label>
                    <input type="text" name="jam_buka" value="Senin - Sabtu, 08:00 - 15:00 WIB"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalTautkanPos')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Simpan Penugasan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: EDIT PROFIL MITRA ───────────────────────────────────────── --}}
<div id="modalEditMitra" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Edit Profil Petugas Mitra</h4>
                <p class="text-xs text-slate-500 mt-0.5">Perbarui informasi data diri petugas lapangan.</p>
            </div>
            <button type="button" onclick="closeModal('modalEditMitra')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formEditMitra" method="POST" action="" class="space-y-3.5 mt-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Nama Petugas *</label>
                <input type="text" id="editMitraNama" name="nama" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Email Login *</label>
                <input type="email" id="editMitraEmail" name="email" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">No. HP / WhatsApp *</label>
                <input type="text" id="editMitraTelepon" name="telepon" required
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalEditMitra')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: GANTI PASSWORD PETUGAS ──────────────────────────────────── --}}
<div id="modalGantiSandi" class="fixed inset-0 z-50 hidden bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm p-6 shadow-xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
            <div>
                <h4 class="font-bold text-slate-900 text-sm">Ganti Password Akun</h4>
                <p id="targetPeranSandi" class="text-xs text-emerald-600 font-semibold mt-0.5">Petugas Mitra</p>
            </div>
            <button type="button" onclick="closeModal('modalGantiSandi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none">&times;</button>
        </div>

        <form id="formGantiSandi" method="POST" action="" class="space-y-3.5 mt-4">
            @csrf
            @method('PUT')
            
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80">
                <p class="text-[11px] text-slate-400">Akun yang akan diubah:</p>
                <p class="font-bold text-slate-800 text-xs sm:text-sm" id="targetNamaSandi"></p>
                <p class="text-xs text-slate-500 font-mono" id="targetEmailSandi"></p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Password Baru *</label>
                <div class="relative">
                    <input type="password" id="inputPasswordBaru" name="password_baru" required minlength="6" placeholder="Minimal 6 karakter"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-9 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <button type="button" onclick="togglePasswordVisibility('inputPasswordBaru')"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Berikan password baru ini langsung kepada petugas untuk login di aplikasi petugas.</p>
            </div>

            <div class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalGantiSandi')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Simpan Password</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    function bukaModalGantiSandi(id, nama, email, peran) {
        document.getElementById('targetNamaSandi').innerText = nama;
        document.getElementById('targetEmailSandi').innerText = email;
        document.getElementById('targetPeranSandi').innerText = peran;
        document.getElementById('formGantiSandi').action = '/admin/petugas/' + id + '/password';
        document.getElementById('inputPasswordBaru').value = '';
        openModal('modalGantiSandi');
    }

    function bukaModalEditMitra(id, nama, email, telepon) {
        document.getElementById('editMitraNama').value = nama;
        document.getElementById('editMitraEmail').value = email;
        document.getElementById('editMitraTelepon').value = telepon;
        document.getElementById('formEditMitra').action = '/admin/petugas/' + id;
        openModal('modalEditMitra');
    }

    function bukaModalTautkanPos(id, nama, currentPosId) {
        document.getElementById('namaPetugasTautkan').innerText = nama;
        document.getElementById('formTautkanPos').action = '/admin/petugas/' + id + '/pos';
        const select = document.getElementById('selectTautkanPosId');
        if (select) {
            select.value = currentPosId || '';
        }
        openModal('modalTautkanPos');
    }

    function toggleFormPos(val) {
        const secExisting = document.getElementById('sectionPosExisting');
        const secNew = document.getElementById('sectionPosNew');

        if (val === 'existing') {
            secExisting.classList.remove('hidden');
            secNew.classList.add('hidden');
        } else if (val === 'new') {
            secExisting.classList.add('hidden');
            secNew.classList.remove('hidden');
        } else {
            secExisting.classList.add('hidden');
            secNew.classList.add('hidden');
        }
    }

    function toggleFormTautkan(val) {
        const secExisting = document.getElementById('sectionTautkanExisting');
        const secNew = document.getElementById('sectionTautkanNew');

        if (val === 'existing') {
            secExisting.classList.remove('hidden');
            secNew.classList.add('hidden');
        } else if (val === 'new') {
            secExisting.classList.add('hidden');
            secNew.classList.remove('hidden');
        } else {
            secExisting.classList.add('hidden');
            secNew.classList.add('hidden');
        }
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.type = (input.type === 'password') ? 'text' : 'password';
    }
</script>
@endpush
