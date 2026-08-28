<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasUuids;

    protected $table = 'transaksi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna', 'id_mitra', 'jenis', 'status',
        'jumlah_total', 'poin_didapat', 'nomor_referensi', 'keterangan',
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

    public function mitra()
    {
        return $this->belongsTo(Partner::class, 'id_mitra');
    }
}
