@extends('admin.layout')

@section('title', 'Detail Pos: ' . $pos->nama)
@section('page-title', 'Detail Pos Bank Sampah')
@section('page-subtitle', 'Monitoring performa dan transaksi pos unit desa')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.mitra.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Pos
    </a>
</div>

{{-- Info Pos & Statistik --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
        <span class="inline-block px-3 py-1 bg-blue-50 text-blue-700 text-xs font-mono font-bold rounded-lg mb-3">
            {{ $pos->kode_pos }}
        </span>
        <h3 class="text-xl font-bold text-gray-800">{{ $pos->nama }}</h3>
        <p class="text-sm text-gray-500 mt-2 flex items-start gap-2">
            <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ $pos->alamat ?: 'Alamat belum diatur' }}
        </p>

        <div class="border-t border-gray-100 my-4"></div>

        <div class="space-y-3 text-sm">
            <div>
                <span class="text-xs text-gray-400 block">Penanggung Jawab (PIC)</span>
                <span class="font-semibold text-gray-800">{{ $pos->pengguna?->nama ?? '-' }}</span>
                <span class="text-xs text-gray-500 block">{{ $pos->pengguna?->telepon ?? $pos->pengguna?->email ?? '-' }}</span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">Jadwal Operasional</span>
                <span class="font-medium text-gray-700 text-xs">{{ $pos->jam_buka ?: 'Belum diatur' }}</span>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Setoran</p>
            <h3 class="text-2xl font-black text-green-600 mt-2">{{ number_format($totalSetoran) }} Kali</h3>
            <p class="text-xs text-gray-400 mt-1">Sampah diterima dari warga</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Pencairan</p>
            <h3 class="text-2xl font-black text-orange-600 mt-2">{{ number_format($totalPenarikan) }} Kali</h3>
            <p class="text-xs text-gray-400 mt-1">Saldo tunai dicairkan</p>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Perputaran Nilai</p>
            <h3 class="text-xl font-black text-blue-600 mt-2">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h3>
            <p class="text-xs text-gray-400 mt-1">Akumulasi nilai setoran</p>
        </div>
    </div>
</div>

{{-- Jenis Sampah Diterima di Pos Ini --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h4 class="font-bold text-gray-800 text-sm">Jenis Sampah yang Diterima di Pos Ini</h4>
        <a href="{{ route('admin.mitra.index') }}" class="text-xs text-emerald-600 hover:underline font-medium">Ubah via Edit Pos</a>
    </div>
    <div class="px-6 py-4">
        @php
            $kats = $pos->kategoriSampah ?? collect();
        @endphp
        @if($kats->isEmpty())
            <div class="flex items-center gap-2 text-sm text-amber-700 bg-amber-50 rounded-xl px-4 py-3 border border-amber-200/60">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Pos ini belum dikonfigurasi — saat ini menerima semua jenis sampah (default). Silakan edit pos untuk mengatur jenis sampah yang diterima.</span>
            </div>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($kats as $kat)
                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $kat->nama }}
                        @if($kat->harga_per_kg)
                            <span class="ml-1 text-emerald-500 font-normal">· Rp {{ number_format($kat->harga_per_kg, 0, ',', '.') }}/kg</span>
                        @endif
                    </span>
                @endforeach
            </div>
            <p class="text-xs text-gray-400 mt-3">{{ $kats->count() }} jenis sampah dikonfigurasi untuk pos ini. Mitra yang ingin menambah jenis baru harus menghubungi Admin.</p>
        @endif
    </div>
</div>

{{-- Tabel 20 Transaksi Terakhir di Pos Ini --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
        <h4 class="font-bold text-gray-800 text-sm">Aktivitas Transaksi Terbaru di Pos Ini</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Nasabah</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Jenis</th>
                    <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                    <th class="text-right px-6 py-3 text-xs font-medium text-gray-500 uppercase">Nominal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($transaksi as $t)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($t->dibuat_pada)->translatedFormat('d M Y, H:i') }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-gray-800">
                        {{ $t->pengguna?->nama ?? '-' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($t->jenis === 'SETORAN')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700">Setoran</span>
                        @elseif($t->jenis === 'CAIRKAN' || $t->jenis === 'PENARIKAN')
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-700">Tarik Tunai</span>
                        @else
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600">{{ $t->jenis }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600">
                        {{ $t->keterangan ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-right font-bold text-gray-800">
                        Rp {{ number_format($t->jumlah_total, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada transaksi di pos ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
