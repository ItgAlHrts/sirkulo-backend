{{-- ══════════════════════════════════════════════════════════════════════ --}}
{{-- TEMPLATE DOKUMEN LAPORAN RESMI SIRKULO (STANDAR PERSIS PDF RESMI)      --}}
{{-- ══════════════════════════════════════════════════════════════════════ --}}
<div class="doc-cetak-container bg-white text-slate-900 leading-normal" style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">

    <style>
        @media screen {
            .blok-validasi-cetak {
                display: none !important;
            }
        }
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm 8mm 10mm;
            }
            body {
                background: #ffffff !important;
                color: #0f172a !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
            }
            .doc-cetak-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .avoid-break {
                page-break-inside: avoid !important;
            }
            .blok-validasi-cetak {
                display: block !important;
            }
        }
    </style>

    {{-- ── 1. HEADER HIJAU UTAMA ─────────────────────────────────────────── --}}
    <div style="background-color: #166534; padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
        {{-- Sisi Kiri: Logo Kotak Putih & Teks Brand --}}
        <div style="display: flex; align-items: center; gap: 14px;">
            <div style="background-color: #ffffff; width: 46px; height: 46px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 1px 3px rgba(0,0,0,0.15); padding: 4px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <img src="{{ url('/logo.png') }}" alt="SIRKULO Logo" style="width: 100%; height: 100%; object-fit: contain; display: block;">
            </div>
            <div>
                <div style="color: #ffffff; font-size: 17pt; font-weight: 800; letter-spacing: 0.5px; line-height: 1.1;">
                    SIRKULO
                </div>
                <div style="color: #dcfce7; font-size: 8.5pt; font-weight: 400; margin-top: 3px;">
                    Sistem Informasi Rekam Data Pengelolaan Sampah
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Laporan Resmi & Subtitle --}}
        <div style="text-align: right;">
            <div style="color: #ffffff; font-size: 11pt; font-weight: 800; letter-spacing: 0.5px;">
                LAPORAN RESMI
            </div>
            <div style="color: #dcfce7; font-size: 8.5pt; font-weight: 400; margin-top: 3px;">
                Bank Sampah Digital
            </div>
        </div>
    </div>

    {{-- ── 2. JUDUL DOKUMEN & PERIODE ────────────────────────────────────── --}}
    <div style="padding: 16px 24px 8px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px;">
            <div>
                <div style="font-size: 13.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.5px;">
                    LAPORAN PENDAPATAN &amp; STATISTIK
                </div>
                <div style="margin-top: 5px; font-size: 9pt; color: #475569; line-height: 1.6;">
                    <div>Periode &nbsp;: &nbsp;<strong style="color: #0f172a;">{{ $namaBulan }} {{ $tahun }}</strong></div>
                    <div>Dicetak &nbsp;: &nbsp;<span style="color: #334155;" class="live-doc-print-time">{{ $tanggalCetakLengkap }}</span></div>
                </div>
            </div>
            <div style="text-align: right; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 6px 14px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <div style="font-size: 7.5pt; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">Nomor Laporan</div>
                <div class="live-doc-nomor font-mono" style="font-size: 9.5pt; font-weight: 800; color: #166534; margin-top: 1px;">{{ $nomorLaporan }}</div>
            </div>
        </div>
    </div>

    {{-- ── 3. INFORMASI POS ──────────────────────────────────────────────── --}}
    <div style="padding: 10px 24px;">
        <div style="font-size: 10.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px; padding-bottom: 5px; border-bottom: 1.5px solid #cbd5e1; margin-bottom: 8px;">
            INFORMASI POS
        </div>
        <table style="width: 100%; font-size: 9pt; border-collapse: collapse; line-height: 1.7;">
            <tr>
                <td style="width: 170px; font-weight: 600; color: #475569; vertical-align: top;">Nama Pos / Instansi</td>
                <td style="width: 15px; font-weight: 600; color: #475569; vertical-align: top;">:</td>
                <td style="font-weight: 700; color: #0f172a; vertical-align: top;" class="live-pos-nama">{{ $posInfoNama }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #475569; vertical-align: top;">Kode Unit Pos</td>
                <td style="width: 15px; font-weight: 600; color: #475569; vertical-align: top;">:</td>
                <td style="font-weight: 700; color: #0f172a; vertical-align: top; font-family: monospace;" class="live-pos-kode">{{ $posInfoKode }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #475569; vertical-align: top;">Pengelola / Penanggung Jawab</td>
                <td style="width: 15px; font-weight: 600; color: #475569; vertical-align: top;">:</td>
                <td style="font-weight: 700; color: #0f172a; vertical-align: top;" class="live-pos-pengelola">{{ $posInfoPengelola }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #475569; vertical-align: top;">Alamat / Lokasi</td>
                <td style="width: 15px; font-weight: 600; color: #475569; vertical-align: top;">:</td>
                <td style="font-weight: 700; color: #0f172a; vertical-align: top;" class="live-pos-alamat">{{ $posInfoAlamat }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600; color: #475569; vertical-align: top;">Jam Operasional</td>
                <td style="width: 15px; font-weight: 600; color: #475569; vertical-align: top;">:</td>
                <td style="font-weight: 700; color: #0f172a; vertical-align: top;" class="live-pos-jam">{{ $posInfoJam }}</td>
            </tr>
        </table>
    </div>

    {{-- ── 4. RINGKASAN KEUANGAN ─────────────────────────────────────────── --}}
    <div style="padding: 10px 24px;" class="avoid-break">
        <div style="padding-bottom: 5px; border-bottom: 1.5px solid #cbd5e1; margin-bottom: 10px;">
            <span style="font-size: 10.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">RINGKASAN KEUANGAN &amp; MARGIN KAS</span>
        </div>

        {{-- 4 Kartu Sejajar: Beli Nasabah, Penarikan, Kas Tabungan, Margin Kas Desa/Pos --}}
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
            {{-- Total Pendapatan Setoran (Beli Warga) --}}
            <div style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 12px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <div style="font-size: 7.5pt; color: #475569; font-weight: 600;">Total Tabungan Masuk</div>
                <div style="font-size: 12.5pt; font-weight: 800; color: #166534; margin-top: 2px;">
                    Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}
                </div>
                <div style="font-size: 7pt; color: #64748b; margin-top: 2px;">
                    {{ $totalSetoran }} transaksi setoran
                </div>
            </div>

            {{-- Total Penarikan Nasabah --}}
            <div style="background-color: #fefce8; border: 1px solid #fef08a; border-radius: 8px; padding: 10px 12px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <div style="font-size: 7.5pt; color: #475569; font-weight: 600;">Total Penarikan Saldo</div>
                <div style="font-size: 12.5pt; font-weight: 800; color: #b45309; margin-top: 2px;">
                    - Rp {{ number_format($totalPenarikanRp, 0, ',', '.') }}
                </div>
                <div style="font-size: 7pt; color: #64748b; margin-top: 2px;">
                    {{ $totalPenarikanTrx }} kali penarikan
                </div>
            </div>

            {{-- Saldo Kas Tabungan Bersih --}}
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <div style="font-size: 7.5pt; color: #475569; font-weight: 600;">Saldo Tabungan Bersih</div>
                <div style="font-size: 12.5pt; font-weight: 800; color: {{ $saldoKasBersih >= 0 ? '#166534' : '#dc2626' }}; margin-top: 2px;">
                    Rp {{ number_format($saldoKasBersih, 0, ',', '.') }}
                </div>
                <div style="font-size: 7pt; color: {{ $saldoKasBersih >= 0 ? '#166534' : '#dc2626' }}; font-weight: 700; margin-top: 2px;">
                    {{ $saldoKasBersih >= 0 ? 'Surplus Tabungan ✓' : 'Defisit Kas ⚠' }}
                </div>
            </div>

            {{-- Margin Keuntungan Kas --}}
            <div style="background-color: #ecfdf5; border: 1.5px solid #10b981; border-radius: 8px; padding: 10px 12px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                <div style="font-size: 7.5pt; color: #065f46; font-weight: 700;">{{ $selectedPos ? 'Margin Kas ' . $selectedPos->nama : 'Margin Kas Seluruh Pos Desa' }}</div>
                <div style="font-size: 12.5pt; font-weight: 900; color: #047857; margin-top: 2px;">
                    + Rp {{ number_format($totalMarginKeuntungan, 0, ',', '.') }}
                </div>
                <div style="font-size: 7pt; color: #047857; font-weight: 700; margin-top: 2px;">
                    +{{ $persentaseTotalMargin }}% &bull; Jual Rp {{ number_format($totalNilaiPengepul, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── 5. RINCIAN SETORAN PER JENIS SAMPAH & MARGIN ───────────────────── --}}
    <div style="padding: 10px 24px;" class="avoid-break">
        <div style="padding-bottom: 5px; border-bottom: 1.5px solid #cbd5e1; margin-bottom: 10px;">
            <span style="font-size: 10.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">
                RINCIAN SETORAN &amp; MARGIN PER JENIS SAMPAH
            </span>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
            <thead>
                <tr style="background-color: #166534; color: #ffffff; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                    <th style="width: 5%; padding: 8px 6px; text-align: center; font-weight: 700;">No</th>
                    <th style="width: 27%; padding: 8px 10px; text-align: left; font-weight: 700;">Jenis Sampah</th>
                    <th style="width: 10%; padding: 8px 6px; text-align: center; font-weight: 700;">Trx</th>
                    <th style="width: 14%; padding: 8px 10px; text-align: right; font-weight: 700;">Berat</th>
                    <th style="width: 14%; padding: 8px 10px; text-align: right; font-weight: 700;">Beli Warga</th>
                    <th style="width: 14%; padding: 8px 10px; text-align: right; font-weight: 700;">Jual Pengepul</th>
                    <th style="width: 16%; padding: 8px 10px; text-align: right; font-weight: 700;">Margin Kas (Rp / %)</th>
                </tr>
            </thead>
            <tbody>
                @php $noPdf = 1; @endphp
                @forelse($rincianKategori as $rk)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 7px 6px; text-align: center; color: #475569;">{{ $noPdf++ }}</td>
                    <td style="padding: 7px 10px; font-weight: 600; color: #0f172a;">{{ $rk['nama'] }}</td>
                    <td style="padding: 7px 6px; text-align: center; color: #334155;">{{ $rk['transaksi'] }} Trx</td>
                    <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #0f172a;">{{ number_format($rk['total_kg'], 1, ',', '.') }} kg</td>
                    <td style="padding: 7px 10px; text-align: right; font-weight: 600; color: #334155;">Rp {{ number_format($rk['total_rp'], 0, ',', '.') }}</td>
                    <td style="padding: 7px 10px; text-align: right; font-weight: 600; color: #0369a1;">Rp {{ number_format($rk['total_pengepul'], 0, ',', '.') }}</td>
                    <td style="padding: 7px 10px; text-align: right;">
                        <span style="font-weight: 800; color: #166534;">+Rp {{ number_format($rk['margin_rp'], 0, ',', '.') }}</span>
                        <span style="font-size: 7.5pt; color: #047857; font-weight: 600;"> (+{{ $rk['margin_persen'] }}%)</span>
                    </td>
                </tr>
                @empty
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td colspan="7" style="padding: 16px 12px; text-align: center; color: #64748b; font-style: italic; font-size: 9pt;">
                        Belum ada transaksi setoran pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: 800; color: #0f172a; border-top: 2px solid #cbd5e1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                    <td colspan="2" style="padding: 8px 10px; text-align: left; letter-spacing: 0.3px;">
                        {{ $selectedPos ? 'TOTAL POS ' . strtoupper($selectedPos->nama) : 'TOTAL SELURUH POS DESA' }}
                    </td>
                    <td style="padding: 8px 6px; text-align: center;">{{ $totalSetoran }} Trx</td>
                    <td style="padding: 8px 10px; text-align: right; font-family: monospace;">{{ number_format($totalBeratKg, 1, ',', '.') }} kg</td>
                    <td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #334155;">Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</td>
                    <td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #0369a1;">Rp {{ number_format($totalNilaiPengepul, 0, ',', '.') }}</td>
                    <td style="padding: 8px 10px; text-align: right; font-weight: 900; color: #166534;">
                        +Rp {{ number_format($totalMarginKeuntungan, 0, ',', '.') }}
                        <span style="font-size: 7.5pt; color: #047857;">(+{{ $persentaseTotalMargin }}%)</span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    @if(!$selectedPos)
    {{-- ── 5B. REKAPITULASI & MARGIN KEUNTUNGAN TIAP POS BANK SAMPAH (HANYA JIKA FILTER SEMUA POS) ── --}}
    <div style="padding: 10px 24px;" class="avoid-break">
        <div style="padding-bottom: 5px; border-bottom: 1.5px solid #cbd5e1; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: flex-end;">
            <span style="font-size: 10.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">
                REKAPITULASI &amp; MARGIN KEUNTUNGAN TIAP POS BANK SAMPAH
            </span>
            <span style="font-size: 8pt; color: #64748b; font-weight: 600;">
                {{ count($rincianPerPos) }} Unit Pos Terdata
            </span>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
            <thead>
                <tr style="background-color: #166534; color: #ffffff; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                    <th style="width: 5%; padding: 8px 6px; text-align: center; font-weight: 700;">No</th>
                    <th style="width: 25%; padding: 8px 10px; text-align: left; font-weight: 700;">Nama Pos Bank Sampah</th>
                    <th style="width: 8%; padding: 8px 6px; text-align: center; font-weight: 700;">Trx</th>
                    <th style="width: 14%; padding: 8px 10px; text-align: right; font-weight: 700;">Total Berat</th>
                    <th style="width: 15%; padding: 8px 10px; text-align: right; font-weight: 700;">Beli Warga (Rp)</th>
                    <th style="width: 15%; padding: 8px 10px; text-align: right; font-weight: 700;">Jual Pengepul (Rp)</th>
                    <th style="width: 18%; padding: 8px 10px; text-align: right; font-weight: 700;">Margin Keuntungan</th>
                </tr>
            </thead>
            <tbody>
                @php $noPosPdf = 1; @endphp
                @forelse($rincianPerPos as $rp)
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 7px 6px; text-align: center; color: #475569;">{{ $noPosPdf++ }}</td>
                    <td style="padding: 7px 10px; color: #0f172a;">
                        <div style="font-weight: 700;">{{ $rp['nama_pos'] }}</div>
                        <div style="font-size: 7pt; color: #64748b;">{{ $rp['kode_pos'] }} &bull; {{ $rp['alamat'] }}</div>
                    </td>
                    <td style="padding: 7px 6px; text-align: center; color: #334155;">{{ $rp['transaksi'] }} Trx</td>
                    <td style="padding: 7px 10px; text-align: right; font-family: monospace; font-weight: 600; color: #0f172a;">
                        {{ number_format($rp['total_kg'], 1, ',', '.') }} kg
                        <div style="font-size: 7pt; color: #166534;">{{ $rp['total_tonase'] }} Ton</div>
                    </td>
                    <td style="padding: 7px 10px; text-align: right; font-weight: 600; color: #334155;">
                        Rp {{ number_format($rp['total_rp'], 0, ',', '.') }}
                    </td>
                    <td style="padding: 7px 10px; text-align: right; font-weight: 600; color: #0369a1;">
                        Rp {{ number_format($rp['total_pengepul'], 0, ',', '.') }}
                    </td>
                    <td style="padding: 7px 10px; text-align: right;">
                        <div style="font-weight: 800; color: #166534;">+Rp {{ number_format($rp['margin_rp'], 0, ',', '.') }}</div>
                        <div style="font-size: 7pt; color: #047857; font-weight: 600;">+{{ $rp['margin_persen'] }}% margin</div>
                    </td>
                </tr>
                @empty
                <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td colspan="7" style="padding: 16px 12px; text-align: center; color: #64748b; font-style: italic; font-size: 9pt;">
                        Belum ada pos yang mencatat transaksi setoran pada periode ini
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr style="background-color: #f1f5f9; font-weight: 800; color: #0f172a; border-top: 2px solid #cbd5e1; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
                    <td colspan="2" style="padding: 8px 10px; text-align: left; letter-spacing: 0.3px;">TOTAL SELURUH POS DESA</td>
                    <td style="padding: 8px 6px; text-align: center;">{{ $totalSetoran }} Trx</td>
                    <td style="padding: 8px 10px; text-align: right; font-family: monospace;">
                        {{ number_format($totalBeratKg, 1, ',', '.') }} kg
                    </td>
                    <td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #334155;">
                        Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}
                    </td>
                    <td style="padding: 8px 10px; text-align: right; font-weight: 800; color: #0369a1;">
                        Rp {{ number_format($totalNilaiPengepul, 0, ',', '.') }}
                    </td>
                    <td style="padding: 8px 10px; text-align: right;">
                        <div style="font-weight: 900; color: #166534;">+Rp {{ number_format($totalMarginKeuntungan, 0, ',', '.') }}</div>
                        <div style="font-size: 7pt; color: #047857; font-weight: 700;">+{{ $persentaseTotalMargin }}% (Seluruh Pos)</div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    @else
    {{-- ── 5B. INFORMASI & MARGIN KHUSUS UNIT POS TERPILIH (JIKA DIFILTER SATU POS) ── --}}
    <div style="padding: 10px 24px;" class="avoid-break">
        <div style="padding-bottom: 5px; border-bottom: 1.5px solid #cbd5e1; margin-bottom: 10px;">
            <span style="font-size: 10.5pt; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 0.3px;">
                RINCIAN KINERJA &amp; MARGIN KAS: {{ strtoupper($selectedPos->nama) }}
            </span>
        </div>

        <div style="border: 1.5px solid #166534; border-radius: 8px; background-color: #f0fdf4; padding: 12px 16px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
            <table style="width: 100%; border-collapse: collapse; font-size: 8.5pt;">
                <tr>
                    <td style="width: 52%; vertical-align: top; padding-right: 14px;">
                        <div style="font-size: 7.5pt; color: #166534; font-weight: 800; text-transform: uppercase;">Unit Pos Bank Sampah</div>
                        <div style="font-size: 11.5pt; font-weight: 800; color: #0f172a; margin-top: 2px;">{{ $selectedPos->nama }}</div>
                        <div style="font-size: 8pt; color: #475569; margin-top: 3px;">
                            Kode: <strong style="color: #166534;">{{ $posInfoKode }}</strong> &bull; PIC / Pengelola: <strong>{{ $posInfoPengelola }}</strong>
                        </div>
                        <div style="font-size: 8pt; color: #64748b; margin-top: 2px;">
                            Wilayah / Alamat: {{ $posInfoAlamat }}
                        </div>
                        <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">
                            Jam Operasional: {{ $posInfoJam }}
                        </div>
                    </td>
                    <td style="width: 48%; vertical-align: top; border-left: 1.5px solid #bbf7d0; padding-left: 16px;">
                        <div style="font-size: 7.5pt; color: #166534; font-weight: 800; text-transform: uppercase;">Margin Kas Pos Ini</div>
                        <div style="font-size: 13pt; font-weight: 900; color: #166534; margin-top: 2px;">
                            + Rp {{ number_format($totalMarginKeuntungan, 0, ',', '.') }}
                            <span style="font-size: 8.5pt; color: #047857; font-weight: 700;">(+{{ $persentaseTotalMargin }}%)</span>
                        </div>
                        <div style="font-size: 7.5pt; color: #475569; margin-top: 4px;">
                            Beli Nasabah: <strong>Rp {{ number_format($totalNilaiSampah, 0, ',', '.') }}</strong> &bull; Jual Pengepul: <strong style="color: #0369a1;">Rp {{ number_format($totalNilaiPengepul, 0, ',', '.') }}</strong>
                        </div>
                        <div style="font-size: 7.5pt; color: #166534; font-weight: 600; margin-top: 3px;">
                            Total Setoran: {{ $totalSetoran }} Trx &bull; Tonase: {{ number_format($totalBeratKg, 1, ',', '.') }} kg ({{ $totalTonase }} Ton)
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endif

    {{-- ── 6. CATATAN / KETERANGAN KHUSUS LAPORAN (OPSIONAL) ──────────────── --}}
    <div class="live-blok-catatan hidden avoid-break" style="padding: 6px 24px 10px 24px;">
        <div style="background-color: #f8fafc; border-left: 3.5px solid #166534; padding: 9px 14px; font-size: 8.5pt; color: #334155; line-height: 1.5; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; border-radius: 4px;">
            <div style="font-weight: 700; color: #0f172a; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px;">Catatan / Keterangan Tambahan:</div>
            <div class="live-doc-catatan" style="white-space: pre-line;"></div>
        </div>
    </div>

    {{-- ── 7. OPSIONAL: LEMBAR PENGESAHAN (3 PIHAK) ──────────────────────── --}}
    <div class="blok-pengesahan-tanda-tangan hidden avoid-break" style="padding: 12px 24px 6px 24px;">
        <div style="text-align: right; font-size: 8.5pt; color: #475569; margin-bottom: 8px;">
            <span class="live-doc-tempat">Ditetapkan di Kantor Balai Desa</span>, <strong style="color: #0f172a;">{{ $tanggalCetak }}</strong>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; text-align: center; font-size: 8.5pt;">
            <div>
                <div style="color: #64748b;">Mengetahui,</div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;" class="live-ttd1-jabatan">Kepala Desa</div>
                <div style="height: 48px; display: flex; align-items: center; justify-content: center; font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                    ( Cap Stempel Desa )
                </div>
                <div style="font-weight: 700; text-decoration: underline; color: #0f172a;" class="live-ttd1-nama">( ........................................ )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 1px;" class="live-ttd1-nip">NIP. .....................................</div>
            </div>

            <div>
                <div style="color: #64748b;">Menyetujui,</div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;" class="live-ttd2-jabatan">Direktur BUMDes</div>
                <div style="height: 48px; display: flex; align-items: center; justify-content: center; font-size: 7.5pt; color: #94a3b8; font-style: italic;">
                    ( Cap Stempel BUMDes )
                </div>
                <div style="font-weight: 700; text-decoration: underline; color: #0f172a;" class="live-ttd2-nama">( ........................................ )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 1px;" class="live-ttd2-nip">NIK/NIP. .................................</div>
            </div>

            <div>
                <div style="color: #64748b;">Dibuat Oleh,</div>
                <div style="font-weight: 700; color: #0f172a; margin-top: 2px;" class="live-ttd3-jabatan">{{ $selectedPos ? 'Penanggung Jawab Pos' : 'Koordinator Bank Sampah' }}</div>
                <div style="height: 48px;"></div>
                <div style="font-weight: 700; text-decoration: underline; color: #0f172a;" class="live-ttd3-nama">( {{ $selectedPos ? $posInfoPengelola : session('admin_nama', 'Administrator') }} )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 1px;" class="live-ttd3-sub">{{ $selectedPos ? $posInfoNama : 'Pengelola Sistem SIRKULO' }}</div>
            </div>
        </div>
    </div>

    {{-- ── 8. VALIDASI DOKUMEN & QR CODE RESMI (HANYA MUNCUL SAAT DICETAK) ── --}}
    <div class="blok-validasi-cetak avoid-break" style="padding: 6px 24px 8px 24px;">
        <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-left: 4px solid #166534; border-radius: 6px; padding: 8px 14px; display: flex; align-items: center; gap: 14px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
            {{-- Kotak QR Code --}}
            <div style="flex-shrink: 0; text-align: center;">
                <div class="doc-qr-box" data-url="{{ $verifikasiUrl }}" style="width: 64px; height: 64px; background-color: #ffffff; border: 1px solid #cbd5e1; padding: 2px; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                    {{-- QR Code di-generate otomatis via JS saat tombol cetak ditekan --}}
                </div>
            </div>

            {{-- Keterangan Validasi --}}
            <div style="flex: 1; font-size: 7.5pt; color: #334155; line-height: 1.4;">
                <div style="font-size: 8.5pt; font-weight: 800; color: #0f172a; letter-spacing: 0.3px;">
                    Verifikasi Dokumen Resmi
                </div>
                <div style="color: #475569; margin-top: 2px;">
                    Pindai QR Code di samping untuk memeriksa keaslian dan kesesuaian data laporan ini secara langsung pada portal verifikasi resmi desa.
                </div>
                <div style="margin-top: 4px; display: flex; flex-wrap: wrap; gap: 10px; font-size: 7pt; color: #64748b;">
                    <div>Kode Verifikasi: <strong style="font-family: monospace; color: #0f172a;" class="live-doc-print-kode">{{ $kodeKeamanan }}</strong></div>
                    <div>&bull;</div>
                    <div>ID Cetak: <span style="font-family: monospace; color: #166534; font-weight: 700;" class="live-doc-print-serial">PRT-LIVE</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 7. FOOTER RESMI DOKUMEN ────────────────────────────────────── --}}
    <div style="background-color: #166534; color: #ffffff; text-align: center; padding: 7px 18px; margin-top: 8px; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;">
        <div style="font-size: 7.5pt; font-weight: 700; letter-spacing: 0.3px;">
            SIRKULO &bull; Sistem Informasi Rekam Data Bank Sampah Digital Desa
        </div>
        <div style="font-size: 6.8pt; color: #dcfce7; margin-top: 1px;">
            Dicetak otomatis oleh sistem &nbsp;|&nbsp; Operator: {{ session('admin_nama', 'Administrator') }}
        </div>
    </div>

</div>
