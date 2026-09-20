<?php

namespace App\Services;

use Illuminate\Support\Str;

/**
 * ReceiptImageService — Generate struk transaksi sebagai gambar PNG.
 *
 * Menggunakan PHP GD (built-in, tanpa library tambahan).
 * Secara otomatis mendeteksi font TTF dari sistem (Windows/Linux)
 * untuk hasil yang lebih baik; jika tidak ada, fallback ke GD built-in font.
 *
 * Output gambar disimpan di: storage/app/public/receipts/
 * URL publik: {APP_URL}/storage/receipts/...
 */
class ReceiptImageService
{
    // ── Canvas ──────────────────────────────────────────────────────────────
    const W             = 900;
    const H_SETORAN     = 1240;
    const H_PENARIKAN   = 1080;

    // ── Warna (R, G, B) ──────────────────────────────────────────────────────
    const C_BG          = [255, 255, 255];
    const C_HEADER_TOP  = [22,  84,  57];  // hijau tua
    const C_HEADER_BOT  = [44, 130,  88];  // hijau sedang
    const C_ACCENT      = [52, 168, 109];  // aksen hijau
    const C_HIGHLIGHT   = [209, 242, 220]; // hijau pucat
    const C_TEXT        = [25,  25,  25];
    const C_LABEL       = [100, 100, 100];
    const C_SEP         = [218, 218, 218];
    const C_ROW_ALT     = [248, 249, 250];
    const C_BADGE_BG    = [209, 242, 220];
    const C_BADGE_FG    = [22,  120,  68];
    const C_SALDO_BG    = [22,  84,  57];  // kotak saldo (hijau tua)
    const C_WHITE       = [255, 255, 255];
    const C_GOLD        = [255, 210,  50];
    const C_FOOTER_BG   = [245, 245, 245];

    // ── Font Sizes (TTF pt) ───────────────────────────────────────────────────
    const FS_LOGO       = 42;
    const FS_TITLE      = 24;
    const FS_SUBTITLE   = 18;
    const FS_BODY       = 17;
    const FS_LABEL      = 14;
    const FS_TINY       = 12;

    private $img;
    private array $c = [];
    private int $canvasH;
    private ?string $fontReg  = null;
    private ?string $fontBold = null;
    private bool $hasTTF      = false;

    public function __construct()
    {
        $this->detectFont();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PUBLIC API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate gambar PNG struk SETORAN.
     *
     * @param array{
     *   nama: string, kode_pengguna: string, nomor_referensi: string,
     *   waktu: string, pos: string, jenis_sampah: string, berat_kg: string,
     *   nominal: string, metode: string, poin_didapat: string,
     *   saldo_baru: string, poin_baru: string
     * } $data
     * @return string Absolute path file PNG
     */
    public function generateSetoran(array $data): string
    {
        $this->guardGD();
        $this->cleanupOldFiles();
        $this->createCanvas(self::H_SETORAN);

        $y = 0;
        $this->drawHeader('STRUK SETORAN SAMPAH', $data['waktu'] ?? '', $y);
        $this->drawBadge('SETORAN BERHASIL DIPROSES', $y);
        $this->drawNasabahBox($data['nama'], $data['kode_pengguna'] ?? '', $y);

        $rows = [
            ['No. Referensi',  $data['nomor_referensi'], true,  true],
            ['Waktu',          $data['waktu'],           false, false],
            ['Unit Loket',     $data['pos'],             true,  false],
            ['Jenis Sampah',   $data['jenis_sampah'],    false, false],
            ['Berat Sampah',   $data['berat_kg'] . ' kg', true, false],
            ['Nilai Sampah',   'Rp ' . $data['nominal'], false, true],
            ['Metode Bayar',   $data['metode'],          true,  false],
            ['Poin Didapat',   '+' . $data['poin_didapat'] . ' Poin', false, true, self::C_BADGE_FG],
        ];
        $this->drawRows($rows, $y);

        $this->drawSaldoBox(
            'Rp ' . $data['saldo_baru'],
            $data['poin_baru'] . ' Poin',
            $y
        );

        $this->drawMotivasi(
            'Terima kasih telah menyetorkan sampah Anda!',
            'Bersama kita jaga kebersihan desa untuk generasi mendatang.',
            $y
        );

        $this->drawFooter($data['nomor_referensi'], $y);

        return $this->save($data['nomor_referensi']);
    }

    /**
     * Generate gambar PNG struk PENARIKAN / PENCAIRAN SALDO.
     *
     * @param array{
     *   nama: string, kode_pengguna: string, nomor_referensi: string,
     *   waktu: string, pos: string, nominal: string,
     *   saldo_baru: string, poin_baru: string
     * } $data
     * @return string Absolute path file PNG
     */
    public function generatePenarikan(array $data): string
    {
        $this->guardGD();
        $this->cleanupOldFiles();
        $this->createCanvas(self::H_PENARIKAN);

        $y = 0;
        $this->drawHeader('STRUK PENCAIRAN SALDO', $data['waktu'] ?? '', $y);
        $this->drawBadge('PENCAIRAN SALDO BERHASIL DISERAHKAN', $y);
        $this->drawNasabahBox($data['nama'], $data['kode_pengguna'] ?? '', $y);

        $rows = [
            ['No. Referensi',  $data['nomor_referensi'], true,  true],
            ['Waktu',          $data['waktu'],           false, false],
            ['Loket Pencairan', $data['pos'],            true,  false],
            ['Metode',         'Tunai Kasir Loket',      false, false],
        ];
        $this->drawRows($rows, $y);

        $this->drawPenarikanBox(
            'Rp ' . $data['nominal'],
            'Rp ' . $data['saldo_baru'],
            $data['poin_baru'] . ' Poin',
            $y
        );

        $this->drawMotivasi(
            'Dana telah berhasil diserahkan di loket.',
            'Simpan struk ini sebagai bukti resmi pencairan saldo Anda.',
            $y
        );

        $this->drawFooter($data['nomor_referensi'], $y);

        return $this->save($data['nomor_referensi']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CANVAS PRIMITIVES
    // ─────────────────────────────────────────────────────────────────────────

    private function createCanvas(int $height): void
    {
        $this->canvasH = $height;
        $this->img = imagecreatetruecolor(self::W, $height);
        imagealphablending($this->img, true);
        imagesavealpha($this->img, true);

        // Alokasi warna
        $palette = [
            'bg'         => self::C_BG,
            'accent'     => self::C_ACCENT,
            'text'       => self::C_TEXT,
            'label'      => self::C_LABEL,
            'sep'        => self::C_SEP,
            'row_alt'    => self::C_ROW_ALT,
            'badge_bg'   => self::C_BADGE_BG,
            'badge_fg'   => self::C_BADGE_FG,
            'saldo_bg'   => self::C_SALDO_BG,
            'white'      => self::C_WHITE,
            'gold'       => self::C_GOLD,
            'footer_bg'  => self::C_FOOTER_BG,
            'highlight'  => self::C_HIGHLIGHT,
        ];
        foreach ($palette as $k => $rgb) {
            $this->c[$k] = imagecolorallocate($this->img, $rgb[0], $rgb[1], $rgb[2]);
        }

        // Background putih bersih
        imagefilledrectangle($this->img, 0, 0, self::W, $height, $this->c['bg']);
    }

    private function color(array $rgb): int
    {
        return imagecolorallocate($this->img, $rgb[0], $rgb[1], $rgb[2]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DRAWING BLOCKS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Header gradasi hijau: logo SIRKULO + judul struk.
     */
    private function drawHeader(string $judul, string $waktu, int &$y): void
    {
        // Gradient dari hijau tua (atas) ke hijau sedang (bawah)
        $topH = 210;
        for ($i = 0; $i < $topH; $i++) {
            $r = (int)(self::C_HEADER_TOP[0] + ($i / $topH) * (self::C_HEADER_BOT[0] - self::C_HEADER_TOP[0]));
            $g = (int)(self::C_HEADER_TOP[1] + ($i / $topH) * (self::C_HEADER_BOT[1] - self::C_HEADER_TOP[1]));
            $b = (int)(self::C_HEADER_TOP[2] + ($i / $topH) * (self::C_HEADER_BOT[2] - self::C_HEADER_TOP[2]));
            $lc = imagecolorallocate($this->img, $r, $g, $b);
            imageline($this->img, 0, $i, self::W, $i, $lc);
        }

        // Nama brand
        $this->txt('SIRKULO', self::W / 2, 62, $this->c['white'], self::FS_LOGO, true, 'center');

        // Tagline
        $this->txt('Bank Sampah Desa Digital', self::W / 2, 92, $this->c['white'], self::FS_LABEL, false, 'center');

        // Garis pemisah putih tipis
        $lineC = imagecolorallocate($this->img, 200, 230, 210);
        imagesetthickness($this->img, 1);
        imageline($this->img, 50, 108, self::W - 50, 108, $lineC);

        // Judul struk
        $this->txt($judul, self::W / 2, 148, $this->c['white'], self::FS_TITLE, true, 'center');

        // Waktu kecil
        $this->txt($waktu, self::W / 2, 175, $this->c['white'], self::FS_TINY, false, 'center');

        // Segitiga dekoratif bawah header
        $triColor = imagecolorallocate($this->img, 44, 130, 88);
        $points = [0, $topH, self::W, $topH, self::W / 2, $topH + 30];
        imagefilledpolygon($this->img, $points, 3, $this->c['bg']);

        $y = $topH + 30;
    }

    /**
     * Badge status (hijau terang) di bawah header.
     */
    private function drawBadge(string $label, int &$y): void
    {
        $padV = 12;
        imagefilledrectangle($this->img, 40, $y, self::W - 40, $y + $padV * 2 + 22, $this->c['badge_bg']);
        // Border
        $this->rect(40, $y, self::W - 40, $y + $padV * 2 + 22, $this->c['badge_fg'], false);

        $this->txt('✓ ' . $label, self::W / 2, $y + $padV + 20, $this->c['badge_fg'], self::FS_LABEL, true, 'center');
        $y += $padV * 2 + 22 + 20;
    }

    /**
     * Kotak info nasabah (nama + kode).
     */
    private function drawNasabahBox(string $nama, string $kode, int &$y): void
    {
        // Separator atas
        imagefilledrectangle($this->img, 0, $y, self::W, $y + 2, $this->c['sep']);
        $y += 2;

        // Baris nama
        imagefilledrectangle($this->img, 0, $y, self::W, $y + 50, $this->c['highlight']);
        $this->txt('NASABAH', 55, $y + 32, $this->c['label'], self::FS_TINY, false);
        $this->txt($nama, self::W - 55, $y + 32, $this->c['text'], self::FS_BODY, true, 'right');
        $y += 50;

        // Baris kode pengguna
        if (!empty($kode)) {
            imagefilledrectangle($this->img, 0, $y, self::W, $y + 40, $this->c['bg']);
            $this->txt('KODE', 55, $y + 26, $this->c['label'], self::FS_TINY);
            $this->txt($kode, self::W - 55, $y + 26, $this->c['label'], self::FS_LABEL, false, 'right');
            $y += 40;
        }

        // Separator bawah
        imagefilledrectangle($this->img, 0, $y, self::W, $y + 2, $this->c['sep']);
        $y += 12;
    }

    /**
     * Kumpulan baris data transaksi.
     *
     * @param array[] $rows [label, value, altBg, valueBold, ?valueColor]
     */
    private function drawRows(array $rows, int &$y): void
    {
        foreach ($rows as $i => $row) {
            [$label, $value, $alt, $bold] = $row;
            $vColorRgb = $row[4] ?? null;

            if ($alt) {
                imagefilledrectangle($this->img, 0, $y, self::W, $y + 46, $this->c['row_alt']);
            }

            $vColor = $vColorRgb ? $this->color($vColorRgb) : $this->c['text'];
            $this->txt($label, 55, $y + 30, $this->c['label'], self::FS_LABEL);
            $this->txt($value, self::W - 55, $y + 30, $vColor, self::FS_BODY, $bold, 'right');
            $y += 46;

            // Garis separator tipis (kecuali baris terakhir)
            if ($i < count($rows) - 1) {
                imageline($this->img, 55, $y, self::W - 55, $y, $this->c['sep']);
            }
        }
        $y += 4;
    }

    /**
     * Kotak saldo tabungan (untuk SETORAN).
     */
    private function drawSaldoBox(string $saldo, string $poin, int &$y): void
    {
        $boxH = 115;
        $y += 20;

        // Background hijau tua
        imagefilledrectangle($this->img, 40, $y, self::W - 40, $y + $boxH, $this->c['saldo_bg']);

        // Teks
        $this->txt('TOTAL SALDO TABUNGAN', self::W / 2, $y + 28, $this->c['white'], self::FS_TINY, false, 'center');
        $this->txt($saldo, self::W / 2, $y + 76, $this->c['white'], self::FS_LOGO, true, 'center');

        // Garis emas tipis
        $goldC = $this->color(self::C_GOLD);
        imageline($this->img, 80, $y + 88, self::W - 80, $y + 88, $goldC);

        // Poin (warna emas)
        $this->txt('Poin: ' . $poin, self::W / 2, $y + $boxH - 4, $goldC, self::FS_LABEL, false, 'center');

        $y += $boxH + 20;
    }

    /**
     * Kotak pencairan saldo (untuk PENARIKAN).
     */
    private function drawPenarikanBox(string $nominal, string $saldo, string $poin, int &$y): void
    {
        $boxH = 145;
        $y += 20;

        imagefilledrectangle($this->img, 40, $y, self::W - 40, $y + $boxH, $this->c['saldo_bg']);

        $this->txt('NOMINAL DICAIRKAN', self::W / 2, $y + 28, $this->c['white'], self::FS_TINY, false, 'center');
        $this->txt($nominal, self::W / 2, $y + 76, $this->c['white'], self::FS_LOGO, true, 'center');

        // Divider
        $divC = imagecolorallocate($this->img, 80, 160, 120);
        imageline($this->img, 60, $y + 88, self::W - 60, $y + 88, $divC);

        $goldC = $this->color(self::C_GOLD);
        $this->txt('Sisa Saldo: ' . $saldo, self::W / 2, $y + 112, $this->c['white'], self::FS_LABEL, false, 'center');
        $this->txt('Sisa Poin: ' . $poin, self::W / 2, $y + $boxH - 4, $goldC, self::FS_LABEL, false, 'center');

        $y += $boxH + 20;
    }

    /**
     * Teks motivasi / keterangan kecil.
     */
    private function drawMotivasi(string $line1, string $line2, int &$y): void
    {
        $this->txt($line1, self::W / 2, $y + 22, $this->c['accent'], self::FS_LABEL, false, 'center');
        $y += 35;
        $this->txt($line2, self::W / 2, $y + 18, $this->c['label'], self::FS_TINY, false, 'center');
        $y += 40;
    }

    /**
     * Footer dengan nomor referensi dan info sistem.
     */
    private function drawFooter(string $noRef, int $y): void
    {
        imagefilledrectangle($this->img, 0, $y, self::W, $this->canvasH, $this->c['footer_bg']);
        imageline($this->img, 0, $y, self::W, $y, $this->c['sep']);

        $y += 25;

        // Garis putus-putus
        for ($x = 55; $x < self::W - 55; $x += 18) {
            imageline($this->img, $x, $y, min($x + 9, self::W - 55), $y, $this->c['sep']);
        }
        $y += 18;

        $this->txt('No. Referensi: ' . $noRef, self::W / 2, $y + 20, $this->c['label'], self::FS_LABEL, false, 'center');
        $y += 36;

        $this->txt('Dokumen ini diterbitkan otomatis oleh Sistem Informasi SIRKULO Desa.', self::W / 2, $y + 16, $this->c['label'], self::FS_TINY, false, 'center');
        $y += 28;
        $this->txt('Simpan sebagai bukti transaksi resmi Anda.', self::W / 2, $y + 14, $this->c['label'], self::FS_TINY, false, 'center');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEXT HELPER
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Tulis teks ke canvas. Otomatis memilih TTF atau GD built-in font.
     *
     * @param string $align 'left'|'center'|'right'
     */
    private function txt(string $text, int $x, int $y, int $color, float $size, bool $bold = false, string $align = 'left'): void
    {
        if ($this->hasTTF) {
            $font = ($bold && $this->fontBold) ? $this->fontBold : $this->fontReg;

            // Hitung posisi berdasarkan alignment
            if ($align !== 'left') {
                $bbox = imagettfbbox($size, 0, $font, $text);
                $textW = abs($bbox[4] - $bbox[0]);
                if ($align === 'center') {
                    $x -= intval($textW / 2);
                } elseif ($align === 'right') {
                    $x -= $textW;
                }
            }
            imagettftext($this->img, $size, 0, $x, $y, $color, $font, $text);
        } else {
            // GD built-in font fallback
            $gdf = match (true) {
                $size >= 28 => 5,
                $size >= 18 => 4,
                $size >= 14 => 3,
                default     => 2,
            };
            $cw = imagefontwidth($gdf);
            $ch = imagefontheight($gdf);
            $tw = strlen($text) * $cw;

            $drawX = match ($align) {
                'center' => $x - intval($tw / 2),
                'right'  => $x - $tw,
                default  => $x,
            };
            // imagestring: y adalah koordinat top-left
            imagestring($this->img, $gdf, $drawX, $y - $ch, $text, $color);
        }
    }

    private function rect(int $x1, int $y1, int $x2, int $y2, int $color, bool $filled = true): void
    {
        if ($filled) {
            imagefilledrectangle($this->img, $x1, $y1, $x2, $y2, $color);
        } else {
            imagerectangle($this->img, $x1, $y1, $x2, $y2, $color);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FONT DETECTION
    // ─────────────────────────────────────────────────────────────────────────

    private function detectFont(): void
    {
        $regular = [
            // Windows
            'C:\\Windows\\Fonts\\arial.ttf',
            'C:\\Windows\\Fonts\\calibri.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            // Linux
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSans.ttf',
            '/usr/share/fonts/TTF/DejaVuSans.ttf',
            '/usr/local/share/fonts/DejaVuSans.ttf',
        ];
        $bold = [
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\calibrib.ttf',
            'C:\\Windows\\Fonts\\segoeuib.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/TTF/DejaVuSans-Bold.ttf',
        ];

        foreach ($regular as $path) {
            if (file_exists($path)) {
                $this->fontReg  = $path;
                $this->hasTTF   = true;
                break;
            }
        }
        foreach ($bold as $path) {
            if (file_exists($path)) {
                $this->fontBold = $path;
                break;
            }
        }
        // Jika tidak ada font bold, gunakan regular sebagai pengganti
        if ($this->hasTTF && $this->fontBold === null) {
            $this->fontBold = $this->fontReg;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // STORAGE & CLEANUP
    // ─────────────────────────────────────────────────────────────────────────

    private function ensureDir(): void
    {
        $dir = storage_path('app/public/receipts');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }

    private function cleanupOldFiles(): void
    {
        try {
            $dir = storage_path('app/public/receipts');
            if (!is_dir($dir)) return;
            $cutoff = time() - 86400; // 24 jam
            foreach (glob($dir . '/struk-*.png') as $file) {
                if (filemtime($file) < $cutoff) {
                    @unlink($file);
                }
            }
        } catch (\Throwable $e) {
            // Silent — jangan hentikan proses utama
        }
    }

    /**
     * Simpan image ke disk dan kembalikan path absolut.
     */
    private function save(string $noRef): string
    {
        $this->ensureDir();
        $slug     = Str::slug($noRef, '-');
        $filename = 'struk-' . $slug . '-' . time() . '.png';
        $path     = storage_path('app/public/receipts/' . $filename);
        imagepng($this->img, $path, 7);
        imagedestroy($this->img);
        return $path;
    }

    private function guardGD(): void
    {
        if (!extension_loaded('gd')) {
            throw new \RuntimeException(
                'PHP GD extension tidak aktif. ' .
                'Aktifkan dengan uncomment "extension=gd" di php.ini.'
            );
        }
    }
}
