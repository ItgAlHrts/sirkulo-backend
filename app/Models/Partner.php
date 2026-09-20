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
        'id_pengguna', 'nama', 'pengelola', 'alamat', 'lintang', 'bujur', 'jam_buka',
    ];

    protected $appends = ['kode_pos', 'pengelola', 'kategori_sampah'];

    /**
     * Nama penanggung jawab / pengelola pos loket.
     * Mengutamakan nama pengelola yang tersimpan, atau nama petugas mitra yang ditugaskan.
     */
    public function getPengelolaAttribute($value)
    {
        // 1. Jika kolom database pengelola diisi dan bukan nama placeholder generik
        if (!empty($value) && !in_array(trim($value), ['Mitra SIRKULO', 'MITRA SIRKULO', 'Mitra'])) {
            return $value;
        }

        // 2. Ambil nama dari akun petugas mitra jika bukan akun generik
        $petugasNama = $this->petugas?->nama ?? $this->pengguna?->nama;
        if (!empty($petugasNama) && !in_array(trim($petugasNama), ['Mitra SIRKULO', 'MITRA SIRKULO', 'Mitra'])) {
            return $petugasNama;
        }

        // 3. Fallback rapi dan profesional
        return (!empty($value)) ? $value : ('Pengelola ' . $this->nama);
    }

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

    /**
     * Jenis-jenis sampah yang diterima khusus di pos loket ini.
     */
    public function kategoriSampah()
    {
        return $this->belongsToMany(
            TrashCategory::class,
            'pos_kategori_sampah',
            'id_mitra',
            'id_kategori'
        )->orderBy('nama');
    }

    /**
     * Accessor untuk kategori sampah yang diterima pos ini.
     */
    public function getKategoriSampahAttribute()
    {
        if ($this->relationLoaded('kategoriSampah')) {
            $list = $this->getRelation('kategoriSampah');
            if ($list !== null) {
                return $list;
            }
        }

        return $this->kategoriSampah()->get();
    }

    public function pengguna()
    {
        return $this->belongsTo(PetugasMitra::class, 'id_pengguna');
    }

    public function petugas()
    {
        return $this->belongsTo(PetugasMitra::class, 'id_pengguna');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaction::class, 'id_mitra');
    }
}
