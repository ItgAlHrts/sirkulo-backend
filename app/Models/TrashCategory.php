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
        'nama', 'harga_per_kg', 'harga_pengepul', 'ikon',
    ];

    protected $appends = [
        'harga_beli',
        'harga_jual',
        'margin_per_kg',
        'persentase_margin',
    ];

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
