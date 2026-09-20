@extends('admin.layout')

@section('title', 'Log Aktivitas & Audit Trail')
@section('page-title', 'Log Aktivitas & Audit Trail')
@section('page-subtitle', 'Rekam jejak forensik seluruh operasi sistem, keamanan, transaksi, dan perubahan data desa')

@section('content')

{{-- ── STATISTIK AUDIT TRAIL ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Aktivitas Hari Ini</p>
            <p class="text-2xl font-black text-slate-800 font-display">{{ number_format($statsHariIni) }}</p>
            <span class="text-[10.5px] text-emerald-600 font-medium">Rekaman hari ini</span>
        </div>
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Minggu Ini</p>
            <p class="text-2xl font-black text-slate-800 font-display">{{ number_format($statsMingguIni) }}</p>
            <span class="text-[10.5px] text-blue-600 font-medium">Sejak awal minggu</span>
        </div>
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Keamanan & Sesi</p>
            <p class="text-2xl font-black text-slate-800 font-display">{{ number_format($statsSecurityAuth) }}</p>
            <span class="text-[10.5px] text-purple-600 font-medium">Login, logout & sandi</span>
        </div>
        <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div>
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1">Mutasi Data Entitas</p>
            <p class="text-2xl font-black text-slate-800 font-display">{{ number_format($statsPerubahanData) }}</p>
            <span class="text-[10.5px] text-amber-600 font-medium">Tambah, ubah, hapus</span>
        </div>
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
        </div>
    </div>
</div>

{{-- ── FILTER & ACTION HEADER ────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-4 mb-5 border border-slate-200 shadow-xs">
    <form method="GET" action="{{ route('admin.aktivitas.index') }}" class="flex flex-col gap-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            {{-- Input Cari --}}
            <div class="lg:col-span-2 relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Cari narasi, admin, aksi, IP, atau ID log..."
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
            </div>

            {{-- Filter Modul --}}
            <div>
                <select name="kategori"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="SEMUA">Semua Modul / Kategori</option>
                    @foreach($daftarKategori as $kode => $label)
                        <option value="{{ $kode }}" {{ request('kategori') === $kode ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Tipe Operasi --}}
            <div>
                <select name="tipe"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="SEMUA">Semua Tipe Operasi</option>
                    <option value="CREATE" {{ request('tipe') === 'CREATE' ? 'selected' : '' }}>CREATE (Penambahan)</option>
                    <option value="UPDATE" {{ request('tipe') === 'UPDATE' ? 'selected' : '' }}>UPDATE (Perubahan)</option>
                    <option value="DELETE" {{ request('tipe') === 'DELETE' ? 'selected' : '' }}>DELETE (Penghapusan)</option>
                    <option value="AUTH" {{ request('tipe') === 'AUTH' ? 'selected' : '' }}>AUTH (Sesi Login/Out)</option>
                    <option value="SECURITY" {{ request('tipe') === 'SECURITY' ? 'selected' : '' }}>SECURITY (Hak Akses/Sandi)</option>
                    <option value="TRANSACTION" {{ request('tipe') === 'TRANSACTION' ? 'selected' : '' }}>TRANSACTION (Finansial)</option>
                    <option value="EXPORT" {{ request('tipe') === 'EXPORT' ? 'selected' : '' }}>EXPORT (Unduhan Berkas)</option>
                </select>
            </div>

            {{-- Filter Periode Waktu --}}
            <div>
                <select name="periode"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs sm:text-sm text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <option value="">Semua Rentang Waktu</option>
                    <option value="hari_ini" {{ request('periode') === 'hari_ini' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="minggu_ini" {{ request('periode') === 'minggu_ini' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="bulan_ini" {{ request('periode') === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                </select>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-100">
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-xs transition">
                    Terapkan Filter
                </button>
                @if(request()->hasAny(['q', 'kategori', 'tipe', 'status', 'periode']))
                    <a href="{{ route('admin.aktivitas.index') }}" class="border border-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs font-medium hover:bg-slate-50 transition">
                        Reset Filter
                    </a>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.aktivitas.exportCsv', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 bg-slate-900 hover:bg-slate-800 text-white px-3.5 py-2 rounded-xl text-xs font-semibold shadow-xs transition"
                   title="Unduh seluruh log audit yang sesuai filter saat ini">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Unduh Log Audit (CSV)</span>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ── TABEL LOG AKTIVITAS ───────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <p class="text-xs text-slate-500">
            Menampilkan <strong>{{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}</strong> dari <strong>{{ $logs->total() }}</strong> rekaman aktivitas audit
            @if(request('q')) dengan kata kunci "<strong>{{ request('q') }}</strong>" @endif
        </p>
        <span class="text-[11px] text-slate-400">Sinkronisasi Realtime (WIB)</span>
    </div>

    <div class="table-responsive">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-4 py-3">Waktu Eksekusi</th>
                    <th class="px-4 py-3">Pelaku / Staf</th>
                    <th class="px-4 py-3">Modul & Tipe</th>
                    <th class="px-4 py-3">Kode Aksi</th>
                    <th class="px-4 py-3">Uraian Keterangan</th>
                    <th class="px-4 py-3">IP & Perangkat</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Detail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($logs as $log)
                @php
                    $kategori = $log->kategori ?? 'UMUM';
                    $tipe = $log->tipe ?? 'ACTION';
                    $status = $log->status ?? 'SUKSES';

                    // Warna Badge Modul
                    $kategoriColor = match($kategori) {
                        'AUTH'         => 'bg-purple-50 text-purple-700 border-purple-200',
                        'NASABAH'      => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'POS_MITRA'    => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                        'PENGGUNA'     => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'HARGA_SAMPAH' => 'bg-amber-50 text-amber-800 border-amber-200',
                        'TRANSAKSI'    => 'bg-teal-50 text-teal-700 border-teal-200',
                        'LAPORAN'      => 'bg-blue-50 text-blue-700 border-blue-200',
                        'WHATSAPP'     => 'bg-green-50 text-green-700 border-green-200',
                        'EDUKASI'      => 'bg-lime-50 text-lime-800 border-lime-200',
                        'FEEDBACK'     => 'bg-rose-50 text-rose-700 border-rose-200',
                        'SISTEM'       => 'bg-slate-100 text-slate-700 border-slate-300',
                        default        => 'bg-slate-50 text-slate-600 border-slate-200',
                    };

                    // Warna Badge Tipe
                    $tipeColor = match($tipe) {
                        'CREATE'      => 'bg-emerald-100 text-emerald-800',
                        'UPDATE'      => 'bg-amber-100 text-amber-800',
                        'DELETE'      => 'bg-rose-100 text-rose-800',
                        'AUTH'        => 'bg-purple-100 text-purple-800',
                        'SECURITY'    => 'bg-red-100 text-red-800',
                        'TRANSACTION' => 'bg-teal-100 text-teal-800',
                        'EXPORT'      => 'bg-blue-100 text-blue-800',
                        default       => 'bg-slate-100 text-slate-700',
                    };

                    // Info Perangkat & Ikon
                    $deviceNama = $log->nama_perangkat ?? 'Perangkat Komputer / Laptop';
                    $deviceTipe = $log->tipe_perangkat ?? 'DESKTOP';
                    $deviceIkon = match($deviceTipe) {
                        'MOBILE', 'APP' => '📱',
                        'TABLET'        => '📱',
                        'SYSTEM'        => '⚙️',
                        default         => '💻',
                    };
                @endphp
                <tr class="hover:bg-slate-50/80 transition">
                    {{-- Waktu --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="font-mono text-slate-800 font-semibold block text-[11px]">
                            {{ \Carbon\Carbon::parse($log->dibuat_pada)->setTimezone('Asia/Jakarta')->format('d/m/Y H:i:s') }}
                        </span>
                        <span class="text-[10px] text-slate-400">
                            {{ \Carbon\Carbon::parse($log->dibuat_pada)->setTimezone('Asia/Jakarta')->diffForHumans() }}
                        </span>
                    </td>

                    {{-- Pelaku --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($log->nama_admin ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <span class="font-semibold text-slate-800 block text-xs">{{ $log->nama_admin ?? 'Sistem' }}</span>
                                <span class="text-[10px] font-semibold text-slate-400 block">{{ $log->peran ?? 'ADMIN' }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Modul & Tipe --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] font-semibold border {{ $kategoriColor }} mb-1">
                            {{ $log->kategori ?? 'UMUM' }}
                        </span>
                        <div>
                            <span class="inline-block px-1.5 py-0.2 rounded text-[9.5px] font-bold {{ $tipeColor }}">
                                {{ $tipe }}
                            </span>
                        </div>
                    </td>

                    {{-- Kode Aksi --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <code class="text-[11px] font-mono font-bold text-slate-700 bg-slate-100 px-1.5 py-0.5 rounded">
                            {{ $log->aksi }}
                        </code>
                    </td>

                    {{-- Uraian Keterangan & Rincian Perubahan --}}
                    <td class="px-4 py-3 max-w-md">
                        {{-- Tag Khusus Event Keamanan / Login --}}
                        @if($log->aksi === 'LOGIN_ADMIN')
                            <div class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                🔑 Berhasil Masuk (Login Web)
                            </div>
                        @elseif($log->aksi === 'GAGAL_LOGIN_ADMIN')
                            <div class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full mb-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                ⚠️ Percobaan Login Ditolak (Gagal)
                            </div>
                        @elseif($log->aksi === 'LOGOUT_ADMIN')
                            <div class="inline-flex items-center gap-1.5 text-[10.5px] font-bold text-slate-600 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-full mb-1">
                                🚪 Keluar Sesi (Logout)
                            </div>
                        @endif

                        {{-- Narasi Utama --}}
                        <p class="text-slate-800 text-xs leading-relaxed font-medium">{{ $log->keterangan }}</p>

                        {{-- Box Komparasi Perubahan (Sebelum ➔ Sesudah) jika ada --}}
                        @if(!empty($log->daftar_perubahan))
                            <div class="mt-2 p-2 bg-amber-50/80 border border-amber-200 rounded-xl text-xs space-y-1">
                                <div class="flex items-center gap-1 font-bold text-amber-900 text-[10.5px] uppercase tracking-wider">
                                    <svg class="w-3.5 h-3.5 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    <span>Perubahan Data:</span>
                                </div>
                                <div class="space-y-1">
                                    @foreach($log->daftar_perubahan as $item)
                                        <div class="flex flex-wrap items-center gap-1.5 leading-snug text-[11px]">
                                            <span class="font-semibold text-slate-700">• {{ $item['label'] ?? $item['field'] }}:</span>
                                            <span class="bg-red-50 text-red-700 border border-red-200 px-1.5 py-0.2 rounded font-mono text-[10px] line-through">{{ $item['sebelum'] }}</span>
                                            <span class="text-slate-400 font-bold text-xs">➔</span>
                                            <span class="bg-emerald-50 text-emerald-800 border border-emerald-300 font-bold px-1.5 py-0.2 rounded font-mono text-[10px]">{{ $item['sesudah'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </td>

                    {{-- IP & Nama Perangkat --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5 font-semibold text-slate-800 text-xs mb-1">
                            <span>{{ $deviceIkon }}</span>
                            <span class="truncate max-w-[170px]" title="{{ $deviceNama }}">{{ $deviceNama }}</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1 font-mono text-[10.5px] bg-slate-100 border border-slate-200 text-slate-700 px-2 py-0.5 rounded-lg font-medium">
                                🌐 IP: {{ $log->ip_address ?: '127.0.0.1' }}
                            </span>
                        </div>
                    </td>

                    {{-- Status --}}
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        @if($status === 'SUKSES')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <svg class="w-3 h-3 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                Sukses
                            </span>
                        @elseif($status === 'GAGAL')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <svg class="w-3 h-3 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                                Gagal
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10.5px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                Peringatan
                            </span>
                        @endif
                    </td>

                    {{-- Tombol Detail Forensik --}}
                    <td class="px-4 py-3 whitespace-nowrap text-center">
                        <button type="button"
                                onclick="bukaModalDetailLog({{ json_encode($log) }})"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition"
                                title="Lihat Rincian Forensik Log">
                            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Rincian</span>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="font-medium text-slate-500">Tidak ada catatan audit yang cocok dengan filter.</p>
                            <a href="{{ route('admin.aktivitas.index') }}" class="text-xs text-emerald-600 hover:underline">Reset semua filter pencarian</a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
        {{ $logs->links() }}
    </div>
    @endif
</div>

{{-- ── MODAL DETAIL AUDIT FORENSIK ───────────────────────────────────── --}}
<div id="modalDetailLog" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in duration-150">
        {{-- Modal Header --}}
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-emerald-400 flex items-center justify-center font-mono text-xs font-bold shadow-xs">
                    LOG
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Rincian Forensik Log Audit</h3>
                    <p class="text-[11px] text-slate-500 font-mono" id="modalLogId">—</p>
                </div>
            </div>
            <button type="button" onclick="tutupModalDetailLog()" class="p-1.5 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 overflow-y-auto space-y-4 text-xs">
            {{-- Grid Info Cepat --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Waktu Eksekusi</span>
                    <span class="font-bold text-slate-800 text-[11px]" id="modalLogWaktu">—</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Pelaku & Peran</span>
                    <span class="font-bold text-slate-800 text-[11px]" id="modalLogPelaku">—</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Modul / Kategori</span>
                    <span class="font-bold text-slate-800 text-[11px]" id="modalLogKategori">—</span>
                </div>
                <div>
                    <span class="text-[10px] text-slate-400 uppercase font-semibold block mb-0.5">Status</span>
                    <span class="font-bold text-slate-800 text-[11px]" id="modalLogStatus">—</span>
                </div>
            </div>

            {{-- Narasi Keterangan --}}
            <div>
                <label class="font-bold text-slate-700 block mb-1 text-[11px] uppercase tracking-wider">Narasi Aktivitas</label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 text-slate-800 text-xs leading-relaxed" id="modalLogKeterangan">
                    —
                </div>
            </div>

            {{-- IP Address & Nama Perangkat --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label class="font-bold text-slate-700 block mb-1 text-[11px] uppercase tracking-wider">Alamat IP Pelaku</label>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono text-[11px] text-slate-800 flex items-center gap-2" id="modalLogIpBox">
                        <span>🌐</span>
                        <span id="modalLogIp">—</span>
                    </div>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1 text-[11px] uppercase tracking-wider">Perangkat & Browser</label>
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 text-[11px] font-semibold text-slate-800 flex items-center gap-2" id="modalLogPerangkatBox">
                        <span id="modalLogPerangkatIkon">💻</span>
                        <span id="modalLogPerangkat">—</span>
                    </div>
                </div>
            </div>

            <div>
                <label class="font-bold text-slate-700 block mb-1 text-[11px] uppercase tracking-wider">User-Agent Lengkap</label>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-2.5 font-mono text-[10px] text-slate-600 break-all leading-relaxed" id="modalLogUa">
                    —
                </div>
            </div>

            {{-- ── TABEL KOMPARASI PERUBAHAN (SEBELUM ➔ SESUDAH) ───────────── --}}
            <div id="modalDiffWrapper" class="hidden">
                <div class="flex items-center justify-between mb-1.5">
                    <label class="font-bold text-slate-800 text-[11px] uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span>Perubahan Data (Sebelum ➔ Sesudah)</span>
                    </label>
                    <span class="text-[10px] font-bold bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full" id="modalDiffCount">0 Perubahan</span>
                </div>
                <div class="overflow-x-auto rounded-2xl border border-amber-200 shadow-2xs">
                    <table class="w-full text-left text-xs bg-white">
                        <thead class="bg-amber-50 text-[10.5px] font-bold text-amber-900 border-b border-amber-200 uppercase">
                            <tr>
                                <th class="px-3 py-2 w-1/3">Data / Atribut</th>
                                <th class="px-3 py-2 w-1/3 text-rose-800">Nilai Sebelum (Lama)</th>
                                <th class="px-3 py-2 w-1/3 text-emerald-800">Nilai Sesudah (Baru)</th>
                            </tr>
                        </thead>
                        <tbody id="modalDiffTbody" class="divide-y divide-slate-100 font-mono text-[11px]">
                            <!-- Diisi via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Payload Data JSON --}}
            <div id="modalJsonWrapper" class="hidden">
                <div class="flex items-center justify-between mb-1">
                    <label class="font-bold text-slate-700 text-[11px] uppercase tracking-wider">Payload Mentah / Snapshot JSON</label>
                    <button type="button" onclick="salinJsonLog()" class="text-[10.5px] text-emerald-600 font-semibold hover:underline">
                        Salin JSON
                    </button>
                </div>
                <pre class="bg-slate-900 text-emerald-400 p-3.5 rounded-2xl font-mono text-[11px] overflow-x-auto max-h-56 leading-relaxed border border-slate-800 shadow-inner" id="modalLogJson">—</pre>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end">
            <button type="button" onclick="tutupModalDetailLog()" class="bg-slate-200 hover:bg-slate-300 text-slate-800 px-5 py-2 rounded-xl text-xs font-semibold transition">
                Tutup Rincian
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
let dataJsonSaatIni = '';

function escapeHtml(text) {
    if (!text) return '-';
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function bukaModalDetailLog(log) {
    document.getElementById('modalLogId').innerText = 'UUID: ' + (log.id || '—');
    document.getElementById('modalLogWaktu').innerText = log.dibuat_pada || '—';
    document.getElementById('modalLogPelaku').innerText = (log.nama_admin || 'Sistem') + ' (' + (log.peran || 'ADMIN') + ')';
    document.getElementById('modalLogKategori').innerText = (log.kategori || 'UMUM') + ' • ' + (log.tipe || 'ACTION');
    document.getElementById('modalLogStatus').innerText = log.status || 'SUKSES';
    document.getElementById('modalLogKeterangan').innerText = log.keterangan || '—';
    document.getElementById('modalLogIp').innerText = log.ip_address || '127.0.0.1';
    document.getElementById('modalLogUa').innerText = log.user_agent || '—';

    // Info Perangkat
    const namaPerangkat = log.nama_perangkat || 'Perangkat Komputer / Laptop';
    document.getElementById('modalLogPerangkat').innerText = namaPerangkat;
    const ikonSpan = document.getElementById('modalLogPerangkatIkon');
    if (log.tipe_perangkat === 'MOBILE' || log.tipe_perangkat === 'APP') {
        ikonSpan.innerText = '📱';
    } else if (log.tipe_perangkat === 'SYSTEM') {
        ikonSpan.innerText = '⚙️';
    } else {
        ikonSpan.innerText = '💻';
    }

    // Perubahan (Sebelum vs Sesudah)
    const diffWrapper = document.getElementById('modalDiffWrapper');
    const diffTbody   = document.getElementById('modalDiffTbody');
    const diffCount   = document.getElementById('modalDiffCount');
    diffTbody.innerHTML = '';

    let perubahanObj = null;
    if (log.daftar_perubahan && Object.keys(log.daftar_perubahan).length > 0) {
        perubahanObj = log.daftar_perubahan;
    } else if (log.detail_json) {
        try {
            const parsed = typeof log.detail_json === 'string' ? JSON.parse(log.detail_json) : log.detail_json;
            if (parsed && parsed.perubahan && Object.keys(parsed.perubahan).length > 0) {
                perubahanObj = parsed.perubahan;
            }
        } catch(e) {}
    }

    if (perubahanObj && Object.keys(perubahanObj).length > 0) {
        const keys = Object.keys(perubahanObj);
        diffCount.innerText = keys.length + ' Perubahan';

        keys.forEach(k => {
            const item = perubahanObj[k];
            const label = item.label || item.field || k;
            const sebelum = item.sebelum || '-';
            const sesudah = item.sesudah || '-';

            const tr = document.createElement('tr');
            tr.className = 'hover:bg-amber-50/40 transition';
            tr.innerHTML = `
                <td class="px-3 py-2.5 font-sans font-semibold text-slate-800">${escapeHtml(label)}</td>
                <td class="px-3 py-2.5">
                    <span class="bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded inline-block line-through text-[10.5px]">${escapeHtml(sebelum)}</span>
                </td>
                <td class="px-3 py-2.5">
                    <span class="bg-emerald-50 text-emerald-800 border border-emerald-300 px-2 py-0.5 rounded inline-block font-bold text-[10.5px]">${escapeHtml(sesudah)}</span>
                </td>
            `;
            diffTbody.appendChild(tr);
        });

        diffWrapper.classList.remove('hidden');
    } else {
        diffWrapper.classList.add('hidden');
    }

    // JSON Payload
    const jsonWrapper = document.getElementById('modalJsonWrapper');
    const jsonPre = document.getElementById('modalLogJson');

    if (log.detail_json && log.detail_json !== 'null' && log.detail_json.trim() !== '') {
        try {
            const parsed = typeof log.detail_json === 'string' ? JSON.parse(log.detail_json) : log.detail_json;
            dataJsonSaatIni = JSON.stringify(parsed, null, 2);
            jsonPre.innerText = dataJsonSaatIni;
        } catch (e) {
            dataJsonSaatIni = log.detail_json;
            jsonPre.innerText = log.detail_json;
        }
        jsonWrapper.classList.remove('hidden');
    } else {
        jsonWrapper.classList.add('hidden');
        dataJsonSaatIni = '';
    }

    document.getElementById('modalDetailLog').classList.remove('hidden');
}

function tutupModalDetailLog() {
    document.getElementById('modalDetailLog').classList.add('hidden');
}

function salinJsonLog() {
    if (!dataJsonSaatIni) return;
    navigator.clipboard.writeText(dataJsonSaatIni).then(() => {
        alert('Data JSON log berhasil disalin ke clipboard!');
    });
}
</script>
@endpush

@endsection
