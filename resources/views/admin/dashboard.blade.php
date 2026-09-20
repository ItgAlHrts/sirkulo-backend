@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional dan keuangan Bank Sampah Desa SIRKULO')

@push('styles')
<style>
    .kpi-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    .kpi-card:hover {
        box-shadow: 0 4px 20px -2px rgba(15,23,42,0.06);
        border-color: #cbd5e1;
    }
    .card-base {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .card-header-clean {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .toggle-clean {
        display: inline-flex;
        background: #f1f5f9;
        padding: 2px;
        border-radius: 8px;
    }
    .toggle-clean button {
        padding: 4px 10px;
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .toggle-clean button.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
</style>
@endpush

@section('content')

{{-- ── WELCOME & ROLE HEADER ─────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h2 class="text-xl font-bold font-display text-slate-800 tracking-tight">
            Selamat Datang, {{ session('admin_nama', 'Administrator') }}
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
            Berikut ringkasan operasional dan perputaran kas Bank Sampah Desa hari ini.
        </p>
    </div>
    <div class="flex items-center gap-2.5 self-start sm:self-center">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ session('admin_peran') === 'SUPER_ADMIN' ? 'bg-amber-50 text-amber-800 border border-amber-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
            <span class="w-2 h-2 rounded-full {{ session('admin_peran') === 'SUPER_ADMIN' ? 'bg-amber-500' : 'bg-emerald-500' }}"></span>
            {{ session('admin_peran') === 'SUPER_ADMIN' ? 'Super Administrator' : 'Admin Operasional' }}
        </span>
        <a href="{{ route('admin.dashboard') }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-600 transition shadow-2xs"
           title="Muat Ulang Data">
            <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Segarkan</span>
        </a>
    </div>
</div>

{{-- ── 4 KPI CARDS ───────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">

    {{-- 1. Total Nasabah --}}
    <a href="{{ route('admin.nasabah.index') }}" class="kpi-card group flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500">Nasabah Terdaftar</p>
            <p class="text-2xl font-bold font-display text-slate-900 mt-1">{{ number_format($totalNasabah) }} <span class="text-xs font-normal text-slate-400">Warga</span></p>
            <p class="text-[11px] text-blue-600 font-semibold mt-1 group-hover:underline">Kelola Nasabah →</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
    </a>

    {{-- 2. Pos Bank Sampah --}}
    <a href="{{ route('admin.mitra.index') }}" class="kpi-card group flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500">Pos Bank Sampah</p>
            <p class="text-2xl font-bold font-display text-slate-900 mt-1">{{ number_format($totalPos) }} <span class="text-xs font-normal text-slate-400">Loket</span></p>
            <p class="text-[11px] text-emerald-600 font-semibold mt-1 group-hover:underline">{{ $totalMitra }} Petugas Pos Aktif →</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
        </div>
    </a>

    {{-- 3. Setoran Bulan Ini --}}
    <a href="{{ route('admin.laporan.index') }}" class="kpi-card group flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500">Setoran (Bulan Ini)</p>
            <p class="text-2xl font-bold font-display text-slate-900 mt-1">Rp {{ number_format($totalSetoranRp, 0, ',', '.') }}</p>
            <p class="text-[11px] text-amber-600 font-semibold mt-1 group-hover:underline">{{ $totalSetoranCount }}× Penimbangan →</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
            </svg>
        </div>
    </a>

    {{-- 4. Total Kas Tabungan Warga --}}
    <div class="kpi-card flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-500">Saldo Tabungan Warga</p>
            <p class="text-2xl font-bold font-display text-slate-900 mt-1">Rp {{ number_format($totalKas, 0, ',', '.') }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1">Tersimpan aman di kas desa</p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>

</div>

{{-- ── CHARTS & RINGKASAN AKTIVITAS ───────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-6">

    {{-- Line Chart --}}
    <div class="lg:col-span-8 card-base">
        <div class="card-header-clean flex-col sm:flex-row items-start sm:items-center gap-2.5">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Tren Transaksi 6 Bulan</h3>
                <p class="text-xs text-slate-400">Grafik penyetoran dan volume aktivitas per bulan</p>
            </div>
            <div class="toggle-clean w-full sm:w-auto justify-center">
                <button type="button" id="btnModeCount" onclick="switchChartMode('count')" class="active flex-1 sm:flex-initial text-center">Jumlah (Txn)</button>
                <button type="button" id="btnModeNominal" onclick="switchChartMode('nominal')" class="flex-1 sm:flex-initial text-center">Nominal (Rp)</button>
            </div>
        </div>
        <div class="p-3 sm:p-5">
            <div style="position:relative; height:220px;">
                <canvas id="chartUtama"></canvas>
            </div>
        </div>
    </div>

    {{-- Donut Chart / Breakdown --}}
    <div class="lg:col-span-4 card-base flex flex-col justify-between">
        <div class="card-header-clean">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Aktivitas Bulan Ini</h3>
                <p class="text-xs text-slate-400">Proporsi jenis layanan berjalan</p>
            </div>
        </div>
        <div class="p-5 flex-1 flex flex-col justify-between gap-4">
            <div class="relative mx-auto flex items-center justify-center" style="width:130px; height:130px;">
                <canvas id="chartDonut" width="130" height="130"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="font-display font-extrabold text-2xl text-slate-800 leading-none">{{ $totalAktivitasCount }}</span>
                    <span class="text-[9px] font-semibold uppercase tracking-wider text-slate-400 mt-0.5">Aktivitas</span>
                </div>
            </div>

            <div class="space-y-2 border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Setoran Sampah
                    </span>
                    <span class="font-bold text-slate-800">{{ $distribusiJenis['setoran'] ?? 0 }} transaksi</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Penarikan Saldo
                    </span>
                    <span class="font-bold text-slate-800">{{ $distribusiJenis['penarikan'] ?? 0 }} transaksi</span>
                </div>
                <div class="flex items-center justify-between text-xs py-1">
                    <span class="flex items-center gap-2 text-slate-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                        Aspirasi Warga
                    </span>
                    <span class="font-bold text-slate-800">{{ $distribusiJenis['feedback'] ?? 0 }} saran</span>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── TRANSAKSI TERKINI & POS LOKET ───────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

    {{-- Transaksi Terkini --}}
    <div class="lg:col-span-8 card-base">
        <div class="card-header-clean">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Transaksi Terkini</h3>
                <p class="text-xs text-slate-400">Penyetoran timbangan dan penarikan saldo warga terbaru</p>
            </div>
            <a href="{{ route('admin.laporan.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700">
                Lihat Semua →
            </a>
        </div>
        <div class="table-responsive">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-400 uppercase font-semibold text-[10px] tracking-wider border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3">Nasabah</th>
                        <th class="px-5 py-3">Loket Pos</th>
                        <th class="px-5 py-3">Jenis</th>
                        <th class="px-5 py-3 text-right">Nominal</th>
                        <th class="px-5 py-3 text-right">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transaksiTerbaru as $t)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="px-5 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($t->nasabah?->nama ?? 'W', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-semibold text-slate-800">{{ $t->nasabah?->nama ?? ($t->pengguna?->nama ?? 'Nasabah') }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">{{ $t->nasabah?->kode_user ?? 'SRKL' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-slate-600">
                            {{ $t->partner?->nama ?? ($t->mitra?->nama ?? 'Pos Desa') }}
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap">
                            @if($t->jenis === 'SETORAN')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                    Setor
                                </span>
                            @elseif($t->jenis === 'PENARIKAN' || $t->jenis === 'CAIRKAN')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    Tarik Tunai
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                    {{ $t->jenis }}
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right font-semibold">
                            @if($t->jenis === 'SETORAN')
                                <span class="text-emerald-600 font-bold">+Rp {{ number_format($t->jumlah_total, 0, ',', '.') }}</span>
                            @else
                                <span class="text-rose-600 font-bold">-Rp {{ number_format($t->jumlah_total, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 whitespace-nowrap text-right text-slate-400 text-[11px]">
                            {{ \Carbon\Carbon::parse($t->dibuat_pada)->diffForHumans(null, true) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-slate-400">
                            Belum ada transaksi tercatat bulan ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pos Bank Sampah Ringkas --}}
    <div class="lg:col-span-4 card-base flex flex-col">
        <div class="card-header-clean">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Pos Bank Sampah</h3>
                <p class="text-xs text-slate-400">Loket timbangan di dusun/RW</p>
            </div>
            <a href="{{ route('admin.mitra.index') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 inline-flex items-center gap-1">
                <span>Kelola</span>
                <span>→</span>
            </a>
        </div>
        <div class="p-4 space-y-3 flex-1 overflow-y-auto">
            @forelse($posList as $index => $pos)
            <div class="p-3.5 rounded-2xl border border-slate-200/80 bg-slate-50/70 hover:bg-emerald-50/30 hover:border-emerald-300/70 transition-all duration-150">
                <div class="flex items-center justify-between gap-2">
                    <h4 class="font-bold text-slate-800 text-xs sm:text-sm truncate" title="{{ $pos->nama }}">
                        {{ $pos->nama }}
                    </h4>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-full border border-emerald-200/80 flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                    </span>
                </div>
                <p class="text-[11px] text-slate-500 truncate mt-1 flex items-center gap-1">
                    <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>{{ $pos->alamat ?: 'Wilayah Desa' }}</span>
                </p>
                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-2.5 pt-2 border-t border-slate-200/60">
                    <span class="truncate mr-2">Petugas: <strong class="text-slate-700 font-semibold">{{ $pos->petugas?->nama ?? ($pos->pengguna?->nama ?? 'Mitra SIRKULO') }}</strong></span>
                    <span class="text-slate-400 font-medium flex-shrink-0">{{ $pos->jam_buka ?? '08:00 - 16:00 WIB' }}</span>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-xs text-slate-400">
                Belum ada pos bank sampah terdaftar.
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
    const labels             = @json($chartLabels);
    const dataSetoran        = @json($chartDataSetoran);
    const dataPenarikanCount = @json($chartDataPenarikan);
    const dataNominal        = @json($chartDataNominal);
    const dataCairkan        = @json($chartDataCairkan);

    let currentMode = 'count';
    let chartUtama  = null;

    function initChartUtama() {
        const canvas = document.getElementById('chartUtama');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');

        const datasets = currentMode === 'count'
            ? [{
                label: 'Setoran Sampah',
                data: dataSetoran,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.08)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#059669'
              }, {
                label: 'Penarikan Saldo',
                data: dataPenarikanCount,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.05)',
                borderWidth: 2,
                borderDash: [4, 4],
                tension: 0.35,
                fill: false,
                pointRadius: 3,
                pointBackgroundColor: '#f59e0b'
              }]
            : [{
                label: 'Nominal Setoran (Rp)',
                data: dataNominal,
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.08)',
                borderWidth: 2,
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointBackgroundColor: '#059669'
              }, {
                label: 'Nominal Penarikan (Rp)',
                data: dataCairkan,
                borderColor: '#f59e0b',
                backgroundColor: 'rgba(245, 158, 11, 0.05)',
                borderWidth: 2,
                borderDash: [4, 4],
                tension: 0.35,
                fill: false,
                pointRadius: 3,
                pointBackgroundColor: '#f59e0b'
              }];

        if (chartUtama) chartUtama.destroy();
        chartUtama = new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            boxWidth: 8,
                            boxHeight: 8,
                            usePointStyle: true,
                            font: { size: 10, weight: '600' },
                            color: '#64748b',
                            padding: 10
                        }
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label(c) {
                                return currentMode === 'nominal'
                                    ? c.dataset.label + ': Rp ' + Number(c.parsed.y).toLocaleString('id-ID')
                                    : c.dataset.label + ': ' + Number(c.parsed.y).toLocaleString('id-ID') + ' transaksi';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            color: '#94a3b8',
                            callback(v) {
                                return currentMode === 'nominal'
                                    ? (v >= 1e6 ? (v/1e6).toFixed(1)+'jt' : v >= 1e3 ? (v/1e3).toFixed(0)+'rb' : v)
                                    : v;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' }
                    }
                }
            }
        });
    }

    function switchChartMode(mode) {
        currentMode = mode;
        document.getElementById('btnModeCount').className   = (mode === 'count'   ? 'active' : '');
        document.getElementById('btnModeNominal').className = (mode === 'nominal' ? 'active' : '');
        initChartUtama();
    }

    function initChartDonut() {
        const canvas = document.getElementById('chartDonut');
        if (!canvas) return;
        const ctx  = canvas.getContext('2d');
        const dist = @json($distribusiJenis);

        const setoran   = Number(dist.setoran || 0);
        const penarikan = Number(dist.penarikan || 0);
        const feedback  = Number(dist.feedback || 0);
        const total     = setoran + penarikan + feedback;

        const chartData = total > 0
            ? [setoran, penarikan, feedback]
            : [1];
        const bgColors  = total > 0
            ? ['#10b981', '#f59e0b', '#a855f7']
            : ['#e2e8f0'];

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: total > 0 ? ['Setoran Sampah', 'Penarikan Saldo', 'Aspirasi Warga'] : ['Belum Ada Aktivitas'],
                datasets: [{
                    data: chartData,
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: false,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        callbacks: {
                            label(c) {
                                return c.label + ': ' + c.raw + (c.dataIndex === 2 ? ' saran' : ' transaksi');
                            }
                        }
                    }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initChartUtama();
        initChartDonut();
    });
</script>
@endpush
