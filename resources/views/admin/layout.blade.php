<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="theme-color" content="#032e22">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" type="image/png" href="{{ url('/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ url('/logo.png') }}">
    <title>@yield('title', 'Admin Panel') — SIRKULO Bank Sampah Desa</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Outfit"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0',
                            300: '#6ee7b7', 400: '#34d399', 500: '#10b981',
                            600: '#059669', 700: '#047857', 800: '#065f46',
                            900: '#064e3b', 950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- QR Code Generator (Laporan Verifikasi) -->
    <script src="/js/qrcode.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
        }
        .font-display { font-family: 'Outfit', sans-serif; }

        @media print {
            .sidebar, .topbar, .page-footer, .clock-badge, .flash-success, .flash-error, nav, header {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                min-height: auto !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                padding: 0 !important;
                background: transparent !important;
                display: block !important;
            }
            .page-body {
                padding: 0 !important;
            }
        }

        /* ── SIDEBAR ───────────────────────────────────── */
        .sidebar {
            width: 250px; flex-shrink: 0;
            background: linear-gradient(170deg, #032e22 0%, #054a37 40%, #043e30 70%, #02241b 100%);
            position: fixed; top: 0; left: 0; bottom: 0;
            display: flex; flex-direction: column;
            border-right: 1px solid rgba(255,255,255,0.06);
            box-shadow: 4px 0 30px rgba(0,0,0,0.3);
            z-index: 50;
            overflow: hidden;
            transition: transform 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Decorative sidebar glow */
        .sidebar::before {
            content: '';
            position: absolute;
            top: -100px; left: -100px;
            width: 350px; height: 350px;
            background: radial-gradient(circle, rgba(16,185,129,0.12) 0%, transparent 65%);
            pointer-events: none;
        }
        .sidebar::after {
            content: '';
            position: absolute;
            bottom: 0; right: -80px;
            width: 250px; height: 250px;
            background: radial-gradient(circle, rgba(20,184,166,0.08) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Sidebar scroll */
        .sidebar-nav {
            overflow-y: auto; flex: 1;
            scrollbar-width: none;
        }
        .sidebar-nav::-webkit-scrollbar { display: none; }

        /* Nav group label */
        .nav-group-label {
            font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: rgba(52,211,153,0.5);
            padding: 0 12px; margin-bottom: 4px;
        }

        /* Nav link */
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 8.5px 12px; border-radius: 10px;
            font-size: 13px; font-weight: 500;
            color: rgba(209,250,229,0.75);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative; cursor: pointer;
            text-decoration: none;
        }
        .nav-link:hover:not(.active) {
            background: rgba(255,255,255,0.08);
            color: #ffffff;
            transform: translateX(3px);
        }
        .nav-link.active {
            background: rgba(255,255,255,0.95);
            color: #064e3b;
            font-weight: 700;
            box-shadow: 0 3px 14px rgba(0,0,0,0.12);
        }
        .nav-link.active svg { color: #059669; }
        .nav-link.active::before {
            content: '';
            position: absolute; left: 0; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 60%;
            background: linear-gradient(to bottom, #34d399, #059669);
            border-radius: 0 3px 3px 0;
        }

        /* Sidebar brand */
        .sidebar-brand {
            padding: 16px 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
            position: relative; z-index: 1;
        }

        /* Brand logo glow */
        .brand-logo {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #34d399, #10b981, #059669);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 16px rgba(16,185,129,0.3);
            transition: all 0.2s ease;
        }

        /* ── MAIN CONTENT ──────────────────────────────── */
        .main-content {
            margin-left: 250px;
            width: calc(100vw - 250px);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            transition: margin-left 0.28s cubic-bezier(0.4, 0, 0.2, 1), width 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── NAVBAR / SIDEBAR TOGGLE RULES ─────────────── */
        #btnShowNavbar {
            display: none;
        }
        body.navbar-hidden #btnShowNavbar {
            display: inline-flex !important;
        }

        body.navbar-hidden .sidebar {
            transform: translateX(-100%) !important;
            box-shadow: none !important;
        }
        body.navbar-hidden .main-content {
            margin-left: 0 !important;
            width: 100vw !important;
        }

        /* Mobile backdrop */
        .sidebar-backdrop {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0; pointer-events: none;
            transition: opacity 0.25s ease;
        }
        body.sidebar-open-mobile .sidebar-backdrop {
            opacity: 1; pointer-events: auto;
        }

        @media (max-width: 1023px) {
            #btnShowNavbar {
                display: none !important;
            }
            .sidebar {
                transform: translateX(-100%) !important;
            }
            .main-content {
                margin-left: 0 !important;
                width: 100vw !important;
            }
            body.sidebar-open-mobile .sidebar {
                transform: translateX(0) !important;
            }
        }

        /* Topbar */
        .topbar {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 54px;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* Page body */
        .page-body {
            flex: 1;
            padding: 20px 24px 24px;
            display: flex;
            flex-direction: column;
        }

        /* Smooth mobile table horizontal scrolling & scrollbar */
        .overflow-x-auto,
        .table-responsive {
            width: 100%;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: thin;
        }
        .overflow-x-auto::-webkit-scrollbar,
        .table-responsive::-webkit-scrollbar {
            height: 4px;
        }
        .overflow-x-auto::-webkit-scrollbar-track,
        .table-responsive::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .overflow-x-auto::-webkit-scrollbar-thumb,
        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }



        /* Flash messages */
        .flash-success {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border: 1px solid #a7f3d0;
            color: #065f46;
        }
        .flash-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Status indicator */
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16,185,129,0.6);
            animation: statusPulse 2s ease-out infinite;
        }
        @keyframes statusPulse {
            0% { box-shadow: 0 0 0 0 rgba(16,185,129,0.6); }
            70% { box-shadow: 0 0 0 8px rgba(16,185,129,0); }
            100% { box-shadow: 0 0 0 0 rgba(16,185,129,0); }
        }

        /* Clock display */
        .clock-badge {
            background: rgba(241,245,249,0.9);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 6px 12px;
            font-size: 12px; font-weight: 600; color: #475569;
        }

        /* User avatar */
        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #34d399, #10b981);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-family: 'Outfit', sans-serif; font-weight: 800;
            font-size: 14px; color: #064e3b;
            box-shadow: 0 2px 10px rgba(16,185,129,0.25);
        }

        /* Page title bar */
        .page-title-text {
            font-family: 'Outfit', sans-serif;
            font-size: 18px; font-weight: 800;
            color: #0f172a; letter-spacing: -0.02em;
        }
        .page-subtitle-text {
            font-size: 12px; color: #64748b; margin-top: 1px; font-weight: 500;
        }

        /* Breadcrumb dot */
        .page-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
        }

        /* Footer */
        .page-footer {
            padding: 16px 32px;
            border-top: 1px solid #e2e8f0;
            background: rgba(255,255,255,0.6);
            font-size: 11px; color: #94a3b8;
            display: flex; align-items: center; justify-content: space-between;
        }

        /* Topbar icon btn */
        .topbar-icon-btn {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: #64748b;
            transition: all 0.2s ease;
            cursor: pointer;
            text-decoration: none;
        }
        .topbar-icon-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        /* Notification bell */
        .notif-btn { position: relative; }
        .notif-dot {
            position: absolute; top: 6px; right: 6px;
            width: 7px; height: 7px; border-radius: 50%;
            background: #ef4444;
            border: 2px solid white;
        }

        /* User card dropdown trigger */
        .user-card-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 5px 8px 5px 5px;
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .user-card-btn:hover { background: #f1f5f9; }

        /* Logout btn sidebar */
        .logout-btn {
            width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
            padding: 9px 12px; border-radius: 10px;
            font-size: 12px; font-weight: 600;
            color: rgba(167,243,208,0.8);
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            transition: all 0.2s ease; cursor: pointer;
        }
        .logout-btn:hover {
            background: rgba(239,68,68,0.15);
            border-color: rgba(239,68,68,0.3);
            color: #fca5a5;
        }

        /* User card in sidebar */
        .sidebar-user-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 14px; padding: 10px;
            margin-bottom: 8px;
            display: flex; align-items: center; gap: 10px;
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="flex min-h-screen">

    <!-- ── SIDEBAR ──────────────────────────────────────────────── -->
    <aside class="sidebar">

        <!-- Brand Header -->
        <div class="sidebar-brand flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white p-1.5 flex items-center justify-center shadow-sm flex-shrink-0">
                    <img src="{{ url('/logo.png') }}" alt="SIRKULO" class="w-full h-full object-contain">
                </div>
                <div>
                    <span class="font-display font-black text-lg tracking-tight text-white block leading-none">SIRKULO</span>
                    <span class="text-[9.5px] font-semibold uppercase tracking-[0.12em] text-emerald-300/80 block mt-0.5">Bank Sampah Desa</span>
                </div>
            </a>
            {{-- Tombol Hide / Collapse Navbar pada Sidebar --}}
            <button type="button" id="btnSidebarToggle" onclick="toggleSidebarNavbar()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/10 hover:bg-white/20 active:scale-95 text-emerald-200 hover:text-white transition-all text-xs focus:outline-none cursor-pointer flex-shrink-0"
                    title="Tutup / Sembunyikan Sidebar">
                <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav px-3 py-2.5 space-y-3.5 flex-1">

            <!-- Group: Utama -->
            <div>
                <p class="nav-group-label">Utama</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.dashboard') }}"
                       class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span>Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- Group: Operasional -->
            <div>
                <p class="nav-group-label">Operasional Desa</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.nasabah.index') }}"
                       class="nav-link {{ request()->routeIs('admin.nasabah*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Nasabah Warga</span>
                    </a>

                    <a href="{{ route('admin.mitra.index') }}"
                       class="nav-link {{ request()->routeIs('admin.mitra*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Pos Bank Sampah</span>
                    </a>

                    {{-- Petugas Lapangan di Operasional Desa --}}
                    <a href="{{ route('admin.petugas.index') }}"
                       class="nav-link {{ request()->routeIs('admin.petugas*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span>Petugas Lapangan</span>
                    </a>

                    <a href="{{ route('admin.harga-sampah.index') }}"
                       class="nav-link {{ request()->routeIs('admin.harga-sampah*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                        </svg>
                        <span>Katalog & Harga Sampah</span>
                    </a>

                    <a href="{{ route('admin.edukasi.index') }}"
                       class="nav-link {{ request()->routeIs('admin.edukasi*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Edukasi Daur Ulang</span>
                    </a>
                </div>
            </div>

            <!-- Group: Laporan -->
            <div>
                <p class="nav-group-label">Laporan & Evaluasi</p>
                <div class="space-y-0.5">
                    <a href="{{ route('admin.laporan.index') }}"
                       class="nav-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Rekap Kas & Transaksi</span>
                    </a>

                    <a href="{{ route('admin.feedback.index') }}"
                       class="nav-link {{ request()->routeIs('admin.feedback*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                        </svg>
                        <span class="flex-1">Aspirasi & Kritik Saran</span>
                        @php
                            try { $pendingFeedback = \App\Models\Feedback::whereNull('jawaban')->count(); } catch(\Throwable $e) { $pendingFeedback = 0; }
                        @endphp
                        @if($pendingFeedback > 0)
                        <span class="text-[10px] font-black bg-rose-500 text-white px-1.5 py-0.5 rounded-full leading-none">{{ $pendingFeedback }}</span>
                        @endif
                    </a>


                    {{-- Panduan Pelaporan DLH - Portal Satu Jari --}}
                    <a href="{{ route('admin.portal-satu-jari') }}"
                       class="nav-link {{ request()->routeIs('admin.portal-satu-jari*') ? 'active' : '' }}"
                       title="Panduan & Akses Portal Satu Jari DLH">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span class="flex-1">Portal Satu Jari DLH</span>
                        <span class="text-[10px] font-bold bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded border border-emerald-500/30 leading-none">DLH</span>
                    </a>
                </div>
            </div>

            <!-- Group: Pengaturan -->
            <div>
                <p class="nav-group-label">Pengaturan</p>
                <div class="space-y-0.5">
                    @if(session('admin_peran') === 'SUPER_ADMIN')
                    {{-- Administrator Desa (Khusus Super Admin) --}}
                    <a href="{{ route('admin.administrator.index') }}"
                       class="nav-link {{ request()->routeIs('admin.administrator*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="flex-1">Administrator Desa</span>
                    </a>

                    {{-- Log Aktivitas & Audit (Khusus Super Admin) --}}
                    <a href="{{ route('admin.aktivitas.index') }}"
                       class="nav-link {{ request()->routeIs('admin.aktivitas*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Log Aktivitas & Audit</span>
                    </a>

                    {{-- Sistem & Kesehatan DB (Khusus Super Admin) --}}
                    <a href="{{ route('admin.sistem.index') }}"
                       class="nav-link {{ request()->routeIs('admin.sistem*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Sistem & Kesehatan DB</span>
                    </a>
                    @endif

                    @if(session('admin_peran') !== 'SUPER_ADMIN')
                    {{-- Ganti Kata Sandi (Khusus Admin Biasa / Operasional) --}}
                    <a href="{{ route('admin.pengaturan.sandi') }}"
                       class="nav-link {{ request()->routeIs('admin.pengaturan.sandi*') ? 'active' : '' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                        <span>Ganti Kata Sandi</span>
                    </a>
                    @endif
                </div>
            </div>

        </nav>

        <!-- Sidebar Footer: User Card & Logout -->
        <div class="px-3 py-2.5 border-t border-white/[0.08] relative z-10">
            <div class="flex items-center gap-2 mb-2.5">
                @if(session('admin_peran') === 'SUPER_ADMIN')
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 font-display font-bold text-xs bg-amber-500/20 border border-amber-400/40 text-amber-300">
                        SA
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate leading-tight">{{ session('admin_nama', 'Super Administrator') }}</p>
                        <p class="text-[9.5px] font-bold text-amber-400 uppercase tracking-wider">Super Admin</p>
                    </div>
                @else
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 font-display font-bold text-xs bg-emerald-500/20 border border-emerald-400/40 text-emerald-300">
                        {{ strtoupper(substr(session('admin_nama', 'AD'), 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-white truncate leading-tight">{{ session('admin_nama', 'Administrator') }}</p>
                        <p class="text-[9.5px] text-emerald-300/80 font-medium">Admin Operasional</p>
                    </div>
                @endif
            </div>
            <form method="POST" action="{{ route('admin.logout', [], false) }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar dari Sistem
                </button>
            </form>
        </div>

    </aside>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop" onclick="closeMobileSidebar()" id="sidebarBackdrop"></div>

    <!-- ── MAIN CONTENT ──────────────────────────────────────────── -->
    <main class="main-content">

        <!-- Topbar -->
        <header class="topbar">
            <div class="flex items-center gap-2.5">

                {{-- Tombol Show Navbar (muncul saat sidebar disembunyikan di desktop) --}}
                <button type="button" id="btnShowNavbar"
                        onclick="toggleSidebarNavbar()"
                        class="w-8 h-8 items-center justify-center rounded-lg bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-700 border border-slate-200 hover:border-emerald-200 transition-all cursor-pointer"
                        title="Tampilkan Sidebar (Ctrl + B)">
                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Tombol Buka Sidebar (mobile) --}}
                <button type="button" id="btnMobileMenu"
                        onclick="openMobileSidebar()"
                        class="flex lg:hidden w-8 h-8 items-center justify-center rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 transition-all"
                        title="Menu">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <div class="page-dot"></div>
                <div>
                    <h1 class="page-title-text">@yield('page-title', 'Dashboard')</h1>
                    <p class="page-subtitle-text hidden md:block">@yield('page-subtitle', 'Panel Kendali Operasional Desa SIRKULO')</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Live Clock -->
                <div class="clock-badge hidden lg:flex items-center gap-1.5 text-xs text-slate-500 py-1 px-2.5 rounded-lg bg-slate-100/80 border border-slate-200/60 font-medium">
                    <span id="liveClock">{{ now()->translatedFormat('d M Y') }}</span>
                    <span class="text-slate-300">•</span>
                    <span id="liveTime" class="font-mono text-slate-700 font-semibold">{{ now()->format('H:i:s') }}</span>
                    <span class="text-[10px] text-slate-400">WIB</span>
                </div>

                <!-- DB Status -->
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/70" title="Koneksi Database Terhubung">
                    <span class="status-dot"></span>
                    <span>Online</span>
                </div>

                <!-- Divider -->
                <div class="h-6 w-px bg-slate-200"></div>

                <!-- User Info -->
                <a href="{{ route('admin.pengaturan.sandi') }}" class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors group" title="Pengaturan Akun & Ganti Kata Sandi">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-700 flex items-center justify-center font-bold text-xs text-white shadow-xs group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr(session('admin_nama', 'A'), 0, 2)) }}
                    </div>
                    <div class="hidden sm:block text-left">
                        <p class="text-xs font-bold text-slate-800 leading-tight group-hover:text-emerald-700 transition-colors">{{ session('admin_nama', 'Administrator') }}</p>
                        <p class="text-[10px] text-slate-500 leading-none mt-0.5">{{ session('admin_peran') === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin Operasional' }}</p>
                    </div>
                </a>

            </div>
        </header>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mx-6 mt-3 px-4 py-2.5 rounded-xl flash-success flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-emerald-500 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-700 hover:text-emerald-900 text-base leading-none font-bold ml-2">×</button>
            </div>
        @endif

        @if(session('error'))
            <div class="mx-6 mt-3 px-4 py-2.5 rounded-xl flash-error flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-6 h-6 rounded-lg bg-rose-500 text-white flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-700 hover:text-rose-900 text-base leading-none font-bold ml-2">×</button>
            </div>
        @endif

        <!-- Page Content -->
        <div class="page-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <footer class="page-footer">
            <div class="flex items-center gap-2">
                <span style="font-weight:700;color:#64748b">SIRKULO</span>
                <span>• Platform Bank Sampah & Ekonomi Sirkular Desa</span>
            </div>
            <div class="flex items-center gap-3">
                <span style="padding:2px 8px;border-radius:6px;background:#f1f5f9;color:#64748b;font-family:monospace;font-size:10px">v1.0.0 Resmi</span>
                <span>© {{ date('Y') }} Kantor Desa & BUMDes</span>
            </div>
        </footer>

    </main>

</div>

<!-- Live Clock Script -->
<script>
    function updateLiveClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const s = String(now.getSeconds()).padStart(2, '0');
        const el = document.getElementById('liveTime');
        if (el) el.textContent = `${h}:${m}:${s}`;
    }
    setInterval(updateLiveClock, 1000);
    updateLiveClock();
</script>

<!-- ── Sidebar Toggle Script ──────────────────────────── -->
<script>
    const SIDEBAR_KEY = 'sirkulo_navbar_hidden';

    // Restore state from localStorage (only on desktop >= 1024px)
    (function initSidebar() {
        if (window.innerWidth >= 1024) {
            const hidden = localStorage.getItem(SIDEBAR_KEY) === '1';
            if (hidden) {
                document.body.classList.add('navbar-hidden');
            }
        }
    })();

    // Toggle / close sidebar
    window.toggleSidebarNavbar = function() {
        // Jika sedang di mobile drawer atau layar mobile (< 1024px), tutup mobile drawer
        if (document.body.classList.contains('sidebar-open-mobile') || window.innerWidth < 1024) {
            closeMobileSidebar();
            return;
        }

        // Desktop: toggle sembunyikan/tampilkan navbar
        const isHidden = document.body.classList.toggle('navbar-hidden');
        localStorage.setItem(SIDEBAR_KEY, isHidden ? '1' : '0');
    };

    // Mobile sidebar open/close
    window.openMobileSidebar = function() {
        document.body.classList.remove('navbar-hidden');
        document.body.classList.add('sidebar-open-mobile');
    };

    window.closeMobileSidebar = function() {
        document.body.classList.remove('sidebar-open-mobile');
    };

    // Auto-close mobile drawer on window resize to desktop
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) {
            closeMobileSidebar();
        }
    });

    // Keyboard shortcut: Ctrl + B atau Escape
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
            e.preventDefault();
            toggleSidebarNavbar();
        }
        if (e.key === 'Escape') {
            if (document.body.classList.contains('sidebar-open-mobile')) {
                closeMobileSidebar();
            }
            const notifDropdown = document.getElementById('adminNotifDropdown');
            if (notifDropdown && !notifDropdown.classList.contains('hidden')) {
                notifDropdown.classList.add('hidden');
            }
        }
    });


</script>

@stack('scripts')

</body>
</html>
