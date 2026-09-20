@extends('admin.layout')

@section('title', 'Tambah Nasabah Baru')
@section('page-title', 'Pendaftaran Nasabah Warga')
@section('page-subtitle', 'Daftarkan akun warga penabung bank sampah resmi desa')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.nasabah.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-emerald-700 font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Daftar Nasabah
    </a>
</div>

<div class="max-w-2xl bg-white rounded-3xl p-8 shadow-xs border border-slate-200/80">
    <div class="flex items-center gap-3.5 mb-6 pb-5 border-b border-slate-100">
        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <div>
            <h3 class="text-base font-bold text-slate-900">Form Pendaftaran Akun Nasabah</h3>
            <p class="text-xs text-slate-500">Semua pendaftaran nasabah dikelola terpusat oleh Admin Desa untuk integritas data</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.nasabah.store') }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nama Lengkap Warga *</label>
            <input type="text" name="nama" value="{{ old('nama') }}" required placeholder="Contoh: Siti Rahmawati"
                   class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('nama') border-rose-500 @enderror">
            @error('nama')
                <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Alamat Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="siti@gmail.com"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('email') border-rose-500 @enderror">
                @error('email')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Nomor HP / WhatsApp *</label>
                <input type="text" name="telepon" value="{{ old('telepon') }}" required placeholder="081234567890"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('telepon') border-rose-500 @enderror">
                @error('telepon')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kata Sandi Login *</label>
                <input type="password" name="kata_sandi" required minlength="6" placeholder="Min. 6 karakter"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 @error('kata_sandi') border-rose-500 @enderror">
                @error('kata_sandi')
                    <p class="text-xs text-rose-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Saldo Awal Tabungan (Rp)</label>
                <input type="number" name="saldo" value="{{ old('saldo', 0) }}" min="0" placeholder="0"
                       class="w-full bg-slate-50 border border-slate-200 rounded-2xl px-4 py-3 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <p class="text-[11px] text-slate-400 mt-1">Default Rp 0 (Poin otomatis dihitung Rp 100 = 1 Poin)</p>
            </div>
        </div>

        {{-- ── WILAYAH & ALAMAT DOMISILI (RT, RW, DESA, KECAMATAN, KABUPATEN) ── --}}
        <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Alamat Domisili Warga</label>
                <span class="text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Format RT / RW / Wilayah</span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">RT</label>
                    <input type="text" id="tambahNasabahRt" name="rt" value="{{ old('rt') }}" placeholder="01" maxlength="5"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateTambahAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">RW</label>
                    <input type="text" id="tambahNasabahRw" name="rw" value="{{ old('rw') }}" placeholder="03" maxlength="5"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateTambahAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Desa / Kel.</label>
                    <input type="text" id="tambahNasabahDesa" name="desa" value="{{ old('desa') }}" placeholder="Karangrejo"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateTambahAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kecamatan</label>
                    <input type="text" id="tambahNasabahKecamatan" name="kecamatan" value="{{ old('kecamatan') }}" placeholder="Jati"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateTambahAlamatNasabah()">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kabupaten / Kota</label>
                    <input type="text" id="tambahNasabahKabupaten" name="kabupaten" value="{{ old('kabupaten') }}" placeholder="Kudus"
                           class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateTambahAlamatNasabah()">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Jalan / Gang / No. Rumah (Opsional)</label>
                <input type="text" id="tambahNasabahJalan" name="jalan" value="{{ old('jalan') }}" placeholder="Contoh: Jl. Melati No. 12, Samping Balai RW"
                       class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                       oninput="updateTambahAlamatNasabah()">
            </div>

            {{-- Hidden input alamat yang terkompilasi --}}
            <input type="hidden" id="tambahNasabahAlamat" name="alamat" value="{{ old('alamat', '-') }}">
            
            <div class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 flex items-center gap-2">
                <span class="font-bold text-slate-400 flex-shrink-0">Format Alamat:</span>
                <span id="previewTambahNasabahAlamat" class="font-medium text-emerald-800 italic truncate">{{ old('alamat') ?: 'Belum diatur' }}</span>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
            <a href="{{ route('admin.nasabah.index') }}" class="px-5 py-2.5 rounded-2xl border border-slate-200 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-2.5 rounded-2xl text-sm font-bold transition-all shadow-xs hover:-translate-y-0.5">
                Daftarkan Nasabah
            </button>
        </div>
    </form>
</div>

<script>
    function updateTambahAlamatNasabah() {
        const rt = (document.getElementById('tambahNasabahRt').value || '').trim();
        const rw = (document.getElementById('tambahNasabahRw').value || '').trim();
        const desa = (document.getElementById('tambahNasabahDesa').value || '').trim();
        const kec = (document.getElementById('tambahNasabahKecamatan').value || '').trim();
        const kab = (document.getElementById('tambahNasabahKabupaten').value || '').trim();
        const jalan = (document.getElementById('tambahNasabahJalan').value || '').trim();

        const parts = [];
        if (rt || rw) {
            parts.push('RT ' + (rt || '-') + ' / RW ' + (rw || '-'));
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
        if (jalan) {
            parts.push(jalan);
        }

        const finalAlamat = parts.join(', ');
        document.getElementById('tambahNasabahAlamat').value = finalAlamat || '-';
        document.getElementById('previewTambahNasabahAlamat').textContent = finalAlamat || 'Belum diatur';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateTambahAlamatNasabah();
    });
</script>
@endsection
