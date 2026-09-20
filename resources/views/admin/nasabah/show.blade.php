@extends('admin.layout')

@section('title', 'Detail Nasabah: ' . $nasabah->nama)
@section('page-title', 'Detail Nasabah')
@section('page-subtitle', 'Informasi profil dan riwayat transaksi nasabah')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.nasabah.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Nasabah
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Profil Card --}}
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col items-center text-center">
        <div class="w-24 h-24 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-3xl mb-4 overflow-hidden border-2 border-green-200">
            @if($nasabah->foto_url)
                <img src="{{ $nasabah->foto_url }}" alt="{{ $nasabah->nama }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($nasabah->nama, 0, 1)) }}
            @endif
        </div>
        <h3 class="text-xl font-bold text-gray-800">{{ $nasabah->nama }}</h3>
        <span class="inline-block mt-1 px-3 py-1 bg-green-50 text-green-700 text-xs font-mono font-semibold rounded-full">
            {{ $nasabah->kode_user }}
        </span>
        <p class="text-sm text-gray-500 mt-1">{{ $nasabah->email }}</p>

        <div class="w-full border-t border-gray-100 my-5"></div>

        <div class="w-full space-y-3 text-left text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">No. Telepon:</span>
                <span class="font-medium text-gray-800">{{ $nasabah->telepon ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Alamat:</span>
                <span class="font-medium text-gray-800 text-right max-w-[180px] truncate" title="{{ $nasabah->alamat }}">
                    {{ $nasabah->alamat ?? '-' }}
                </span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Tanggal Daftar:</span>
                <span class="font-medium text-gray-800">
                    {{ \Carbon\Carbon::parse($nasabah->dibuat_pada)->translatedFormat('d F Y') }}
                </span>
            </div>
        </div>

        <div class="w-full mt-6 flex gap-2">
            <a href="{{ route('admin.nasabah.edit', $nasabah->id) }}" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 rounded-xl text-xs transition-colors text-center">
                Edit Profil
            </a>
            <form method="POST" action="{{ route('admin.nasabah.destroy', $nasabah->id) }}" class="flex-1"
                  onsubmit="return confirm('Yakin ingin menghapus nasabah ini secara permanen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-50 hover:bg-red-100 text-red-600 font-medium py-2 rounded-xl text-xs transition-colors">
                    Hapus Akun
                </button>
            </form>
        </div>
    </div>

    {{-- Saldo & Ringkasan Transaksi --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6 text-white shadow-sm">
                <p class="text-xs font-medium text-green-100 uppercase tracking-wider">Saldo Tabungan Saat Ini</p>
                <h2 class="text-3xl font-extrabold mt-2">Rp {{ number_format($nasabah->saldo, 0, ',', '.') }}</h2>
                <p class="text-xs text-green-200 mt-2">Setara dengan {{ number_format($nasabah->poin) }} Poin Sirkulo</p>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col justify-center">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Transaksi</p>
                <h2 class="text-3xl font-extrabold text-gray-800 mt-2">{{ $transaksi->count() }} Kali</h2>
                <p class="text-xs text-gray-400 mt-2">Termasuk setoran sampah dan penarikan saldo</p>
            </div>
        </div>

        {{-- Tabel Riwayat Transaksi --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h4 class="font-bold text-gray-800 text-sm">Riwayat 20 Transaksi Terakhir</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-6 py-3 text-xs font-medium text-gray-500 uppercase">Waktu</th>
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
                            <td class="px-6 py-4 text-right font-semibold text-gray-800">
                                {{ $t->jenis === 'SETORAN' ? '+' : '-' }} Rp {{ number_format($t->jumlah_total, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada transaksi tercatat.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
