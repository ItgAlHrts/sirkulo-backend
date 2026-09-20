<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Keaslian Dokumen Laporan — SIRKULO Bank Sampah Desa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen flex flex-col justify-between antialiased selection:bg-emerald-100 selection:text-emerald-900">

    {{-- ── HEADER NAVIGASI PUBLIK ── --}}
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-700 text-white flex items-center justify-center font-black text-xl shadow-xs">
                    S
                </div>
                <div>
                    <h1 class="font-extrabold text-base tracking-tight text-slate-900 leading-none">SIRKULO</h1>
                    <p class="text-[11px] text-slate-500 font-medium mt-0.5">Portal Verifikasi Dokumen &amp; Arsip Digital Desa</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                    <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse"></span>
                    Sistem Online
                </span>
            </div>
        </div>
    </header>

    {{-- ── KONTEN UTAMA ── --}}
    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8 sm:py-12 flex-1 w-full">

        @if($isValid)
        {{-- ── KARTU 1: STATUS VERIFIKASI SAH & TERKUNCI ── --}}
        <div class="bg-white rounded-3xl shadow-md border border-emerald-200/80 p-6 sm:p-8 text-center relative overflow-hidden">
            <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-50 rounded-full blur-2xl pointer-events-none"></div>

            <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center mb-4 ring-8 ring-emerald-50 shadow-inner">
                <svg class="w-10 h-10 sm:w-12 sm:h-12" fill="none" stroke="currentColor" stroke-width="2.3" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>

            <div class="inline-flex items-center gap-1.5 bg-emerald-700 text-white text-[11px] uppercase tracking-widest font-extrabold px-3.5 py-1 rounded-full mb-3 shadow-xs">
                ✓ Dokumen Sah &amp; Terverifikasi
            </div>

            <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                Laporan Resmi Terdaftar pada Sistem SIRKULO
            </h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-xl mx-auto mt-2 leading-relaxed">
                Dokumen fisik atau arsip PDF yang Anda pindai terbukti <strong>asli, sah, dan bebas dari segala bentuk manipulasi data</strong>. Seluruh angka transaksi di bawah ini terikat langsung dengan basis data resmi desa.
            </p>

            {{-- Badge Kode Keamanan --}}
            <div class="mt-5 inline-flex flex-col sm:flex-row items-center gap-2 sm:gap-4 bg-slate-50 border border-slate-200 rounded-2xl px-5 py-3 text-left">
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Nomor Dokumen Resmi</div>
                    <div class="text-sm font-bold font-mono text-emerald-800">{{ $nomorLaporan }}</div>
                </div>
                <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Kode Integritas Keamanan</div>
                    <div class="text-sm font-bold font-mono text-slate-900">{{ $expectedKode }}</div>
                </div>
                @if($idCetakanUnik)
                <div class="hidden sm:block w-px h-8 bg-slate-200"></div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">ID Lembar Cetakan Unik</div>
                    <div class="text-sm font-bold font-mono text-emerald-700">{{ $idCetakanUnik }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- ── KARTU 2: RINCIAN RESMI DATABASE DESA (ANTI-MANIPULASI) ── --}}
        <div class="mt-6 bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800 uppercase tracking-wider">
                        Data Otentik Rekam Pangkalan Data
                    </h3>
                </div>
                <span class="text-[11px] font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                    Terkunci Realtime
                </span>
            </div>

            <div class="p-6 space-y-4 text-xs sm:text-sm">
                {{-- Info Wilayah, Pos & Penerbitan --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pb-4 border-b border-slate-100">
                    <div>
                        <div class="text-slate-400 font-semibold text-[11px]">Periode Laporan</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $namaBulan }} {{ $tahun }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-semibold text-[11px]">Waktu Cetak Fisik</div>
                        <div class="font-bold text-emerald-800 mt-0.5 flex items-center gap-1.5 flex-wrap">
                            <span>{{ $waktuCetakLengkap }}</span>
                            <span class="text-[9px] font-black bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded border border-emerald-300">TERKUNCI SERVER</span>
                        </div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-semibold text-[11px]">Unit Pos / Instansi</div>
                        <div class="font-bold text-slate-800 mt-0.5">{{ $selectedPos ? $selectedPos->nama : 'Pusat Bank Sampah Desa SIRKULO (Seluruh Pos)' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-400 font-semibold text-[11px]">Alamat Pos</div>
                        <div class="font-medium text-slate-700 mt-0.5">{{ $selectedPos ? ($selectedPos->alamat ?: 'Kantor Bank Sampah Dusun') : 'Gedung BUMDes Kantor Balai Desa' }}</div>
                    </div>
                    <div class="md:col-span-2">
                        <div class="text-slate-400 font-semibold text-[11px]">Pengelola Terdaftar di Sistem</div>
                        <div class="font-bold text-emerald-800 mt-0.5">{{ $selectedPos ? $selectedPos->pengelola : 'Administrator Desa & BUMDes' }}</div>
                    </div>
                </div>

                {{-- Angka Ringkasan Sampah & Kas Keuangan --}}
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-3">
                        Statistik Sampah &amp; Kas Keuangan Sah
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="bg-emerald-50 border border-emerald-200/80 rounded-2xl p-3.5">
                            <div class="text-[10.5px] font-bold text-emerald-800 uppercase">Total Sampah</div>
                            <div class="font-black text-lg text-emerald-900 mt-1">{{ number_format($totalBeratKg, 1, ',', '.') }} kg</div>
                            <div class="text-[10px] text-emerald-700 mt-0.5 font-semibold">{{ $totalTonase }} Ton</div>
                        </div>

                        <div class="bg-teal-50 border border-teal-200/80 rounded-2xl p-3.5">
                            <div class="text-[10.5px] font-bold text-teal-800 uppercase">Total Setoran</div>
                            <div class="font-black text-lg text-teal-900 mt-1">{{ $totalSetoran }} Trx</div>
                            <div class="text-[10px] text-teal-700 mt-0.5">Penimbangan warga</div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5">
                            <div class="text-[10.5px] font-bold text-slate-600 uppercase">Total Uang Sampah</div>
                            <div class="font-black text-base text-slate-900 mt-1">Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Kas tabungan masuk</div>
                        </div>

                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3.5">
                            <div class="text-[10.5px] font-bold text-slate-600 uppercase">Saldo Kas Bersih</div>
                            <div class="font-black text-base {{ $saldoKasBersih >= 0 ? 'text-emerald-700' : 'text-rose-600' }} mt-1">
                                Rp {{ number_format($saldoKasBersih, 0, ',', '.') }}
                            </div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Setelah pencairan</div>
                        </div>
                    </div>
                </div>

                {{-- Kriptografi & Waktu Sistem --}}
                <div class="pt-4 border-t border-slate-100 bg-slate-50/50 -mx-6 -mb-6 p-6">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div class="text-xs text-slate-600 leading-relaxed">
                            <div class="font-bold text-slate-800 mb-0.5">Integritas Kriptografi Dokumen (SHA-256 Digest)</div>
                            <div class="font-mono text-[10.5px] text-slate-700 break-all bg-white p-2 rounded-lg border border-slate-200 mt-1">
                                {{ strtoupper($expectedHash) }}
                            </div>
                            <div class="mt-2 text-[11px] text-slate-500">
                                Waktu Server Verifikasi: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</strong>. Setiap perbedaan angka atau tanggal pada lembaran fisik terhadap data di atas membuktikan dokumen fisik tersebut tidak sah.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- Status Tidak Cocok / Peringatan Manipulasi --}}
        <div class="bg-white rounded-3xl shadow-md border border-rose-200 p-6 sm:p-8 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h2 class="text-xl font-black text-slate-900">Peringatan: Kode Keamanan Tidak Valid</h2>
            <p class="text-xs sm:text-sm text-slate-600 max-w-md mx-auto mt-2">
                Kode keamanan dokumen yang diperiksa tidak sesuai dengan rekaman resmi server. Harap pastikan dokumen dicetak langsung dari sistem SIRKULO resmi.
            </p>
        </div>
        @endif

        <div class="mt-8 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} Sistem Informasi SIRKULO &bull; Bank Sampah Digital Desa
        </div>
    </main>

</body>
</html>
