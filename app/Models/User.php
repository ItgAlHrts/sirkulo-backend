<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasUuids, HasApiTokens;

    // Nama tabel
    protected $table = 'pengguna';

    // Nama kolom timestamp kustom
    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama', 'email', 'foto_url', 'kata_sandi', 'telepon', 'alamat',
        'saldo', 'poin', 'peran', 'otp_reset', 'kadaluarsa_otp',
    ];

    protected $appends = ['kode_user'];

    public function getPoinAttribute(): int
    {
        // 1 Poin = Rp 100
        return (int) floor(((int)($this->attributes['saldo'] ?? 0)) / 100);
    }

    public function getFotoUrlAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?(/.*)?$#i', $value, $matches)) {
                $path = $matches[3] ?? '';
                return request()->getSchemeAndHttpHost() . $path;
            }
            return $value;
        }
        $cleanPath = ltrim($value, '/');
        if (!str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = 'storage/' . $cleanPath;
        }
        return request()->getSchemeAndHttpHost() . '/' . $cleanPath;
    }

    public function getKodeUserAttribute(): string
    {
        if (strtolower($this->email ?? '') === 'mitrasirkulo@gmail.com' || ($this->peran ?? '') === 'MITRA') {
            return 'SRKL-ADM';
        }

        // Urutan nomor anggota berurutan (SRKL001, SRKL002, SRKL003, dst)
        try {
            $allNasabahIds = \Illuminate\Support\Facades\Cache::remember('nasabah_id_order_list', 5, function () {
                return self::where('peran', 'NASABAH')
                    ->orderBy('dibuat_pada', 'asc')
                    ->orderBy('id', 'asc')
                    ->pluck('id')
                    ->toArray();
            });

            $index = array_search($this->id, $allNasabahIds);
            if ($index !== false) {
                return 'SRKL' . sprintf('%03d', $index + 1);
            }
        } catch (\Throwable $e) {
            // fallback
        }

        // Fallback jika tidak ditemukan: 4 karakter unik terakhir dari ID
        $cleanId = str_replace('-', '', (string)$this->id);
        return 'SRKL' . strtoupper(substr($cleanId, -4));
    }

    protected $hidden = [
        'kata_sandi',
    ];

    protected function casts(): array
    {
        return [
            'dibuat_pada'     => 'datetime:Y-m-d H:i:s',
            'diperbarui_pada' => 'datetime:Y-m-d H:i:s',
        ];
    }

    protected function serializeDate(\DateTimeInterface $date)
    {
        return \Carbon\Carbon::instance($date)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    // Beritahu Laravel kolom password menggunakan nama 'kata_sandi'
    public function getAuthPassword(): string
    {
        return $this->kata_sandi;
    }

    // Beritahu Laravel nama KOLOM password (untuk Guard)
    public function getAuthPasswordName(): string
    {
        return 'kata_sandi';
    }

    // Relasi
    public function mitra()
    {
        return $this->hasOne(Partner::class, 'id_pengguna');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaction::class, 'id_pengguna');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notification::class, 'id_pengguna');
    }
}
