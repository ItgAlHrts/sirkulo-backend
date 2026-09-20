<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class PetugasMitra extends Authenticatable
{
    use HasUuids, HasApiTokens;

    protected $table = 'petugas_mitra';

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
        'peran',
    ];

    protected $appends = ['kode_user'];

    public function getKodeUserAttribute(): string
    {
        return 'SRKL-ADM';
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

    public function mitra()
    {
        return $this->hasOne(Partner::class, 'id_pengguna');
    }
}
