<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    use HasUuids;

    protected $table = 'mitra';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'id_pengguna', 'nama', 'alamat', 'lintang', 'bujur', 'jam_buka',
    ];

    protected $appends = ['kode_pos'];

    /**
     * Kode Pos unik & berbeda untuk tiap pos bank sampah (misal POS-001, POS-002, dst).
     */
    public function getKodePosAttribute()
    {
        // 1. Ekstrak nomor dari nama pos jika ada, contoh "Pos 1 - ..." -> "POS-001"
        if (preg_match('/pos\s*(\d+)/i', $this->nama, $matches)) {
            return 'POS-' . sprintf('%03d', (int) $matches[1]);
        }

        // 2. Jika tidak ada angka di nama, buat kode unik dari substring UUID id
        $cleanId = strtoupper(str_replace('-', '', $this->id));
        return 'POS-' . substr($cleanId, 0, 4);
    }

    public function pengguna()
    {
        return $this->belongsTo(User::class, 'id_pengguna');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaction::class, 'id_mitra');
    }
}
