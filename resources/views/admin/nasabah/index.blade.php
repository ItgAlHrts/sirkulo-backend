@extends('admin.layout')

@section('title', 'Data Nasabah Warga')
@section('page-title', 'Data Nasabah Warga')
@section('page-subtitle', 'Manajemen akun rekening bank sampah, saldo tabungan, dan informasi warga desa')

@section('content')

{{-- ── SEARCH & ACTION HEADER ─────────────────────────────────────── --}}
<div class="bg-white rounded-2xl p-4 mb-5 border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3">
    <form method="GET" action="{{ route('admin.nasabah.index') }}" class="w-full sm:w-auto flex-1 flex items-center gap-2">
        <div class="relative flex-1 max-w-md">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama, email, no. HP, atau kode..."
                   class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-9 pr-3 py-2 text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
        </div>
        <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex-shrink-0">
            Cari
        </button>
        @if(request('q'))
            <a href="{{ route('admin.nasabah.index') }}" class="border border-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium hover:bg-slate-50 transition flex-shrink-0">
                Reset
            </a>
        @endif
    </form>

    <div class="w-full sm:w-auto flex items-center justify-between sm:justify-end gap-2 text-xs">
        <span class="px-3 py-2 rounded-xl bg-slate-100 font-semibold text-slate-600">
            Total: {{ $nasabah->total() }} Nasabah
        </span>
        <a href="{{ route('admin.nasabah.create') }}" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Nasabah</span>
        </a>
    </div>
</div>

{{-- ── TABEL NASABAH ───────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
        <p class="text-xs text-slate-500">
            Menampilkan <strong>{{ $nasabah->firstItem() ?? 0 }}–{{ $nasabah->lastItem() ?? 0 }}</strong> dari <strong>{{ $nasabah->total() }}</strong> nasabah
            @if(request('q')) dengan kata kunci "<strong>{{ request('q') }}</strong>" @endif
        </p>
    </div>

    <div class="table-responsive">
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-[10.5px] font-semibold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                <tr>
                    <th class="px-5 py-3">Kode User</th>
                    <th class="px-5 py-3">Nama Nasabah</th>
                    <th class="px-5 py-3">Kontak & Alamat</th>
                    <th class="px-5 py-3 text-right">Saldo Kas</th>
                    <th class="px-5 py-3 text-center">Poin</th>
                    <th class="px-5 py-3">Terdaftar</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($nasabah as $n)
                <tr class="hover:bg-slate-50/70 transition">
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700">
                            {{ $n->kode_user }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 font-bold text-xs flex items-center justify-center flex-shrink-0">
                                {{ strtoupper(substr($n->nama, 0, 2)) }}
                            </div>
                            <div>
                                <span class="font-semibold text-slate-800 block text-xs">{{ $n->nama }}</span>
                                <span class="text-[11px] text-slate-400 block">{{ $n->email }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap">
                        <div>
                            <p class="font-medium text-slate-700">{{ $n->telepon ?: '-' }}</p>
                            <p class="text-slate-400 truncate max-w-xs text-[11px]">{{ $n->alamat ?: 'Belum diatur' }}</p>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-right">
                        <span class="font-bold text-slate-800">
                            Rp {{ number_format($n->saldo, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200/60">
                            {{ number_format($n->poin) }} Poin
                        </span>
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-slate-500 text-[11px]">
                        {{ \Carbon\Carbon::parse($n->dibuat_pada)->translatedFormat('d M Y') }}
                    </td>
                    <td class="px-5 py-3.5 whitespace-nowrap text-center">
                        <div class="flex items-center justify-center gap-1.5">
                            <a href="{{ route('admin.nasabah.show', $n->id) }}"
                               class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition" title="Detail Riwayat">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.nasabah.edit', $n->id) }}"
                               class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition" title="Edit Data">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('admin.nasabah.resetPassword', $n->id) }}"
                                  onsubmit="return confirm('Reset password {{ $n->nama }} ({{ $n->kode_user }}) ke default: password123?')">
                                @csrf
                                <button type="submit"
                                        class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition" title="Reset Password ke default (password123)">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.nasabah.destroy', $n->id) }}"
                                  onsubmit="return confirm('Hapus data nasabah {{ $n->nama }}? Saldo dan riwayat akan ikut dihapus.')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" title="Hapus Nasabah">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-8 text-center text-slate-400">
                        Tidak ada data nasabah yang sesuai kriteria pencarian.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($nasabah->hasPages())
    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/50">
        {{ $nasabah->links() }}
    </div>
    @endif
</div>
@endsection
