@extends('admin.layout')

@section('title', 'Rekap Kas & Transaksi')
@section('page-title', 'Rekap Kas & Transaksi')
@section('page-subtitle', 'Laporan bulanan setoran sampah dan perputaran keuangan bank sampah desa')

@push('styles')
<style>
    .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:14px; padding:18px 20px; transition:box-shadow .2s; }
    .stat-card:hover { box-shadow:0 4px 18px rgba(15,23,42,.07); }
    .stat-card.primary { background:linear-gradient(135deg,#064e3b,#065f46); border-color:transparent; color:#fff; }
    .tbl { width:100%; border-collapse:separate; border-spacing:0; font-size:13px; }
    .tbl thead th { padding:10px 16px; font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; background:#f8fafc; border-bottom:1px solid #f1f5f9; }
    .tbl tbody td { padding:12px 16px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
    .tbl tbody tr:hover td { background:#f8fafc; }
    .tbl tfoot td { padding:12px 16px; font-weight:700; background:#ecfdf5; border-top:2px solid #a7f3d0; }
    .badge-kg { background:#ecfdf5; color:#065f46; border-radius:8px; padding:2px 8px; font-size:11px; font-weight:700; }
    .badge-rp { background:#f0fdf4; color:#166534; border-radius:8px; padding:2px 8px; font-size:11px; font-weight:700; }
    .badge-persen { background:#f1f5f9; color:#334155; border-radius:8px; padding:2px 8px; font-size:11px; font-weight:600; }
    .section-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; overflow:hidden; margin-bottom:20px; }
    .section-card-header { padding:14px 20px; border-bottom:1px solid #f1f5f9; background:#f8fafc; }
    .section-card-header h4 { font-size:13px; font-weight:700; color:#0f172a; margin:0; }
    .section-card-header p  { font-size:11px; color:#94a3b8; margin:2px 0 0; }
    .filter-select { height:38px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:0 12px; font-size:13px; font-weight:600; color:#334155; transition:border-color .15s; cursor:pointer; }
    .filter-select:focus { outline:none; border-color:#10b981; background:#fff; }
    .btn-cetak { display:inline-flex; align-items:center; gap:7px; background:#065f46; color:#fff; border:none; border-radius:10px; padding:0 18px; height:38px; font-size:13px; font-weight:700; cursor:pointer; transition:background .15s; }
    .btn-cetak:hover { background:#047857; }
    .input-doc { width:100%; background:#fff; border:1px solid #e2e8f0; border-radius:9px; padding:7px 12px; font-size:12px; color:#1e293b; transition:border-color .15s; }
    .input-doc:focus { outline:none; border-color:#10b981; }
    .input-doc.locked { background:#fef2f2; border-color:#fecaca; color:#991b1b; cursor:not-allowed; }
    .input-label { font-size:11px; font-weight:700; color:#475569; margin-bottom:4px; display:block; }
    .form-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; }
</style>
@endpush

@section('content')

{{-- ══════════════════════════════════ TAMPILAN LAYAR ══════════════════════════════════ --}}
<div class="print:hidden space-y-5">

    {{-- ── FILTER PERIODE & POS + TOMBOL AKSI ───────────────────────────── --}}
    <div class="bg-white border border-slate-200 rounded-2xl p-4">
        <form method="GET" action="{{ route('admin.laporan.index') }}"
              class="flex flex-wrap items-end gap-3">
            <div>
                <label class="input-label">Bulan</label>
                <select name="bulan" class="filter-select" onchange="this.form.submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="input-label">Tahun</label>
                <select name="tahun" class="filter-select" onchange="this.form.submit()">
                    @foreach($daftarTahun as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="input-label">Pos Bank Sampah</label>
                <select name="pos_id" class="filter-select" onchange="this.form.submit()" style="max-width:240px">
                    <option value="">Semua Pos</option>
                    @foreach($posList as $pos)
                        <option value="{{ $pos->id }}" {{ $posId == $pos->id ? 'selected' : '' }}>
                            {{ $pos->nama }} ({{ $pos->kode_pos ?? 'POS' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="ml-auto">
                <button type="button" onclick="cetakDokumenResmi()" class="btn-cetak">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak / Simpan PDF
                </button>
            </div>
        </form>
    </div>

    {{-- ── 4 KARTU STATISTIK ── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card primary">
            <p class="text-[11px] font-bold uppercase tracking-wider" style="color:#a7f3d0">Total Berat Sampah</p>
            <p class="text-2xl font-black mt-1">{{ number_format($totalBeratKg, 1, ',', '.') }} <span class="text-base font-normal">kg</span></p>
            <p class="text-xs mt-1" style="color:#a7f3d0">≈ {{ $totalTonase }} Ton terkelola</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Uang dari Sampah</p>
            <p class="text-xl font-black text-emerald-700 mt-1">Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $totalSetoran }} kali setoran</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Pencairan / Penarikan</p>
            <p class="text-xl font-black text-amber-600 mt-1">Rp {{ number_format($totalPenarikanRp, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $totalPenarikanTrx }} kali penarikan</p>
        </div>
        <div class="stat-card">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Sisa Kas Tabungan</p>
            <p class="text-xl font-black mt-1 {{ $saldoKasBersih >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">Rp {{ number_format($saldoKasBersih, 0, ',', '.') }}</p>
            <p class="text-xs text-slate-500 mt-1">Surplus tabungan warga</p>
        </div>
    </div>

    {{-- ── TABEL 1: REKAP PER JENIS SAMPAH ── --}}
    <div class="section-card">
        <div class="section-card-header flex items-center justify-between">
            <div>
                <h4>Rekap Setoran per Jenis Sampah</h4>
                <p>Periode: {{ $namaBulan }} {{ $tahun }} • {{ $posInfoNama }}</p>
            </div>
            <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-3 py-1 rounded-lg">{{ count($rincianKategori) }} Jenis</span>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="text-center" style="width:44px">No</th>
                        <th>Jenis Sampah</th>
                        <th class="text-center">Setoran</th>
                        <th class="text-right">Berat (kg)</th>
                        <th class="text-right">Nilai Beli (Rp)</th>
                        <th class="text-right">Margin</th>
                        <th class="text-right">Porsi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @forelse($rincianKategori as $rk)
                    @php $persen = $totalBeratKg > 0 ? round(($rk['total_kg'] / $totalBeratKg) * 100, 1) : 0; @endphp
                    <tr>
                        <td class="text-center text-xs text-slate-400 font-mono">{{ $no++ }}</td>
                        <td class="font-semibold text-slate-800">{{ $rk['nama'] }}</td>
                        <td class="text-center text-xs text-slate-500">{{ $rk['transaksi'] }}×</td>
                        <td class="text-right"><span class="badge-kg">{{ number_format($rk['total_kg'], 1, ',', '.') }}</span></td>
                        <td class="text-right font-mono font-semibold text-slate-700">Rp {{ number_format($rk['total_rp'], 0, ',', '.') }}</td>
                        <td class="text-right">
                            @if($rk['margin_rp'] > 0)
                                <span class="badge-rp">+{{ $rk['margin_persen'] }}%</span>
                            @else
                                <span class="badge-persen">—</span>
                            @endif
                        </td>
                        <td class="text-right"><span class="badge-persen">{{ $persen }}%</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 py-10 text-sm">
                            Belum ada setoran sampah pada {{ $namaBulan }} {{ $tahun }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($rincianKategori) > 0)
                <tfoot class="bg-emerald-50/50 font-bold border-t-2 border-emerald-200 text-slate-900">
                    <tr>
                        <td colspan="2" class="text-center uppercase tracking-wider text-xs font-black">
                            {{ $selectedPos ? 'TOTAL POS ' . strtoupper($selectedPos->nama) : 'TOTAL SELURUH POS DESA' }}
                        </td>
                        <td class="text-center text-emerald-900 font-bold">{{ $totalSetoran }} kali</td>
                        <td class="text-right font-mono text-emerald-900 font-black">{{ number_format($totalBeratKg, 1, ',', '.') }} kg</td>
                        <td class="text-right font-mono text-emerald-900 font-black">Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</td>
                        <td class="text-right font-mono text-emerald-900 font-black">+{{ $persentaseTotalMargin }}%</td>
                        <td class="text-right text-emerald-900 font-mono font-black">100.0%</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if(!$selectedPos)
    {{-- ── TABEL 2: KINERJA TIAP POS ── --}}
    <div class="section-card">
        <div class="section-card-header flex items-center justify-between">
            <div>
                <h4>Kinerja Tiap Pos Bank Sampah</h4>
                <p>Perbandingan tonase dan perputaran uang per pos</p>
            </div>
            <span class="text-xs font-bold bg-emerald-100 text-emerald-800 px-3 py-1 rounded-lg">{{ count($rincianPerPos) }} Pos</span>
        </div>
        <div class="overflow-x-auto">
            <table class="tbl">
                <thead>
                    <tr>
                        <th class="text-center" style="width:44px">No</th>
                        <th>Nama Pos</th>
                        <th>Pengelola</th>
                        <th class="text-center">Setoran</th>
                        <th class="text-right">Berat (kg)</th>
                        <th class="text-right">Nilai Beli (Rp)</th>
                        <th class="text-right">% Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @php $noP = 1; @endphp
                    @forelse($rincianPerPos as $rp)
                    <tr>
                        <td class="text-center text-xs text-slate-400 font-mono">{{ $noP++ }}</td>
                        <td>
                            <div class="font-semibold text-slate-800">{{ $rp['nama_pos'] }}</div>
                            <div class="text-[11px] text-slate-400">{{ $rp['kode_pos'] }} • {{ $rp['alamat'] }}</div>
                        </td>
                        <td class="text-xs text-slate-600">{{ $rp['pengelola'] }}</td>
                        <td class="text-center text-xs text-slate-500">{{ $rp['transaksi'] }}×</td>
                        <td class="text-right"><span class="badge-kg">{{ number_format($rp['total_kg'], 1, ',', '.') }}</span></td>
                        <td class="text-right font-mono font-semibold text-slate-700">Rp {{ number_format($rp['total_rp'], 0, ',', '.') }}</td>
                        <td class="text-right"><span class="badge-rp">+{{ $rp['margin_persen'] }}%</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-slate-400 py-10 text-sm">
                            Belum ada pos dengan setoran pada periode {{ $namaBulan }} {{ $tahun }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="font-black text-emerald-900 text-sm">TOTAL SELURUH POS</td>
                        <td class="text-center text-emerald-900 font-bold">{{ $totalSetoran }}×</td>
                        <td class="text-right font-black text-emerald-900 font-mono">{{ number_format($totalBeratKg, 1, ',', '.') }} kg</td>
                        <td class="text-right font-black text-emerald-900 font-mono">Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</td>
                        <td class="text-right font-black text-emerald-900">+{{ $persentaseTotalMargin }}%</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @else
    {{-- Info pos terpilih --}}
    <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Laporan Spesifik Pos</p>
            <p class="font-bold text-slate-900 mt-0.5">{{ $selectedPos->nama }} <span class="text-xs font-mono text-slate-500 ml-1">({{ $posInfoKode }})</span></p>
            <p class="text-xs text-slate-500 mt-0.5">{{ $posInfoPengelola }} • {{ $posInfoAlamat }}</p>
        </div>
        <div class="flex items-center gap-6">
            <div class="text-right">
                <p class="text-[10px] uppercase font-bold text-slate-400">Margin Pos Ini</p>
                <p class="text-xl font-black text-emerald-700">+ Rp {{ number_format($totalMarginKeuntungan, 0, ',', '.') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] uppercase font-bold text-slate-400">% Margin</p>
                <p class="text-xl font-black text-emerald-800">+{{ $persentaseTotalMargin }}%</p>
            </div>
        </div>
    </div>
    @endif

    {{-- ── PANEL CETAK DOKUMEN (ACCORDION, TERSEMBUNYI DEFAULT) ── --}}
    <div class="section-card">
        <div class="section-card-header flex items-center justify-between cursor-pointer select-none" onclick="togglePanelCetak()">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div>
                    <h4>Pratinjau &amp; Cetak Dokumen Resmi</h4>
                    <p>Klik untuk mengisi data penandatangan &amp; melihat pratinjau PDF</p>
                </div>
            </div>
            <svg id="chevron-cetak" class="w-5 h-5 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>

        <div id="panel-cetak-body" class="hidden">
            {{-- Form isian singkat --}}
            <div class="p-5 border-b border-slate-100">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h5 class="text-sm font-bold text-slate-700">Data Dokumen</h5>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 cursor-pointer">
                            <input type="checkbox" id="toggle-tanda-tangan" onchange="toggleSignatures(this.checked)"
                                   class="rounded text-emerald-600 border-slate-300 focus:ring-emerald-500">
                            Sertakan Lembar Pengesahan
                        </label>
                        <button type="button" onclick="cetakDokumenResmi()" class="btn-cetak" style="height:34px;padding:0 14px;font-size:12px">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            Cetak PDF
                        </button>
                    </div>
                </div>

                {{-- Grid input data dokumen --}}
                <div class="form-grid mb-3">
                    <div>
                        <span class="input-label">Nama Pos / Instansi <span class="text-rose-400 text-[9px]">Terkunci</span></span>
                        <input type="text" id="kolom-pos-nama" value="{{ $posInfoNama }}" readonly class="input-doc locked">
                    </div>
                    <div>
                        <span class="input-label">Pengelola / Penanggung Jawab</span>
                        <input type="text" id="kolom-pos-pengelola" value="{{ $posInfoPengelola }}"
                               oninput="updateDocField('posPengelola', this.value)"
                               placeholder="Nama pengelola pos..."
                               class="input-doc">
                    </div>
                    <div>
                        <span class="input-label">Nomor Surat</span>
                        <input type="text" id="kolom-doc-nomor" value="{{ $nomorLaporan }}"
                               oninput="updateDocField('docNomor', this.value)" class="input-doc">
                    </div>
                    <div>
                        <span class="input-label">Tempat Penetapan</span>
                        <input type="text" id="kolom-doc-tempat" value="Ditetapkan di Kantor Balai Desa"
                               oninput="updateDocField('docTempat', this.value)" class="input-doc">
                    </div>
                    <div>
                        <span class="input-label">Tanggal Cetak <span class="text-rose-400 text-[9px]">Otomatis</span></span>
                        <input type="text" id="kolom-doc-tanggal" value="{{ $tanggalCetakLengkap }}" readonly class="input-doc locked">
                    </div>
                </div>

                {{-- Pejabat penandatangan (tersembunyi, muncul jika checkbox dicentang) --}}
                <div id="blok-ttd-isian" class="hidden pt-3 border-t border-slate-100">
                    <p class="text-xs font-bold text-slate-500 mb-3">Pejabat Penandatangan</p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">1. Mengetahui</p>
                            <input type="text" id="kolom-ttd1-jabatan" value="Kepala Desa" oninput="updateDocField('ttd1Jabatan', this.value)" placeholder="Jabatan" class="input-doc">
                            <input type="text" id="kolom-ttd1-nama" value="" oninput="updateDocField('ttd1Nama', this.value)" placeholder="Nama Lengkap..." class="input-doc">
                            <input type="text" id="kolom-ttd1-nip" value="" oninput="updateDocField('ttd1Nip', this.value)" placeholder="NIP / Identitas" class="input-doc font-mono">
                        </div>
                        <div class="space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">2. Menyetujui</p>
                            <input type="text" id="kolom-ttd2-jabatan" value="Direktur BUMDes" oninput="updateDocField('ttd2Jabatan', this.value)" placeholder="Jabatan" class="input-doc">
                            <input type="text" id="kolom-ttd2-nama" value="" oninput="updateDocField('ttd2Nama', this.value)" placeholder="Nama Lengkap..." class="input-doc">
                            <input type="text" id="kolom-ttd2-nip" value="" oninput="updateDocField('ttd2Nip', this.value)" placeholder="NIK / NIP" class="input-doc font-mono">
                        </div>
                        <div class="space-y-2">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">3. Dibuat Oleh</p>
                            <input type="text" id="kolom-ttd3-jabatan" value="{{ $selectedPos ? 'Penanggung Jawab Pos' : 'Koordinator Bank Sampah' }}" oninput="updateDocField('ttd3Jabatan', this.value)" placeholder="Jabatan" class="input-doc">
                            <input type="text" id="kolom-ttd3-nama" value="{{ $posInfoPengelola }}" oninput="updateDocField('ttd3Nama', this.value)" placeholder="Nama Pembuat" class="input-doc">
                            <input type="text" id="kolom-ttd3-sub" value="{{ $selectedPos ? $posInfoNama : 'Pengelola Sistem SIRKULO' }}" oninput="updateDocField('ttd3Sub', this.value)" placeholder="Unit / Lembaga" class="input-doc">
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="input-label">Catatan Tambahan (Opsional)</span>
                        <textarea id="kolom-doc-catatan" rows="2" oninput="updateDocField('docCatatan', this.value)"
                                  placeholder="Catatan khusus bila ada..."
                                  class="input-doc" style="height:auto;padding-top:8px;padding-bottom:8px;"></textarea>
                    </div>
                </div>
            </div>

            {{-- Pratinjau kertas A4 --}}
            <div class="p-5 bg-slate-50">
                <p class="text-xs font-semibold text-slate-400 mb-3 text-center">— Pratinjau Dokumen (sesuai hasil cetak PDF) —</p>
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl bg-white shadow-md rounded-sm border border-slate-200 overflow-hidden">
                        @include('admin.laporan._dokumen_laporan')
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════ DOKUMEN CETAK / PDF ══════════════════════════════════════════ --}}
<div id="laporan-cetak-resmi" class="hidden print:block text-slate-900 bg-white">
    @include('admin.laporan._dokumen_laporan')
</div>

@push('scripts')
<script>
    // ── Accordion Panel Cetak ──────────────────────────────────────────────
    function togglePanelCetak() {
        const body = document.getElementById('panel-cetak-body');
        const chevron = document.getElementById('chevron-cetak');
        const isHidden = body.classList.contains('hidden');
        body.classList.toggle('hidden', !isHidden);
        if (chevron) chevron.style.transform = isHidden ? 'rotate(180deg)' : '';
    }

    // ── Toggle form TTD & blok di preview ─────────────────────────────────
    function toggleSignatures(show) {
        const isian = document.getElementById('blok-ttd-isian');
        if (isian) isian.classList.toggle('hidden', !show);
        document.querySelectorAll('.blok-pengesahan-tanda-tangan').forEach(el => {
            el.classList.toggle('hidden', !show);
        });
    }

    // ── Data Default dari Server ───────────────────────────────────────────
    const defaultDataLaporan = {
        posNama: @json($posInfoNama),
        posKode: @json($posInfoKode),
        posPengelola: @json($posInfoPengelola),
        posAlamat: @json($posInfoAlamat),
        posJam: @json($posInfoJam),
        docNomor: @json($nomorLaporan),
        docTempat: "Ditetapkan di Kantor Balai Desa",
        ttd1Jabatan: "Kepala Desa",
        ttd1Nama: "( ........................................ )",
        ttd1Nip: "NIP. .....................................",
        ttd2Jabatan: "Direktur BUMDes",
        ttd2Nama: "( ........................................ )",
        ttd2Nip: "NIK/NIP. .................................",
        ttd3Jabatan: @json($selectedPos ? 'Penanggung Jawab Pos' : 'Koordinator Bank Sampah'),
        ttd3Nama: @json($selectedPos ? ('( ' . $posInfoPengelola . ' )') : ('( ' . session('admin_nama', 'Administrator') . ' )')),
        ttd3Sub: @json($selectedPos ? $posInfoNama : 'Pengelola Sistem SIRKULO'),
        docCatatan: ""
    };

    const laporanStorageKey = 'sirkulo_laporan_custom_{{ $posId ?? "all" }}_{{ $bulan }}_{{ $tahun }}';

    const fieldMapping = {
        posPengelola: { inputId: 'kolom-pos-pengelola', docClass: 'live-pos-pengelola', format: v => v || defaultDataLaporan.posPengelola },
        docNomor:     { inputId: 'kolom-doc-nomor',     docClass: 'live-doc-nomor',     format: v => v || defaultDataLaporan.docNomor },
        docTempat:    { inputId: 'kolom-doc-tempat',    docClass: 'live-doc-tempat',    format: v => v || defaultDataLaporan.docTempat },
        ttd1Jabatan:  { inputId: 'kolom-ttd1-jabatan',  docClass: 'live-ttd1-jabatan',  format: v => v || defaultDataLaporan.ttd1Jabatan },
        ttd1Nama:     { inputId: 'kolom-ttd1-nama',     docClass: 'live-ttd1-nama',     format: v => v ? `( ${v} )` : defaultDataLaporan.ttd1Nama },
        ttd1Nip:      { inputId: 'kolom-ttd1-nip',      docClass: 'live-ttd1-nip',      format: v => v || defaultDataLaporan.ttd1Nip },
        ttd2Jabatan:  { inputId: 'kolom-ttd2-jabatan',  docClass: 'live-ttd2-jabatan',  format: v => v || defaultDataLaporan.ttd2Jabatan },
        ttd2Nama:     { inputId: 'kolom-ttd2-nama',     docClass: 'live-ttd2-nama',     format: v => v ? `( ${v} )` : defaultDataLaporan.ttd2Nama },
        ttd2Nip:      { inputId: 'kolom-ttd2-nip',      docClass: 'live-ttd2-nip',      format: v => v || defaultDataLaporan.ttd2Nip },
        ttd3Jabatan:  { inputId: 'kolom-ttd3-jabatan',  docClass: 'live-ttd3-jabatan',  format: v => v || defaultDataLaporan.ttd3Jabatan },
        ttd3Nama:     { inputId: 'kolom-ttd3-nama',     docClass: 'live-ttd3-nama',     format: v => v ? `( ${v} )` : defaultDataLaporan.ttd3Nama },
        ttd3Sub:      { inputId: 'kolom-ttd3-sub',      docClass: 'live-ttd3-sub',      format: v => v || defaultDataLaporan.ttd3Sub },
        docCatatan:   { inputId: 'kolom-doc-catatan',   docClass: 'live-doc-catatan',   format: v => v }
    };

    function updateDocField(key, value) {
        const config = fieldMapping[key];
        if (!config) return;
        document.querySelectorAll('.' + config.docClass).forEach(el => el.innerText = config.format(value));
        if (key === 'docCatatan') {
            document.querySelectorAll('.live-blok-catatan').forEach(b => b.classList.toggle('hidden', !(value && value.trim().length > 0)));
        }
        try {
            const data = {};
            Object.keys(fieldMapping).forEach(k => { const el = document.getElementById(fieldMapping[k].inputId); if (el) data[k] = el.value; });
            localStorage.setItem(laporanStorageKey, JSON.stringify(data));
        } catch (e) {}
    }

    function loadSavedCustomValues() {
        try {
            const raw = localStorage.getItem(laporanStorageKey);
            if (!raw) return;
            const data = JSON.parse(raw);
            Object.keys(data).forEach(k => {
                const config = fieldMapping[k];
                if (config && data[k]) {
                    const el = document.getElementById(config.inputId);
                    if (el && !el.readOnly) { el.value = data[k]; updateDocField(k, data[k]); }
                }
            });
        } catch (e) {}
    }

    // ── Print Metadata & Stempel Keamanan ─────────────────────────────────
    const printMetadata = {
        posId: @json($selectedPos ? $selectedPos->id : 'ALL_POS'),
        posParam: @json($posId ?: 'all'),
        tahun: @json($tahun),
        bulan: @json($bulan),
        totalBeratKg: @json($totalBeratKgFormatted),
        totalNilaiSampah: @json($totalNilaiSampah),
        totalSetoran: @json($totalSetoran),
        nomorLaporan: @json($nomorLaporan),
        baseUrl: @json(url('/verifikasi-laporan'))
    };
    const namaBulanIndoList = ["","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];

    function siapkanStempelCetakBaru() {
        const now = new Date();
        const pad = n => String(n).padStart(2, '0');
        const yyyy = now.getFullYear(), mm = pad(now.getMonth()+1), dd = pad(now.getDate());
        const hh = pad(now.getHours()), ii = pad(now.getMinutes()), ss = pad(now.getSeconds());
        const tglYmd = `${yyyy}${mm}${dd}`, jamHis = `${hh}${ii}${ss}`;
        const tokenChars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        let token = '';
        for (let i = 0; i < 4; i++) token += tokenChars.charAt(Math.floor(Math.random() * tokenChars.length));
        const printTimeStr = `${dd} ${namaBulanIndoList[now.getMonth()+1]||''} ${yyyy}, ${hh}:${ii}:${ss} WIB`;
        const idSesiCetak = `PRT-${tglYmd}-${jamHis}-${token}`;
        const payload = ['SIRKULO-OFFICIAL-VERIFIED-REPORT', printMetadata.posId, printMetadata.tahun, printMetadata.bulan,
            printMetadata.totalBeratKg, printMetadata.totalNilaiSampah, printMetadata.totalSetoran, tglYmd, jamHis, token].join('|');
        let hashHex = typeof window.sha256Sync === 'function' ? window.sha256Sync(payload) : ('a1b2c3d4e5f67890' + jamHis + token);
        const kodeKeamanan = 'SRK-' + (hashHex.substring(0,4)+'-'+hashHex.substring(4,8)+'-'+hashHex.substring(8,12)).toUpperCase();
        const verifikasiUrl = `${printMetadata.baseUrl}?no=${encodeURIComponent(printMetadata.nomorLaporan)}&kode=${kodeKeamanan}&pos=${printMetadata.posParam}&periode=${printMetadata.bulan}-${printMetadata.tahun}&tgl=${tglYmd}&jam=${jamHis}&token=${token}`;
        document.querySelectorAll('.live-doc-print-time').forEach(el => el.innerText = printTimeStr);
        document.querySelectorAll('.live-doc-print-kode').forEach(el => el.innerText = kodeKeamanan);
        document.querySelectorAll('.live-doc-print-serial').forEach(el => el.innerText = idSesiCetak);
        document.querySelectorAll('.live-doc-print-digest').forEach(el => el.innerText = hashHex.substring(0,20).toUpperCase()+'...');
        const inputTgl = document.getElementById('kolom-doc-tanggal');
        if (inputTgl) inputTgl.value = printTimeStr;
        if (typeof QRCode !== 'undefined') {
            document.querySelectorAll('.doc-qr-box').forEach(el => {
                el.innerHTML = '';
                new QRCode(el, { text: verifikasiUrl, width: 68, height: 68, colorDark: '#166534', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M });
            });
        }
        return { verifikasiUrl, idSesiCetak, kodeKeamanan };
    }

    function cetakDokumenResmi() {
        siapkanStempelCetakBaru();
        setTimeout(() => window.print(), 80);
    }

    document.addEventListener('DOMContentLoaded', () => { loadSavedCustomValues(); siapkanStempelCetakBaru(); });
    window.addEventListener('beforeprint', () => siapkanStempelCetakBaru());
</script>
@endpush

@endsection
