<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasUuids;

    protected $table = 'kritik_saran';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna', 'kategori', 'pesan', 'jawaban', 'dijawab_pada', 'id_mitra'
    ];

    protected $casts = [
        'dibuat_pada'     => 'datetime:Y-m-d H:i:s',
        'diperbarui_pada' => 'datetime:Y-m-d H:i:s',
        'dijawab_pada'    => 'datetime:Y-m-d H:i:s',
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
        return $this->belongsTo(User::class, 'id_mitra');
    }
}
