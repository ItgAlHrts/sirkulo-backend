<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasUuids;

    protected $table = 'admin';

    public $incrementing = false;
    protected $keyType = 'string';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id',
        'nama',
        'email',
        'kata_sandi',
        'telepon',
        'peran',
        'otp_reset',
        'kadaluarsa_otp',
    ];

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

    public function isSuperAdmin(): bool
    {
        return strtoupper($this->peran ?? '') === 'SUPER_ADMIN';
    }

    public function getLabelPeranAttribute(): string
    {
        return $this->isSuperAdmin() ? 'Super Admin' : 'Admin Operasional';
    }
}
