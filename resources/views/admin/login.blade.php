<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#032e22">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="icon" type="image/png" href="{{ url('/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ url('/logo.png') }}">
    <title>Login Administrator — SIRKULO Bank Sampah Desa</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
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
        * { box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            margin: 0; 
            min-height: 100vh; 
            background: #f8fafc;
            overflow-x: hidden;
        }
        @media (min-width: 1024px) {
            body {
                height: 100vh;
                overflow: hidden;
            }
        }

        /* Clean dark left panel */
        .left-panel {
            background: linear-gradient(160deg, #032e22 0%, #054a37 50%, #02241b 100%);
        }

        /* Clean right panel */
        .right-panel {
            background: #f8fafc;
            overflow-y: auto;
        }

        /* Input */
        .input-field {
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 16px 12px 44px;
            font-size: 14px;
            color: #0f172a;
            width: 100%;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.12);
        }

        /* Login button */
        .btn-login {
            background: linear-gradient(135deg, #059669, #047857);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-login:hover {
            background: linear-gradient(135deg, #10b981, #059669);
            box-shadow: 0 4px 16px rgba(5,150,105,0.3);
            transform: translateY(-1px);
        }


        /* Feature list item */
        .feature-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .feature-row:last-child { border-bottom: none; }
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
                    Platform Bank Sampah Digital
                </span>
                <h2 class="font-display font-black text-4xl text-white leading-[1.15] tracking-tight mb-4">
                    Kelola Bank Sampah<br>
                    <span class="text-emerald-400">Lebih Cerdas.</span>
                </h2>
                <p class="text-emerald-100/60 text-sm leading-relaxed max-w-sm">
                    Sistem terpadu manajemen bank sampah, tabungan warga, dan koordinasi pos timbangan berbasis teknologi modern.
                </p>

                <!-- Feature List -->
                <div class="mt-8 space-y-0">
                    <div class="feature-row">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 border border-emerald-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Pemisahan Hak Akses 3-Tier</p>
                            <p class="text-xs text-emerald-200/50">Super Admin · Admin Operasional · Mitra Pos</p>
                        </div>
                    </div>
                    <div class="feature-row">
                        <div class="w-8 h-8 rounded-xl bg-teal-500/15 border border-teal-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-teal-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Laporan & Rekap Otomatis</p>
                            <p class="text-xs text-emerald-200/50">Grafik kas, transaksi, dan CSV untuk DLH</p>
                        </div>
                    </div>
                    <div class="feature-row">
                        <div class="w-8 h-8 rounded-xl bg-amber-500/15 border border-amber-400/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Aplikasi Mobile Nasabah</p>
                            <p class="text-xs text-emerald-200/50">API REST sinkron real-time di Android</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p class="text-xs text-emerald-300/40">
                v1.0.0 · SIRKULO Bank Sampah Desa · Terisolasi &amp; Aman
            </p>
        </div>

        <!-- ── RIGHT PANEL: LOGIN FORM ──────────────────────── -->
        <div class="flex-1 right-panel flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-[420px]">
                
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

                <!-- Welcome Text -->
                <div class="mb-7">
                    <h1 class="font-display font-black text-3xl text-slate-900 tracking-tight">Masuk ke Panel</h1>
                    <p class="text-slate-500 text-sm mt-1.5">
                        Kelola operasional Bank Sampah Desa SIRKULO dari sini.
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

                <!-- Login Form -->
                <form method="POST" action="{{ route('admin.login.post', [], false) }}" class="space-y-4">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1.5">Email Administrator</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="emailInput" name="email" 
                                   value="{{ old('email') }}" required autofocus
                                   placeholder="nama@email.com"
                                   autocomplete="email"
                                   class="input-field">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-600">Kata Sandi</label>
                            <a href="{{ route('admin.lupa-password') }}" class="text-xs font-semibold text-emerald-600 hover:text-emerald-700 hover:underline transition-colors">
                                Lupa Kata Sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input type="password" id="passwordInput" name="password" 
                                   required
                                   placeholder="••••••••"
                                   autocomplete="current-password"
                                   class="input-field" style="padding-right: 44px;">
                            <button type="button" onclick="togglePwd()" 
                                     class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition">
                                <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn-login mt-1">
                        Masuk ke Panel Kendali
                    </button>
                </form>

                <!-- Security Info Footer -->
                <div class="mt-6 pt-5 border-t border-slate-200 flex items-center justify-between text-xs text-slate-400">
                    <div class="flex items-center gap-1.5 text-emerald-700 font-medium">
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <span>Koneksi Aman & Terenkripsi SSL</span>
                    </div>
                    <span class="text-slate-400 font-semibold tracking-wider text-[11px]">SIRKULO v1.0</span>
                </div>

            </div>
        </div>

    </div>

    <script>
        function togglePwd() {
            const pw = document.getElementById('passwordInput');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
