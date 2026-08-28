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

    public function getKodeUserAttribute(): string
    {
        if (strtolower($this->email ?? '') === 'itang@gmail.com') {
            return 'SRKL001';
        }
        if (strtolower($this->email ?? '') === 'mitrasirkulo@gmail.com') {
            return 'SRKL-ADM';
        }

        // Format kode pendek SRKL(nomor) 3 digit
        $digits = preg_replace('/[^0-9]/', '', (string)$this->id);
        if (strlen($digits) >= 3) {
            return 'SRKL' . substr($digits, 0, 3);
        }
        $num = (abs(crc32((string)$this->id)) % 900) + 100;
        return 'SRKL' . sprintf('%03d', $num);
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
