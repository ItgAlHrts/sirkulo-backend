@extends('admin.layout')

@section('title', 'Kelola Administrator Desa')
@section('page-title', 'Administrator Desa')
@section('page-subtitle', 'Manajemen akun pengelola web panel dan tingkat hak akses sistem')

@section('content')


{{-- ── 2 CARD ADMINISTRATOR DESA ───────────────────────────────────────── --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    @forelse($admins->sortByDesc('peran') as $a)
    @php
        $isSelf = $a->id === session('admin_id');
        $isSuper = $a->peran === 'SUPER_ADMIN';
    @endphp
    <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden">
        {{-- Garis aksen warna atas --}}
        <div class="absolute top-0 inset-x-0 h-1.5 {{ $isSuper ? 'bg-gradient-to-r from-amber-400 to-amber-600' : 'bg-gradient-to-r from-emerald-400 to-emerald-600' }}"></div>

        <div>
            {{-- Header Card: Avatar, Nama, Tag Anda & Badge Peran --}}
            <div class="flex items-start justify-between gap-3 mb-5 pt-1">
                <div class="flex items-center gap-3.5">
                    <div class="w-13 h-13 rounded-2xl flex items-center justify-center font-bold text-base flex-shrink-0 {{ $isSuper ? 'bg-amber-100 text-amber-900 border border-amber-200/80' : 'bg-emerald-100 text-emerald-900 border border-emerald-200/80' }}" style="width: 50px; height: 50px;">
                        {{ strtoupper(substr($a->nama, 0, 2)) }}
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-bold text-slate-900 text-base leading-tight">{{ $a->nama }}</h3>
                            @if($isSelf)
                                <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md border border-emerald-200">Anda</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">{{ $a->email }}</p>
                    </div>
                </div>

                {{-- Badge Peran --}}
                @if($isSuper)
                    <span class="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200/80 font-bold text-xs rounded-xl inline-flex items-center gap-1.5 flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span>Super Administrator</span>
                    </span>
                @else
                    <span class="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold text-xs rounded-xl inline-flex items-center gap-1.5 flex-shrink-0">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span>Admin Operasional</span>
                    </span>
                @endif
            </div>

            {{-- Detail Informasi Akun --}}
            <div class="space-y-3 py-4 border-y border-slate-100 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Nomor Kontak / WA</span>
                    <span class="font-mono font-semibold text-slate-800">{{ $a->telepon ?: '—' }}</span>
                </div>
                <div class="flex items-start justify-between gap-4">
                    <span class="text-slate-400 font-medium flex-shrink-0">Wewenang Sistem</span>
                    <span class="text-right font-medium text-slate-700">
                        {{ $isSuper ? 'Akses Penuh Sistem, Kelola Staf & Basis Data' : 'Operasional Bank Sampah & Pelayanan Warga' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400 font-medium">Status Akun</span>
                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif Terhubung
                    </span>
                </div>
            </div>
        </div>

        {{-- Toolbar Aksi Bawah --}}
        <div class="pt-5 mt-2 flex items-center justify-end gap-2 flex-wrap">
            @if($isSelf)
                {{-- Akun Pribadi: Tombol Ganti Sandi Saya --}}
                <a href="{{ route('admin.pengaturan.sandi') }}"
                   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors shadow-2xs"
                   title="Buka Pengaturan Kata Sandi Pribadi Anda">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    <span>Ganti Sandi Saya</span>
                </a>
            @else
                {{-- Akun Administrator Lain: Reset Sandi & Promote/Demote --}}
                @if(session('admin_peran') === 'SUPER_ADMIN')
                    <button type="button"
                            onclick="bukaModalResetSandi('{{ $a->id }}', '{{ addslashes($a->nama) }}', '{{ addslashes($a->email) }}', '{{ $isSuper ? 'Super Administrator' : 'Admin Operasional' }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 transition-colors"
                            title="Reset kata sandi akun administrator ini">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Reset Sandi</span>
                    </button>

                    @if($a->peran === 'ADMIN')
                        <form method="POST" action="{{ route('admin.administrator.promote', $a->id) }}"
                              onsubmit="return confirm('Jadikan {{ $a->nama }} sebagai Super Administrator?');" class="inline">
                            @csrf @method('PUT')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-amber-800 bg-amber-50 hover:bg-amber-100 border border-amber-200/80 transition-colors" title="Naikkan wewenang ke Super Administrator">
                                <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
                                </svg>
                                <span>Promote</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('admin.administrator.demote', $a->id) }}"
                              onsubmit="return confirm('Turunkan {{ $a->nama }} menjadi Admin Operasional?');" class="inline">
                            @csrf @method('PUT')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-200 transition-colors" title="Turunkan wewenang ke Admin Operasional">
                                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/>
                                </svg>
                                <span>Demote</span>
                            </button>
                        </form>
                    @endif
                @endif
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-2 bg-white rounded-2xl p-12 text-center text-xs text-slate-400 border border-slate-200">
        Tidak ada akun administrator terdaftar.
    </div>
    @endforelse
</div>

{{-- ── MODAL: RESET PASSWORD ADMINISTRATOR ─────────────────────────────── --}}
<div id="modalResetSandi" class="fixed inset-0 z-50 hidden bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4 transition-all">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in duration-150">
        <div class="flex items-start justify-between pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Reset Sandi Administrator</h4>
                    <p class="text-xs text-slate-500 mt-0.5">Atur ulang kata sandi baru untuk staf admin</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalResetSandi')" class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none p-1 rounded-lg hover:bg-slate-100 transition-colors">&times;</button>
        </div>

        <form id="formResetSandi" method="POST" action="" class="space-y-4 mt-4">
            @csrf
            @method('PUT')
            
            <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Target Akun Administrator</span>
                <div class="flex items-center justify-between gap-2">
                    <div>
                        <p class="font-bold text-slate-900 text-sm" id="targetNamaSandi"></p>
                        <p class="text-xs text-slate-500 font-mono" id="targetEmailSandi"></p>
                    </div>
                    <span id="targetPeranBadge" class="px-2.5 py-1 rounded-lg text-[11px] font-bold bg-slate-200/70 text-slate-700"></span>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-bold text-slate-700">Kata Sandi Baru *</label>
                    <button type="button" onclick="buatSandiAcak()" class="text-[11px] font-semibold text-emerald-600 hover:text-emerald-700 hover:underline inline-flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Buat Sandi Acak</span>
                    </button>
                </div>
                <div class="relative">
                    <input type="password" id="inputPasswordBaru" name="password_baru" required minlength="6" placeholder="Masukkan minimal 6 karakter"
                           class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-20 py-2.5 text-xs sm:text-sm font-mono focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                        <button type="button" onclick="salinSandi()" title="Salin kata sandi"
                                class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-200/60 transition-colors">
                            <svg id="iconCopy" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <svg id="iconCopied" class="w-4 h-4 text-emerald-600 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>
                        <button type="button" onclick="togglePasswordVisibility('inputPasswordBaru')" title="Lihat kata sandi"
                                class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg hover:bg-slate-200/60 transition-colors">
                            <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-1.5">
                    <span>Minimal 6 karakter kombinasi aman.</span>
                    <span id="copyToast" class="text-emerald-600 font-semibold hidden">Tersalin ke clipboard!</span>
                </div>
            </div>

            <div class="p-3 bg-amber-50/60 rounded-xl border border-amber-200/60 text-xs text-amber-800 flex items-start gap-2">
                <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Kata sandi baru akan langsung berlaku. Berikan kata sandi baru ini kepada pengelola akun terkait.</span>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modalResetSandi')" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors shadow-xs">Terapkan Sandi Baru</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.remove('hidden');
    }

    function closeModal(id) {
        const modal = document.getElementById(id);
        if (modal) modal.classList.add('hidden');
    }

    function bukaModalResetSandi(id, nama, email, peran) {
        document.getElementById('targetNamaSandi').innerText = nama;
        document.getElementById('targetEmailSandi').innerText = email;
        
        const badge = document.getElementById('targetPeranBadge');
        badge.innerText = peran;
        if (peran.includes('Super')) {
            badge.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-amber-100 text-amber-800';
        } else {
            badge.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 text-emerald-800';
        }

        document.getElementById('formResetSandi').action = '/admin/administrator/' + id + '/password';
        document.getElementById('inputPasswordBaru').value = '';
        document.getElementById('inputPasswordBaru').type = 'password';
        document.getElementById('copyToast').classList.add('hidden');
        openModal('modalResetSandi');
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.type = (input.type === 'password') ? 'text' : 'password';
    }

    function buatSandiAcak() {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%';
        let randomPass = 'Desa#';
        for (let i = 0; i < 5; i++) {
            randomPass += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        const input = document.getElementById('inputPasswordBaru');
        input.value = randomPass;
        input.type = 'text'; // tampilkan agar langsung terlihat
        salinSandi();
    }

    function salinSandi() {
        const input = document.getElementById('inputPasswordBaru');
        if (!input.value) return;
        
        navigator.clipboard.writeText(input.value).then(() => {
            const toast = document.getElementById('copyToast');
            const iconCopy = document.getElementById('iconCopy');
            const iconCopied = document.getElementById('iconCopied');
            
            toast.classList.remove('hidden');
            iconCopy.classList.add('hidden');
            iconCopied.classList.remove('hidden');
            
            setTimeout(() => {
                toast.classList.add('hidden');
                iconCopy.classList.remove('hidden');
                iconCopied.classList.add('hidden');
            }, 2500);
        });
    }
</script>
@endpush
