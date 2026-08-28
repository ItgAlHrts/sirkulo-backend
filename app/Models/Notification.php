<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasUuids;

    protected $table = 'notifikasi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna', 'judul', 'deskripsi', 'jenis',
    ];

    protected $casts = [
        'dibuat_pada'     => 'datetime:Y-m-d H:i:s',
        'diperbarui_pada' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return \Carbon\Carbon::instance($date)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }
}
