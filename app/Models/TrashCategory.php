<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TrashCategory extends Model
{
    use HasUuids;

    protected $table = 'kategori_sampah';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama', 'harga_per_kg', 'harga_pengepul', 'ikon', 'foto_contoh',
    ];

    protected $casts = [
        'foto_contoh' => 'array',
    ];

    protected $appends = [
        'harga_beli',
        'harga_jual',
        'margin_per_kg',
        'persentase_margin',
        'foto_contoh',
    ];

    /**
     * Ikon / URL Foto Utama Sampah.
     */
    public function getIkonAttribute($value): string
    {
        if (empty($value)) {
            return 'ic_trash';
        }

        // Jika URL eksternal atau tersimpan dengan localhost/127.0.0.1
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?(/.*)?$#i', $value, $matches)) {
                $path = $matches[3] ?? '';
                return request()->getSchemeAndHttpHost() . $path;
            }
            return $value;
        }

        // Jika path relatif (misal 'storage/sampah/xxx.jpg' atau 'sampah/xxx.jpg')
        if (str_starts_with($value, 'storage/') || str_starts_with($value, 'sampah/')) {
            $cleanPath = ltrim($value, '/');
            if (!str_starts_with($cleanPath, 'storage/')) {
                $cleanPath = 'storage/' . $cleanPath;
            }
            return request()->getSchemeAndHttpHost() . '/' . $cleanPath;
        }

        return $value;
    }

    /**
     * Daftar Galeri Foto Contoh Sampah yang Diterima (Multi-Photo).
     */
    public function getFotoContohAttribute($value): array
    {
        $raw = $this->attributes['foto_contoh'] ?? $value ?? null;
        $list = [];

        if (!empty($raw)) {
            $list = is_array($raw) ? $raw : json_decode($raw, true);
            if (!is_array($list)) {
                $list = array_filter(explode(',', (string)$raw));
            }
        }

        // Jika foto_contoh kosong tapi ada ikon berformat foto url (http/storage), sertakan sebagai contoh pertama
        $ikon = $this->attributes['ikon'] ?? null;
        if (empty($list) && !empty($ikon) && (str_starts_with($ikon, 'http://') || str_starts_with($ikon, 'https://') || str_starts_with($ikon, 'storage/') || str_starts_with($ikon, 'sampah/'))) {
            $list = [$ikon];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (empty($item)) return null;
            if (str_starts_with($item, 'http://') || str_starts_with($item, 'https://')) {
                if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?(/.*)?$#i', $item, $matches)) {
                    $path = $matches[3] ?? '';
                    return request()->getSchemeAndHttpHost() . $path;
                }
                return $item;
            }
            if (str_starts_with($item, 'storage/') || str_starts_with($item, 'sampah/')) {
                $cleanPath = ltrim($item, '/');
                if (!str_starts_with($cleanPath, 'storage/')) {
                    $cleanPath = 'storage/' . $cleanPath;
                }
                return request()->getSchemeAndHttpHost() . '/' . $cleanPath;
            }
            return $item;
        }, (array) $list)));
    }

    /**
     * Harga Beli dari Nasabah (yang dibayarkan ke warga).
     */
    public function getHargaBeliAttribute(): int
    {
        return (int) $this->harga_per_kg;
    }

    /**
     * Harga Jual ke Pengepul / Pabrik.
     * Jika belum diatur, default ke harga beli + 40% (estimasi wajar).
     */
    public function getHargaJualAttribute(): int
    {
        if ($this->attributes['harga_pengepul'] ?? null) {
            return (int) $this->attributes['harga_pengepul'];
        }
        return (int) max($this->harga_per_kg + 500, round($this->harga_per_kg * 1.4));
    }

    /**
     * Margin / Keuntungan kotor per kg (Harga Pengepul - Harga Nasabah).
     */
    public function getMarginPerKgAttribute(): int
    {
        return max(0, $this->getHargaJualAttribute() - $this->getHargaBeliAttribute());
    }

    /**
     * Persentase Keuntungan margin.
     */
    public function getPersentaseMarginAttribute(): int
    {
        $beli = $this->getHargaBeliAttribute();
        if ($beli <= 0) return 0;
        return (int) round(($this->getMarginPerKgAttribute() / $beli) * 100);
    }
}
