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
        'id_pengguna', 'judul', 'deskripsi', 'isi', 'jenis', 'sudah_dibaca',
    ];

    protected $casts = [
        'dibuat_pada'     => 'datetime:Y-m-d H:i:s',
        'diperbarui_pada' => 'datetime:Y-m-d H:i:s',
        'sudah_dibaca'    => 'boolean',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return \Carbon\Carbon::instance($date)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    public function getDeskripsiAttribute($value)
    {
        return $value ?: ($this->attributes['isi'] ?? '');
    }

    public function getIsiAttribute($value)
    {
        return $value ?: ($this->attributes['deskripsi'] ?? '');
    }

    public function setDeskripsiAttribute($value)
    {
        $this->attributes['deskripsi'] = $value;
        if (!isset($this->attributes['isi']) || empty($this->attributes['isi'])) {
            $this->attributes['isi'] = $value;
        }
    }

    public function setIsiAttribute($value)
    {
        $this->attributes['isi'] = $value;
        if (!isset($this->attributes['deskripsi']) || empty($this->attributes['deskripsi'])) {
            $this->attributes['deskripsi'] = $value;
        }
    }

    /**
     * Relasi ke pemilik notif — bisa nasabah ATAU mitra.
     * Kita pakai morph-less pattern: cukup simpan id_pengguna
     * dan biarkan controller menentukan konteksnya.
     */
    public function pengguna()
    {
        return $this->belongsTo(Nasabah::class, 'id_pengguna');
    }
}
