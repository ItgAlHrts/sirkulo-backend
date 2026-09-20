@extends('admin.layout')

@section('title', 'Kesehatan Database & Pemeliharaan Sistem Server')
@section('page-title', 'Sistem & Kesehatan Database')
@section('page-subtitle', 'Diagnostik kesehatan MySQL, manajemen migrasi online, pemeliharaan cache, audit folder hosting, dan backup database')

@section('content')

{{-- Alert Notifikasi Sukses / Error --}}
@if(session('success'))
<div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-sm animate-fade-in">
    <div class="w-7 h-7 rounded-xl bg-emerald-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <div class="flex-1 text-xs">
        <h5 class="font-bold text-sm text-emerald-950">Operasi Berhasil</h5>
        <p class="mt-0.5 text-emerald-800 font-medium leading-relaxed">{{ session('success') }}</p>
    </div>
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-sm animate-fade-in">
    <div class="w-7 h-7 rounded-xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </div>
    <div class="flex-1 text-xs">
        <h5 class="font-bold text-sm text-rose-950">Terjadi Kendala</h5>
        <p class="mt-0.5 text-rose-800 font-medium leading-relaxed">{{ session('error') }}</p>
    </div>
</div>
@endif


{{-- Banner Notifikasi Migrasi Database (Jika ada berkas migrasi pending) --}}
@if(!$migrationStats['is_synced'])
<div class="mb-6 p-4 rounded-2xl bg-amber-100/80 border-2 border-amber-400 text-amber-950 flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-sm animate-pulse">
    <div class="flex items-start gap-3">
        <div class="w-9 h-9 rounded-xl bg-amber-600 text-white flex items-center justify-center flex-shrink-0 mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h4 class="text-sm font-bold text-amber-950 flex items-center gap-2">
                Pembaruan Skema Database Tersedia
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white">{{ $migrationStats['pending_count'] }} Berkas Baru</span>
            </h4>
            <p class="text-xs text-amber-900 mt-0.5">Terdapat {{ $migrationStats['pending_count'] }} berkas migrasi yang belum dieksekusi ke database hosting. Klik tombol di samping untuk mengeksekusinya secara otomatis.</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.sistem.runMigrations') }}" onsubmit="return confirm('Jalankan migrasi database sekarang?');" class="flex-shrink-0">
        @csrf
        <button type="submit" class="px-4 py-2.5 bg-amber-700 hover:bg-amber-800 active:scale-95 text-white text-xs font-bold rounded-xl shadow transition-all flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Jalankan Migrasi Sekarang</span>
        </button>
    </form>
</div>
@endif

{{-- 1. Kartu Indikator Utama (KPI Status Sistem) --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Status Koneksi DB & Latensi --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-emerald-200 transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Koneksi Database</span>
            <span class="w-2.5 h-2.5 rounded-full {{ $dbConnected ? 'bg-emerald-500 shadow-sm shadow-emerald-500/50 animate-pulse' : 'bg-rose-500' }}"></span>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-2xl font-black text-gray-800">{{ $dbConnected ? 'Terhubung' : 'Terputus' }}</h3>
            @if($dbConnected && $dbLatencyMs !== null)
            <span class="text-xs font-bold px-2 py-0.5 rounded-md {{ $dbLatencyMs < 20 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                {{ $dbLatencyMs }} ms
            </span>
            @endif
        </div>
        <p class="text-xs text-gray-500 mt-1 flex items-center gap-1 truncate">
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7z"/>
            </svg>
            <span class="font-mono text-gray-700 font-semibold">{{ $dbName }}</span> ({{ $dbHost }}:{{ $dbPort }})
        </p>
    </div>

    {{-- Total Ukuran Database & Tabel --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-emerald-200 transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kapasitas Database</span>
            <span class="text-xs font-bold text-gray-500">{{ $totalTablesCount }} Tabel Aktif</span>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-2xl font-black text-gray-800">{{ $dbSizeFormatted }}</h3>
            <span class="text-[11px] font-medium text-gray-400">Total data</span>
        </div>
        <p class="text-xs text-gray-500 mt-1 flex items-center justify-between">
            <span>{{ number_format($totalRowsCount, 0, ',', '.') }} total baris</span>
            <span class="text-[11px] text-gray-400">Overhead: {{ $dbOverheadFormatted }}</span>
        </p>
    </div>

    {{-- Lingkungan Server & Modus Debug --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-emerald-200 transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Lingkungan Server</span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $environment === 'production' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                {{ strtoupper($environment) }}
            </span>
        </div>
        <div class="flex items-baseline gap-2">
            <h3 class="text-2xl font-black text-gray-800">PHP {{ explode('-', $phpVersion)[0] }}</h3>
            <span class="text-[11px] font-medium text-gray-400">Laravel {{ $laravelVersion }}</span>
        </div>
        <p class="text-xs mt-1 font-semibold {{ $debugMode ? 'text-rose-600' : 'text-emerald-600' }} flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full {{ $debugMode ? 'bg-rose-500' : 'bg-emerald-500' }}"></span>
            Debug: {{ $debugMode ? 'AKTIF (Ubah ke false di Hosting)' : 'NONAKTIF (Aman)' }}
        </p>
    </div>

    {{-- Kesiapan Hosting (Ekstensi & Izin Folder) --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:border-emerald-200 transition-all">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400">Kesiapan Hosting</span>
            <span class="w-2.5 h-2.5 rounded-full {{ ($activeExtensionsCount === count($extensionStatus) && $writableFoldersCount === count($folderPermissions)) ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
        </div>
        @php
            $hostingReadyPct = count($extensionStatus) > 0
                ? round((($activeExtensionsCount + $writableFoldersCount) / (count($extensionStatus) + count($folderPermissions))) * 100)
                : 0;
            $hostingLabel = $hostingReadyPct >= 100 ? '100% Siap' : ($hostingReadyPct >= 80 ? 'Hampir Siap' : 'Perlu Cek');
            $hostingColor = $hostingReadyPct >= 100 ? 'text-emerald-700' : ($hostingReadyPct >= 80 ? 'text-amber-700' : 'text-rose-700');
        @endphp
        <div class="flex items-baseline gap-2">
            <h3 class="text-2xl font-black {{ $hostingColor }}">{{ $hostingLabel }}</h3>
            <span class="text-[11px] font-semibold {{ $hostingReadyPct >= 100 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $activeExtensionsCount }}/{{ count($extensionStatus) }} Ekstensi</span>
        </div>
        <p class="text-xs text-gray-500 mt-1 flex items-center justify-between">
            <span>Folder Izin: <strong class="{{ $writableFoldersCount === count($folderPermissions) ? 'text-emerald-700' : 'text-amber-700' }}">{{ $writableFoldersCount }}/{{ count($folderPermissions) }} Writeable</strong></span>
            <span class="{{ $hostingReadyPct >= 100 ? 'text-emerald-600' : 'text-amber-600' }} font-bold">{{ $hostingReadyPct >= 100 ? 'Lolos Audit' : 'Periksa' }}</span>
        </p>
    </div>
</div>

{{-- 2. Pusat Aksi Pemeliharaan Server & Database (One-Click Operations) --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 mb-4 border-b border-gray-100 gap-2">
        <div>
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Pusat Kendali Aksi Cepat Pemeliharaan
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Eksekusi operasi server penting langsung dari browser tanpa perlu akses command line atau terminal SSH.</p>
        </div>
        <div class="flex items-center gap-2 text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200/60">
            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Aman Digunakan di Shared Hosting
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- Aksi 1: Migrasi Database Online --}}
        <div class="border border-gray-100 hover:border-blue-200 rounded-xl p-4 bg-gradient-to-b from-white to-blue-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM9 12h6m-6 4h6"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Jalankan Migrasi Database</h4>
                <p class="text-xs text-gray-500 mt-1">Eksekusi <code class="font-mono bg-gray-100 px-1 py-0.5 rounded text-[11px]">migrate --force</code> secara aman untuk membuat/memperbarui tabel saat kode baru diupload ke hosting.</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Status: <strong class="{{ $migrationStats['is_synced'] ? 'text-emerald-600' : 'text-amber-600' }}">{{ $migrationStats['executed'] }}/{{ $migrationStats['total_files'] }} Berkas Aktif</strong>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.sistem.runMigrations') }}" onsubmit="return confirm('Jalankan migrasi database sekarang?');" class="mt-4">
                @csrf
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Jalankan Migrasi DB</span>
                </button>
            </form>
        </div>

        {{-- Aksi 2: Optimasi & Defragmentasi Tabel --}}
        <div class="border border-gray-100 hover:border-emerald-200 rounded-xl p-4 bg-gradient-to-b from-white to-emerald-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Optimasi & Periksa Tabel DB</h4>
                <p class="text-xs text-gray-500 mt-1">Jalankan <code class="font-mono bg-gray-100 px-1 py-0.5 rounded text-[11px]">OPTIMIZE & ANALYZE</code> pada seluruh tabel untuk menyusun ulang indeks dan menghemat ruang disk.</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Overhead saat ini: <strong class="text-gray-700">{{ $dbOverheadFormatted }}</strong>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.sistem.optimizeTables') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>Optimalkan Tabel Sekarang</span>
                </button>
            </form>
        </div>

        {{-- Aksi 3: Bersihkan Seluruh Cache --}}
        <div class="border border-gray-100 hover:border-amber-200 rounded-xl p-4 bg-gradient-to-b from-white to-amber-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Bersihkan Seluruh Cache</h4>
                <p class="text-xs text-gray-500 mt-1">Hapus cache konfigurasi, cache rute, dan template Blade lama pasca perubahan kode atau konfigurasi file .env.</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Status: <span class="text-gray-700 font-semibold">{{ $viewsCached ? 'Ada cache view' : 'Bersih' }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.sistem.clearCache') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Bersihkan Cache</span>
                </button>
            </form>
        </div>

        {{-- Aksi 4: Optimasi Produksi (Cache Config & Rute) --}}
        <div class="border border-gray-100 hover:border-indigo-200 rounded-xl p-4 bg-gradient-to-b from-white to-indigo-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Optimasi Kecepatan Produksi</h4>
                <p class="text-xs text-gray-500 mt-1">Pre-compile berkas konfigurasi dan rute agar performa API mobile dan admin panel berjalan dengan latensi paling minim.</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Config: <strong class="{{ $configCached ? 'text-emerald-600' : 'text-gray-600' }}">{{ $configCached ? 'Di-cache' : 'Dinamis' }}</strong> • Rute: <strong class="{{ $routesCached ? 'text-emerald-600' : 'text-gray-600' }}">{{ $routesCached ? 'Di-cache' : 'Dinamis' }}</strong>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.sistem.optimize') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span>Optimasi Sistem</span>
                </button>
            </form>
        </div>

        {{-- Aksi 5: Tautkan Storage & Cek Fallback --}}
        <div class="border border-gray-100 hover:border-purple-200 rounded-xl p-4 bg-gradient-to-b from-white to-purple-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center mb-2.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Tautkan Storage Publik</h4>
                <p class="text-xs text-gray-500 mt-1">Perbarui symlink folder storage. Bila hosting Anda membatasi symlink, SIRKULO otomatis menyajikan berkas via router fallback bawaan!</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Symlink: <strong class="{{ $storageLinked ? 'text-emerald-600' : 'text-amber-600' }}">{{ $storageLinked ? 'Terhubung' : 'Fallback Router Aktif' }}</strong> ({{ $storageSize }})
                </div>
            </div>
            <form method="POST" action="{{ route('admin.sistem.storageLink') }}" class="mt-4">
                @csrf
                <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    <span>Tautkan Ulang Storage</span>
                </button>
            </form>
        </div>

        {{-- Aksi 6: Download Cadangan SQL Database --}}
        <div class="border border-emerald-200 hover:border-emerald-300 rounded-xl p-4 bg-gradient-to-b from-emerald-50/40 to-teal-50/20 flex flex-col justify-between transition-all">
            <div>
                <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center mb-2.5 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                </div>
                <h4 class="font-bold text-gray-800 text-sm">Unduh Backup Database (.sql)</h4>
                <p class="text-xs text-gray-500 mt-1">Unduh seluruh tabel, data warga, riwayat setoran, saldo kas, dan konfigurasi sistem dalam berkas SQL murni sekali klik.</p>
                <div class="mt-2 text-[11px] text-gray-400">
                    Kapasitas: <strong class="text-emerald-700 font-bold">{{ $dbSizeFormatted }}</strong> (Format standar MySQL)
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.sistem.backupDb') }}" class="block text-center w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-semibold py-2.5 rounded-lg shadow-sm transition-all flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span>Unduh Cadangan SQL</span>
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 3. Detail Diagnostik Seluruh Tabel Database --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 pb-4 mb-4 border-b border-gray-100">
        <div>
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM9 12h6m-6 4h6"/>
                </svg>
                Statistik & Integritas Tabel Database
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Pemantauan kesehatan fisik tabel, jumlah baris data, alokasi memori indeks, dan status fragmentasi.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-bold flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                {{ $healthyTablesCount }}/{{ count($tableStats) }} Tabel Aktif & Sehat
            </span>
            <form method="POST" action="{{ route('admin.sistem.optimizeTables') }}" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition-colors flex items-center gap-1" title="Periksa dan optimalkan semua tabel">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Cek & Optimasi</span>
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-600">
            <thead class="bg-gray-50/80 text-gray-700 font-bold border-b border-gray-100 uppercase text-[10px] tracking-wider">
                <tr>
                    <th class="px-4 py-3">Nama Tabel & Deskripsi</th>
                    <th class="px-4 py-3 text-center">Engine</th>
                    <th class="px-4 py-3 text-right">Jumlah Baris</th>
                    <th class="px-4 py-3 text-right">Ukuran Data</th>
                    <th class="px-4 py-3 text-right">Ukuran Indeks</th>
                    <th class="px-4 py-3 text-right">Total Kapasitas</th>
                    <th class="px-4 py-3 text-center">Status Integritas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($tableStats as $st)
                <tr class="hover:bg-gray-50/80 transition-colors">
                    <td class="px-4 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center flex-shrink-0 font-mono text-xs font-bold">
                                {{ strtoupper(substr($st['table'], 0, 2)) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-gray-800 text-xs">{{ $st['table'] }}</span>
                                    <span class="text-[11px] font-semibold text-gray-500">({{ $st['label'] }})</span>
                                </div>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ $st['desc'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        <span class="font-mono text-gray-600 text-[11px] bg-gray-100 px-2 py-0.5 rounded">{{ $st['engine'] }}</span>
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono font-bold text-gray-800">
                        {{ number_format($st['rows'], 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-gray-600">
                        {{ $st['data_size'] }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono text-gray-500">
                        {{ $st['index_size'] }}
                    </td>
                    <td class="px-4 py-3.5 text-right font-mono font-bold text-emerald-700">
                        {{ $st['total_size'] }}
                    </td>
                    <td class="px-4 py-3.5 text-center">
                        @if(!$st['exists'])
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-md font-bold text-[10px] uppercase">
                            Belum Dibuat
                        </span>
                        @elseif($st['status'] === 'PERLU_OPTIMASI')
                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-md font-bold text-[10px] uppercase" title="Terdapat ruang kosong: {{ $st['overhead'] }}">
                            Fragmentasi
                        </span>
                        @else
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md font-bold text-[10px] uppercase">
                            Prima & Sehat
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-6 text-gray-400">Tidak ada informasi tabel yang tersedia.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- 4. Audit Kesiapan Lingkungan Hosting (PHP Extensions & Folder Permissions) --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    {{-- Kolom Kiri: Ekstensi PHP Wajib Hosting --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Audit Ekstensi PHP Server
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Daftar modul PHP yang disyaratkan untuk kestabilan framework dan transaksi aplikasi.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $activeExtensionsCount === count($extensionStatus) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700' }}">
                {{ $activeExtensionsCount }}/{{ count($extensionStatus) }} Aktif
            </span>
        </div>

        <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
            @foreach($extensionStatus as $ext)
            <div class="flex items-center justify-between p-2.5 rounded-xl border border-gray-100 bg-gray-50/40 hover:bg-white transition-all">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-6 h-6 rounded-lg {{ $ext['active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center flex-shrink-0 text-xs font-bold">
                        {{ $ext['active'] ? '✓' : '✕' }}
                    </div>
                    <div class="min-w-0">
                        <h5 class="font-mono font-bold text-gray-800 text-xs leading-none">{{ $ext['name'] }} <span class="text-gray-400 font-normal">({{ $ext['ext'] }})</span></h5>
                        <p class="text-[11px] text-gray-400 mt-1 truncate">{{ $ext['role'] }}</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase flex-shrink-0 {{ $ext['active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $ext['active'] ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 rounded-xl bg-blue-50/60 border border-blue-100 text-xs text-blue-900">
            <strong class="font-bold">Tips Hosting cPanel:</strong> Jika ada ekstensi nonaktif, masuk ke menu <em>Select PHP Version</em> > tab <em>Extensions</em> di cPanel, lalu centang modul tersebut.
        </div>
    </div>

    {{-- Kolom Kanan: Izin Folder / Writeable Permissions --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
            <div>
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    Izin Akses Tulis Folder (Permissions)
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">Memastikan sistem dapat menulis cache, berkas sesi login, berkas log error, dan foto upload.</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $writableFoldersCount === count($folderPermissions) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700' }}">
                {{ $writableFoldersCount }}/{{ count($folderPermissions) }} Writeable
            </span>
        </div>

        <div class="space-y-2 max-h-[380px] overflow-y-auto pr-1">
            @foreach($folderPermissions as $fp)
            <div class="flex items-center justify-between p-2.5 rounded-xl border border-gray-100 bg-gray-50/40 hover:bg-white transition-all">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-6 h-6 rounded-lg {{ $fp['writable'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }} flex items-center justify-center flex-shrink-0 text-xs font-bold">
                        {{ $fp['writable'] ? '✓' : '✕' }}
                    </div>
                    <div class="min-w-0">
                        <h5 class="font-mono font-bold text-gray-800 text-xs leading-none">{{ $fp['label'] }}</h5>
                        <p class="text-[11px] text-gray-400 mt-1 truncate">{{ $fp['role'] }}</p>
                    </div>
                </div>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase flex-shrink-0 {{ $fp['writable'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                    {{ $fp['writable'] ? 'Dapat Ditulis' : 'Terkunci (755)' }}
                </span>
            </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 rounded-xl bg-amber-50/60 border border-amber-100 text-xs text-amber-900">
            <strong class="font-bold">Tips Izin Shared Hosting:</strong> Pastikan folder <code class="font-mono bg-white px-1 py-0.5 rounded">storage/</code> dan <code class="font-mono bg-white px-1 py-0.5 rounded">bootstrap/cache/</code> memiliki hak akses <strong>775</strong> atau <strong>755</strong> di cPanel File Manager.
        </div>
    </div>
</div>

{{-- 5. Parameter Mesin & Batasan Runtime Server --}}
<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 mb-6">
    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
        <div>
            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Spesifikasi Mesin & Batasan Runtime PHP
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Konfigurasi batas alokasi memori, ukuran unggahan berkas, dan kapasitas disk server.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Batas Memori</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono">{{ $memoryLimit }}</h4>
            <p class="text-[11px] text-emerald-600 mt-0.5 font-medium">Terpakai: {{ $memoryUsage }}</p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Maks. Upload File</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono">{{ $maxUploadSize }}</h4>
            <p class="text-[11px] text-gray-500 mt-0.5">Unggah Foto & PDF</p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Maks. POST Size</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono">{{ $maxPostSize }}</h4>
            <p class="text-[11px] text-gray-500 mt-0.5">Total Payload Form</p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Maks. Waktu Eksekusi</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono">{{ $maxExecTime }}</h4>
            <p class="text-[11px] text-gray-500 mt-0.5">Timeout PHP Script</p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Kapasitas Disk Server</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono">{{ $diskTotal }}</h4>
            <p class="text-[11px] text-gray-500 mt-0.5">Tersedia: {{ $diskFree }}</p>
        </div>

        <div class="p-3.5 rounded-xl border border-gray-100 bg-gray-50/50">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Queue Driver</span>
            <h4 class="text-base font-bold text-gray-800 mt-1 font-mono uppercase">{{ $queueDriver }}</h4>
            <p class="text-[11px] text-emerald-600 mt-0.5 font-medium">Sinkron (Langsung)</p>
        </div>
    </div>
</div>

{{-- 6. Panduan Praktis Hosting (cPanel & VPS) Siap Pakai — Accordion --}}
<details class="mb-2" id="panduan-hosting">
    <summary class="flex items-center gap-2 cursor-pointer select-none bg-white rounded-2xl px-6 py-4 shadow-sm border border-gray-100 hover:border-blue-200 transition-all">
        <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
        </svg>
        <span class="text-sm font-bold text-gray-800 flex-1">📋 Panduan Persiapan Hosting & Daftar Periksa Keamanan</span>
        <span class="text-xs text-gray-400 font-medium">Klik untuk buka/tutup</span>
        <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 details-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </summary>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-3">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                Panduan Menyiapkan File .env di Hosting
            </h3>
            <p class="text-xs text-gray-500 mb-4">
                Saat pertama kali mengunggah file aplikasi ke cPanel atau VPS, ubah parameter berikut di berkas <strong class="text-gray-800 font-mono">.env</strong>:
            </p>

            <div class="space-y-3 text-xs">
                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="font-bold text-gray-800 mb-1">1. Pengaturan Mode Produksi & URL Resmi Desa:</p>
                    <pre class="bg-gray-800 text-green-400 p-2.5 rounded-lg font-mono text-[11px] overflow-x-auto">
APP_ENV=production
APP_DEBUG=false
APP_URL=https://banksampah.desa.id</pre>
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="font-bold text-gray-800 mb-1">2. Pengaturan Kredensial Database MySQL cPanel:</p>
                    <pre class="bg-gray-800 text-green-400 p-2.5 rounded-lg font-mono text-[11px] overflow-x-auto">
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cpaneluser_sirkulodb
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=PasswordKuatAnda123!</pre>
                </div>

                <div class="p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <p class="font-bold text-gray-800 mb-1">3. Selesai Setup? Buka Halaman Ini Lalu:</p>
                    <p class="text-gray-600 leading-relaxed">Cukup klik tombol <strong>"Jalankan Migrasi Database"</strong> dan <strong>"Optimasi Sistem"</strong> di halaman ini tanpa perlu membuka terminal SSH!</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <h3 class="text-base font-bold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
                Daftar Periksa Keamanan Server Desa (Pre-Launch Checklist)
            </h3>
            <p class="text-xs text-gray-500 mb-4">
                Pastikan poin-poin berikut telah terpenuhi sebelum server dipakai resmi oleh warga dan petugas mitra pos:
            </p>

            <div class="space-y-3 text-xs">
                <div class="flex items-start gap-2.5 p-2 rounded-xl bg-gray-50">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">✓</div>
                    <div>
                        <span class="font-bold text-gray-800">Document Root Domain Diarahkan ke Folder /public:</span>
                        <p class="text-gray-500 mt-0.5">Di cPanel, arahkan direktori domain ke <code class="font-mono bg-white px-1 py-0.5 rounded text-[10px]">public_html/public</code> atau pindahkan isi folder <code class="font-mono bg-white px-1 py-0.5 rounded text-[10px]">public</code> ke root agar file inti Laravel tidak terekspos.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 p-2 rounded-xl bg-gray-50">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">✓</div>
                    <div>
                        <span class="font-bold text-gray-800">Sertifikat SSL / HTTPS Aktif:</span>
                        <p class="text-gray-500 mt-0.5">Aktifkan AutoSSL gratis di cPanel agar seluruh enkripsi kata sandi dan transaksi warga aman dari penyadapan.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 p-2 rounded-xl bg-gray-50">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">✓</div>
                    <div>
                        <span class="font-bold text-gray-800">Nonaktifkan Mode Debug:</span>
                        <p class="text-gray-500 mt-0.5">Pastikan <code class="font-mono bg-white px-1 py-0.5 rounded text-[10px]">APP_DEBUG=false</code> agar informasi database tidak bocor saat terjadi kesalahan sistem.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 p-2 rounded-xl bg-gray-50">
                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center flex-shrink-0 mt-0.5 font-bold">✓</div>
                    <div>
                        <span class="font-bold text-gray-800">Pencadangan Rutin Mingguan:</span>
                        <p class="text-gray-500 mt-0.5">Gunakan tombol <strong>"Unduh Cadangan SQL"</strong> secara berkala setiap minggu untuk disimpan di Google Drive atau flashdisk desa sebagai cadangan arsip resmi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</details>

<style>
    details[open] > summary .details-chevron { transform: rotate(180deg); }
</style>

@endsection
