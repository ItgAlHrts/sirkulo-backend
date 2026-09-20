<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Kata Sandi Baru — SIRKULO Bank Sampah Desa</title>
    <link rel="icon" type="image/png" href="{{ url('/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .left-panel {
            background: linear-gradient(145deg, #064e3b 0%, #065f46 45%, #047857 100%);
            position: relative;
            overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute;
            top: -15%; right: -15%;
            width: 480px; height: 480px;
            background: radial-gradient(circle, rgba(52,211,153,0.18) 0%, transparent 70%);
            pointer-events: none;
        }
        .right-panel {
            background: #f8fafc;
        }
        .input-field {
            width: 100%;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 11px 16px 11px 40px;
            font-size: 13.5px;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }
        .input-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3.5px rgba(16,185,129,0.12);
        }
        .btn-action {
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            border-radius: 12px;
            padding: 13px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-action:hover {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 16px rgba(5,150,105,0.3);
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="text-slate-800">

    <div class="flex h-screen">

        <!-- ── LEFT PANEL ──────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-[46%] left-panel flex-col justify-between p-10 xl:p-14">
            <!-- Brand -->
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-white p-2 flex items-center justify-center shadow-lg shadow-emerald-950/30 border border-white/40 flex-shrink-0">
                    <img src="{{ url('/logo.png') }}" alt="SIRKULO Logo" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="font-display font-black text-2xl tracking-tight text-white block leading-none">SIRKULO</span>
                    <span class="text-[10.5px] font-semibold uppercase tracking-[0.15em] text-emerald-200 mt-1 block">Portal Administrator Desa</span>
                </div>
            </div>

            <!-- Hero Content -->
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-400/25 text-emerald-300 text-xs font-semibold mb-5 block w-fit">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Langkah 3 dari 3: Buat Kata Sandi Baru
                </span>
                <h2 class="font-display font-black text-4xl text-white leading-[1.15] tracking-tight mb-4">
                    Atur Kata Sandi<br>
                    <span class="text-emerald-400">Super Admin.</span>
                </h2>
                <p class="text-emerald-100/70 text-sm leading-relaxed max-w-sm">
                    Kode OTP telah terverifikasi dengan sukses. Sekarang tentukan kata sandi baru yang aman untuk akun Super Administrator Anda.
                </p>
            </div>

            <!-- Footer note -->
            <p class="text-xs text-emerald-300/40">
                v1.0.0 · SIRKULO Bank Sampah Desa · Terisolasi &amp; Aman
            </p>
        </div>

        <!-- ── RIGHT PANEL: NEW PASSWORD FORM ──────────────── -->
        <div class="flex-1 right-panel flex items-center justify-center p-6 sm:p-10 overflow-y-auto">
            <div class="w-full max-w-[420px] py-6">
                
                <!-- Mobile Brand -->
                <div class="flex lg:hidden items-center gap-3.5 mb-8">
                    <div class="w-14 h-14 rounded-2xl bg-white p-2 flex items-center justify-center shadow-md border border-slate-200 flex-shrink-0">
                        <img src="{{ url('/logo.png') }}" alt="SIRKULO Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="font-display font-black text-2xl text-slate-900 tracking-tight">SIRKULO</span>
                        <p class="text-[10.5px] font-bold uppercase tracking-widest text-emerald-600 mt-0.5">Portal Admin</p>
                    </div>
                </div>

                <!-- Header Title -->
                <div class="mb-5">
                    <a href="{{ route('admin.login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 hover:text-emerald-800 mb-3 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <span>Batalkan &amp; Kembali ke Login</span>
                    </a>
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-xs font-bold">✓</span>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-700">OTP Terverifikasi</span>
                    </div>
                    <h1 class="font-display font-black text-3xl text-slate-900 tracking-tight">Kata Sandi Baru</h1>
                    <p class="text-slate-500 text-xs mt-1 leading-relaxed">
                        Silakan buat kata sandi baru minimal 8 karakter untuk akun Super Administrator.
                    </p>
                </div>

                <!-- Alerts -->
                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-200 text-rose-800 text-xs rounded-xl px-4 py-3 mb-4 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-rose-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium leading-relaxed">{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs rounded-xl px-4 py-3 mb-4 flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                <!-- Form Ganti Sandi Baru -->
                <form method="POST" action="{{ route('admin.lupa-password.ganti-sandi.post') }}" class="space-y-4">
                    @csrf

                    <!-- Kata Sandi Baru -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="passwordInput" name="password" 
                                   required minlength="8" autofocus
                                   placeholder="Minimal 8 karakter"
                                   class="input-field" style="padding-right: 44px;">
                            <button type="button" onclick="togglePwd('passwordInput', 'eyeIcon1')" 
                                     class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg id="eyeIcon1" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Konfirmasi Kata Sandi Baru -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">Ulangi Kata Sandi Baru</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                            </span>
                            <input type="password" id="passwordConfirmInput" name="password_confirmation" 
                                   required minlength="8"
                                   placeholder="Ulangi kata sandi baru"
                                   class="input-field" style="padding-right: 44px;">
                            <button type="button" onclick="togglePwd('passwordConfirmInput', 'eyeIcon2')" 
                                     class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg id="eyeIcon2" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-action shadow-md flex items-center justify-center gap-2 mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Simpan Kata Sandi Baru &amp; Selesai</span>
                    </button>
                </form>

                <!-- Security Info Footer -->
                <div class="mt-8 pt-5 border-t border-slate-200 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Terenkripsi SSL &amp; Aman</span>
                    </div>
                    <span class="text-slate-400 font-semibold tracking-wider text-[11px]">SIRKULO v1.0</span>
                </div>

            </div>
        </div>

    </div>

    <script>
        function togglePwd(fieldId, iconId) {
            const pw = document.getElementById(fieldId);
            const icon = document.getElementById(iconId);
            if (pw.type === 'password') {
                pw.type = 'text';
            } else {
                pw.type = 'password';
            }
        }
    </script>
</body>
</html>
