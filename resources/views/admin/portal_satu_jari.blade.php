@extends('admin.layout')

@section('title', 'Portal Satu Jari DLH')
@section('page-title', 'Portal Satu Jari DLH')
@section('page-subtitle', 'Panduan dan akses pelaporan digital terintegrasi ke Dinas Lingkungan Hidup')

@push('styles')
<style>
    .guide-step-number {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 15px;
        flex-shrink: 0;
    }
    .credential-badge {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        background: #f1f5f9;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 13px;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">


    <!-- ── Akun & Kredensial Otomatis ───────────────────────────────── -->
    <div class="bg-white rounded-2xl p-5 sm:p-6 border border-slate-200/80 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <h2 class="font-display font-bold text-base text-slate-800 flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Informasi Akun Terdaftar di DLH
                </h2>
                <p class="text-slate-500 text-xs mt-0.5">Tautan tombol di halaman ini sudah terkonfigurasi dengan tautan masuk otomatis (Direct Link).</p>
            </div>

        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-4">
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Email / Identitas</span>
                <span class="credential-badge font-semibold text-emerald-800 truncate" title="{{ $email }}">{{ $email }}</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex flex-col justify-between">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Password / Sandi</span>
                    @if(session('admin_peran') === 'SUPER_ADMIN')
                    <button type="button" onclick="openModalKredensial()" class="text-[11px] font-bold text-amber-600 hover:text-amber-700 underline">Ubah</button>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span class="credential-badge font-bold text-slate-800 tracking-wider">{{ $password }}</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase">DLH</span>
                </div>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Unit Pengelola</span>
                <span class="text-sm font-bold text-slate-800 leading-snug">Bank Sampah Srikandi Berdikari</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/60 flex flex-col justify-between">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1">Status Kemitraan</span>
                <span class="text-sm font-bold text-emerald-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Aktif (Dinas Lingkungan Hidup)
                </span>
            </div>
        </div>
    </div>

    <!-- ── Langkah-Langkah Panduan Pelaporan ────────────────────────── -->
    <div class="bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-sm space-y-6">
        <div>
            <h2 class="font-display font-extrabold text-lg text-slate-800">
                Panduan Langkah Pelaporan ke DLH
            </h2>
            <p class="text-slate-500 text-sm mt-1">
                Ikuti langkah-langkah berikut untuk melaporkan data penimbangan sampah desa secara valid:
            </p>
        </div>

        <div class="space-y-4">
            <!-- Langkah 1 -->
            <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                <div class="guide-step-number bg-emerald-100 text-emerald-800">1</div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Siapkan & Cross-Check Data dari SIRKULO</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Buka menu <a href="{{ route('admin.laporan.index') }}" class="text-emerald-700 font-semibold underline hover:text-emerald-800">Laporan & Rekap</a> di aplikasi SIRKULO ini. Catat atau unduh rekap tonase sampah terkumpul (kg) dan persentase kategori jenis sampah (Plastik, Kertas, Logam, Kaca, dll) untuk periode yang akan dilaporkan.
                    </p>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                <div class="guide-step-number bg-emerald-100 text-emerald-800">2</div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Buka Portal Satu Jari melalui Tautan Resmi</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Klik tombol <strong>"Buka Portal Satu Jari DLH"</strong> di bagian bawah halaman ini. Tautan sudah terkonfigurasi dengan login langsung. Jika diminta masuk secara manual, gunakan Email: <code class="bg-slate-100 px-1 py-0.5 rounded text-emerald-800 font-mono">{{ $email }}</code> dan Password: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800 font-mono font-bold">{{ $password }}</code>.
                    </p>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                <div class="guide-step-number bg-emerald-100 text-emerald-800">3</div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Pilih Form Laporan Periode Berjalan</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Di dalam Portal Satu Jari, pilih menu <strong>Input Laporan Berkala</strong> (Bulanan / Triwulanan). Pastikan memilih bulan dan tahun laporan yang sesuai dengan data pembukuan Anda.
                    </p>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                <div class="guide-step-number bg-emerald-100 text-emerald-800">4</div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Inputkan Rekapitulasi Tonase Sampah</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Masukkan berat total sampah dalam satuan kilogram (kg) sesuai kategori yang diminta DLH. Bila ada data residu atau sampah yang disalurkan ke pengepul / industri daur ulang, masukkan pada kolom mitra off-taker.
                    </p>
                </div>
            </div>

            <!-- Langkah 5 -->
            <div class="flex gap-4 p-4 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-50 transition-colors">
                <div class="guide-step-number bg-emerald-100 text-emerald-800">5</div>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-slate-800">Kirim & Unduh Bukti Pelaporan</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Periksa kembali ringkasan sebelum klik tombol Kirim. Setelah laporan berhasil terkirim, unduh lembar konfirmasi / bukti pelaporan digital untuk disimpan sebagai arsip administrasi desa.
                    </p>
                </div>
            </div>
        </div>

        <!-- ── Call to Action Box ──────────────────────────────────── -->
        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-slate-800">Sudah siap mengisi laporan?</p>
                <p class="text-xs text-slate-500">Klik tombol di samping untuk langsung diarahkan ke portal DLH.</p>
            </div>
            <a href="{{ $portalUrl }}"
               target="_blank"
               rel="noopener noreferrer"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-6 py-3.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-md hover:shadow-lg transition-all">
                <span>Buka Portal Satu Jari DLH Sekarang</span>
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- ── Informasi Bantuan & Hotline DLH ──────────────────────────── -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="p-5 rounded-2xl bg-amber-50/80 border border-amber-200/80">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center flex-shrink-0 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-amber-900">Batas Waktu Pelaporan</h4>
                    <p class="text-xs text-amber-800/90 mt-1 leading-relaxed">
                        Laporan bulanan ke DLH dianjurkan diserahkan selambat-lambatnya tanggal <strong>5 setiap bulannya</strong> agar rekapitulasi data sampah di tingkat kabupaten/kota tetap mutakhir.
                    </p>
                </div>
            </div>
        </div>

        <div class="p-5 rounded-2xl bg-sky-50/80 border border-sky-200/80">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center flex-shrink-0 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-sky-900">Kendala Teknis & Akses Akun</h4>
                    <p class="text-xs text-sky-800/90 mt-1 leading-relaxed">
                        Jika tautan otomatis mengalami kegagalan login atau sesi kedaluwarsa, hubungi staf pendamping DLH bidang Pengelolaan Sampah dan Limbah B3 setempat.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>

@if(session('admin_peran') === 'SUPER_ADMIN')
<!-- ── Modal Ubah Kredensial Portal DLH (Khusus Super Admin) ─────── -->
<div id="modalKredensial" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden transition-all" role="dialog" aria-modal="true">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
        {{-- Header Modal --}}
        <div class="px-6 py-4.5 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-display font-bold text-base text-white leading-tight">Ubah Kredensial DLH</h3>
                    <p class="text-[11px] text-amber-300/90 font-medium">Hak Akses: Super Administrator</p>
                </div>
            </div>
            <button type="button" onclick="closeModalKredensial()" 
                    class="w-8 h-8 rounded-lg hover:bg-white/10 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form Modal --}}
        <form action="{{ route('admin.portal-satu-jari.update') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Email Akun DLH
                </label>
                <div class="relative">
                    <input type="email" name="email" value="{{ old('email', $email) }}" required
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">Email yang digunakan saat login ke Portal Satu Jari.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                    Password / Sandi Baru
                </label>
                <div class="relative">
                    <input type="text" name="password" id="inputModalPassword" value="{{ old('password', $password) }}" required minlength="4"
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl border border-slate-300 text-sm font-mono font-bold tracking-wider text-slate-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">
                    Password ini akan langsung ditampilkan di panduan dan disematkan pada tautan login otomatis.
                </p>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200/80 text-[11.5px] text-amber-900 leading-relaxed">
                <strong>Catatan:</strong> Jika Anda memperbarui password ini, staf/mitra yang membaca panduan akan langsung melihat password terbaru.
            </div>

            <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                <button type="button" onclick="closeModalKredensial()" 
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModalKredensial() {
        const modal = document.getElementById('modalKredensial');
        if (modal) {
            modal.classList.remove('hidden');
            const pwdInput = document.getElementById('inputModalPassword');
            if (pwdInput) {
                setTimeout(() => pwdInput.focus(), 100);
            }
        }
    }

    function closeModalKredensial() {
        const modal = document.getElementById('modalKredensial');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    // Tutup saat menekan tombol Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeModalKredensial();
        }
    });
</script>
@endif
@endsection
