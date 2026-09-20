@extends('admin.layout')

@section('title', 'Edit Data Nasabah')
@section('page-title', 'Edit Data Nasabah')
@section('page-subtitle', 'Perbarui informasi identitas atau password nasabah')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.nasabah.show', $nasabah->id) }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-green-700 font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Kembali ke Detail Nasabah
    </a>
</div>

<div class="max-w-2xl bg-white rounded-2xl p-8 shadow-sm border border-gray-100">
    <form method="POST" action="{{ route('admin.nasabah.update', $nasabah->id) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
            <input type="text" name="nama" value="{{ old('nama', $nasabah->nama) }}" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('nama') border-red-500 @enderror">
            @error('nama')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Email</label>
            <input type="email" name="email" value="{{ old('email', $nasabah->email) }}" required
                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Telepon / WhatsApp</label>
            <input type="text" name="telepon" value="{{ old('telepon', $nasabah->telepon) }}"
                   placeholder="Contoh: 081234567890"
                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        {{-- ── WILAYAH & ALAMAT DOMISILI (RT, RW, DESA, KECAMATAN, KABUPATEN) ── --}}
        <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-3">
            <div class="flex items-center justify-between">
                <label class="block text-sm font-semibold text-gray-800">Alamat Domisili Warga</label>
                <span class="text-[11px] text-emerald-700 font-semibold bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Format RT / RW / Wilayah</span>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-2.5">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">RT</label>
                    <input type="text" id="nasabahRt" name="rt" value="{{ old('rt', $alamatData['rt'] ?? '') }}" placeholder="01" maxlength="5"
                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">RW</label>
                    <input type="text" id="nasabahRw" name="rw" value="{{ old('rw', $alamatData['rw'] ?? '') }}" placeholder="03" maxlength="5"
                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Desa / Kel.</label>
                    <input type="text" id="nasabahDesa" name="desa" value="{{ old('desa', $alamatData['desa'] ?? '') }}" placeholder="Karangrejo"
                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatNasabah()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kecamatan</label>
                    <input type="text" id="nasabahKecamatan" name="kecamatan" value="{{ old('kecamatan', $alamatData['kecamatan'] ?? '') }}" placeholder="Jati"
                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatNasabah()">
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kabupaten / Kota</label>
                    <input type="text" id="nasabahKabupaten" name="kabupaten" value="{{ old('kabupaten', $alamatData['kabupaten'] ?? '') }}" placeholder="Kudus"
                           class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                           oninput="updateAlamatNasabah()">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Jalan / Gang / No. Rumah (Opsional)</label>
                <input type="text" id="nasabahJalan" name="jalan" value="{{ old('jalan', $alamatData['jalan'] ?? '') }}" placeholder="Contoh: Jl. Melati No. 12, Samping Balai RW"
                       class="w-full bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 focus:outline-none"
                       oninput="updateAlamatNasabah()">
            </div>

            {{-- Hidden input alamat yang terkompilasi --}}
            <input type="hidden" id="nasabahAlamat" name="alamat" value="{{ old('alamat', $nasabah->alamat) }}">
            
            <div class="text-xs text-slate-600 bg-white p-2.5 rounded-xl border border-slate-200 flex items-center gap-2">
                <span class="font-bold text-slate-400 flex-shrink-0">Format Alamat:</span>
                <span id="previewNasabahAlamat" class="font-medium text-emerald-800 italic truncate">{{ old('alamat', $nasabah->alamat) ?: 'Belum diatur' }}</span>
            </div>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru (Opsional)</label>
            <p class="text-xs text-gray-500 mb-3">Kosongkan jika nasabah tidak meminta pergantian password.</p>
            <input type="password" name="password_baru" placeholder="Masukkan password baru jika ingin mereset"
                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
        </div>

        <div class="flex items-center justify-end gap-3 pt-4">
            <a href="{{ route('admin.nasabah.show', $nasabah->id) }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors shadow-sm">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

<script>
    function updateAlamatNasabah() {
        const rt = (document.getElementById('nasabahRt').value || '').trim();
        const rw = (document.getElementById('nasabahRw').value || '').trim();
        const desa = (document.getElementById('nasabahDesa').value || '').trim();
        const kec = (document.getElementById('nasabahKecamatan').value || '').trim();
        const kab = (document.getElementById('nasabahKabupaten').value || '').trim();
        const jalan = (document.getElementById('nasabahJalan').value || '').trim();

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
        document.getElementById('nasabahAlamat').value = finalAlamat || '-';
        document.getElementById('previewNasabahAlamat').textContent = finalAlamat || 'Belum diatur';
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateAlamatNasabah();
    });
</script>
@endsection
