<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Nasabah extends Authenticatable
{
    use HasUuids, HasApiTokens;

    protected $table = 'nasabah';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'nama',
        'email',
        'foto_url',
        'kata_sandi',
        'telepon',
        'alamat',
        'saldo',
        'poin',
        'peran',
        'otp_reset',
        'kadaluarsa_otp',
        'kode_user',
    ];

    protected $appends = [];

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
            if (preg_match('#^https?://(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+|172\.(1[6-9]|2\d|3[01])\.\d+\.\d+)(:\d+)?(/.*)?$#i', $value, $matches)) {
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
        // Baca dari kolom DB permanen terlebih dahulu (tidak akan berubah meski nasabah lain dihapus)
        $stored = $this->attributes['kode_user'] ?? null;
        if (!empty($stored)) {
            return $stored;
        }

        // Fallback: gunakan 4 karakter unik terakhir UUID (bukan posisi urutan)
        $cleanId = str_replace('-', '', (string)$this->id);
        return 'SRKL' . strtoupper(substr($cleanId, -4));
    }

    /**
     * Generate kode_user berikutnya secara otomatis (SRKL001, SRKL002, dst.).
     * Nomor bertambah terus — tidak pernah dipakai ulang walau nasabah dihapus.
     */
    public static function generateKodeUser(): string
    {
        // Ambil nomor tertinggi yang sudah pernah dipakai dari kolom kode_user
        $maxKode = self::whereNotNull('kode_user')
            ->where('kode_user', 'like', 'SRKL%')
            ->orderByRaw('CAST(SUBSTRING(kode_user, 5) AS UNSIGNED) DESC')
            ->value('kode_user');

        $nextNum = 1;
        if ($maxKode && preg_match('/^SRKL(\d+)$/', $maxKode, $m)) {
            $nextNum = (int) $m[1] + 1;
        }

        return 'SRKL' . sprintf('%03d', $nextNum);
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

    public function getAuthPassword(): string
    {
        return $this->kata_sandi;
    }

    public function getAuthPasswordName(): string
    {
        return 'kata_sandi';
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
