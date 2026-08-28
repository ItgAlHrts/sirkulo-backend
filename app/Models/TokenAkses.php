<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class TokenAkses extends SanctumPersonalAccessToken
{
    /**
     * Nama tabel di database.
     */
    protected $table = 'token_akses';

    /**
     * Nonaktifkan timestamps otomatis (kolom kita kustom).
     */
    public $timestamps = false;

    /**
     * Map kolom kustom ke field yang diharapkan Sanctum.
     * Sanctum membaca: name, token, abilities, last_used_at, expires_at
     * Kolom kita: nama, token, kemampuan, terakhir_digunakan, kadaluarsa
     */
    protected $fillable = [
        'tokenable_type',
        'tokenable_id',
        'nama',
        'token',
        'kemampuan',
        'terakhir_digunakan',
        'kadaluarsa',
        'dibuat_pada',
    ];

    // Sanctum butuh 'name' → kita redirect ke 'nama'
    public function getNameAttribute(): string
    {
        return $this->nama ?? '';
    }

    // Sanctum butuh 'abilities' → kita redirect ke 'kemampuan'
    public function getAbilitiesAttribute(): array
    {
        $kemampuan = $this->kemampuan;
        if (!$kemampuan) return ['*'];
        $decoded = json_decode($kemampuan, true);
        return is_array($decoded) ? $decoded : ['*'];
    }

    // Sanctum butuh 'last_used_at' → kita redirect ke 'terakhir_digunakan'
    public function getLastUsedAtAttribute()
    {
        return $this->terakhir_digunakan;
    }

    // Sanctum butuh 'expires_at' → kita redirect ke 'kadaluarsa'
    public function getExpiresAtAttribute()
    {
        return $this->kadaluarsa;
    }

    // Saat Sanctum mau update 'last_used_at', simpan ke 'terakhir_digunakan'
    public function setLastUsedAtAttribute($value): void
    {
        $this->attributes['terakhir_digunakan'] = $value;
    }

    // Tangani updated_at / created_at kustom
    public function freshTimestamp()
    {
        return now();
    }

    public function getCreatedAtColumn(): string
    {
        return 'dibuat_pada';
    }

    public function getUpdatedAtColumn(): string
    {
        return 'diperbarui_pada';
    }
}
