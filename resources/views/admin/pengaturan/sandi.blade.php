@extends('admin.layout')

@section('title', 'Pengaturan Kata Sandi Administrator')
@section('page-title', 'Pengaturan Akun')
@section('page-subtitle', 'Kelola kata sandi akun administrator Anda')

@section('content')
<div class="space-y-6 max-w-4xl">

    {{-- Page Header --}}
    <div>
        <h3 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Pengaturan & Keamanan Akun</h3>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Kelola kata sandi akun administrator Anda untuk menjaga keamanan akses sistem operasional desa.
        </p>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-center gap-3">
        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm">
        <div class="flex items-center gap-2 font-bold mb-1">
            <svg class="w-4 h-4 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            Periksa input Anda:
        </div>
        <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-700">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Kartu Ringkasan Akun Administrator --}}
        <div class="md:col-span-1 bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col items-center text-center">
            <div class="w-16 h-16 rounded-2xl {{ session('admin_peran') === 'SUPER_ADMIN' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-800' }} flex items-center justify-center font-bold text-xl mb-3 shadow-xs">
                {{ strtoupper(substr($admin->nama ?? session('admin_nama', 'AD'), 0, 2)) }}
            </div>
            
            <h4 class="font-bold text-slate-900 text-base leading-tight">{{ $admin->nama ?? session('admin_nama') }}</h4>
            <p class="text-xs text-slate-500 font-mono mt-1 break-all">{{ $admin->email ?? session('admin_email') }}</p>

            <div class="mt-3">
                @if(session('admin_peran') === 'SUPER_ADMIN')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                        Super Administrator
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Admin Operasional
                    </span>
                @endif
            </div>

            <div class="w-full mt-6 pt-6 border-t border-slate-100 text-left space-y-3 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Akses Panel</span>
                    <span class="text-slate-700 font-medium">Sistem Operasional Desa</span>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px] font-semibold uppercase tracking-wider">Terdaftar Sejak</span>
                    <span class="text-slate-700 font-medium font-mono">
                        {{ $admin && $admin->created_at ? $admin->created_at->translatedFormat('d F Y') : '—' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form Ganti Kata Sandi --}}
        <div class="md:col-span-2 bg-white rounded-2xl p-6 sm:p-7 border border-slate-200/80 shadow-xs">
            <div class="flex items-center gap-2.5 pb-4 mb-5 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 text-sm sm:text-base">Ganti Kata Sandi Akun</h4>
                    <p class="text-xs text-slate-400">Pastikan menggunakan kombinasi kata sandi yang kuat dan aman.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.pengaturan.updateSandi') }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Kata Sandi Saat Ini --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Saat Ini *</label>
                    <div class="relative">
                        <input type="password" id="inputCurrentPassword" name="password_sekarang" required
                               placeholder="Masukkan kata sandi lama Anda"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button" onclick="togglePasswordVisibility('inputCurrentPassword')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Kata Sandi Baru --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi Baru *</label>
                    <div class="relative">
                        <input type="password" id="inputNewPassword" name="password_baru" required minlength="6"
                               placeholder="Minimal 6 karakter"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button" onclick="togglePasswordVisibility('inputNewPassword')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Konfirmasi Kata Sandi Baru --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Kata Sandi Baru *</label>
                    <div class="relative">
                        <input type="password" id="inputConfirmPassword" name="password_baru_confirmation" required minlength="6"
                               placeholder="Ketik ulang kata sandi baru"
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl pl-3.5 pr-10 py-2.5 text-xs sm:text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <button type="button" onclick="togglePasswordVisibility('inputConfirmPassword')"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-4 flex items-center justify-end border-t border-slate-100">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold text-white bg-slate-900 hover:bg-slate-800 shadow-sm hover:shadow transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Kata Sandi Baru
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    function togglePasswordVisibility(id) {
        const el = document.getElementById(id);
        if (el) {
            el.type = el.type === 'password' ? 'text' : 'password';
        }
    }
</script>
@endpush
