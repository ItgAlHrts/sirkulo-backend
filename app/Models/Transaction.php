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
        'jenis_sampah', 'berat_kg', 'harga_per_kg', 'metode_pembayaran',
        'catatan', 'saldo_sebelumnya', 'saldo_baru',
    ];

    protected $casts = [
        'jumlah_total'     => 'integer',
        'poin_didapat'     => 'integer',
        'berat_kg'         => 'float',
        'harga_per_kg'     => 'integer',
        'saldo_sebelumnya' => 'integer',
        'saldo_baru'       => 'integer',
        'dibuat_pada'      => 'datetime:Y-m-d H:i:s',
        'diperbarui_pada'  => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'jenis_sampah',
        'berat_kg',
        'harga_per_kg',
        'metode_pembayaran',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return \Carbon\Carbon::instance($date)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

    // ── Accessors dengan fallback cerdas untuk transaksi lama ──
    public function getJenisSampahAttribute($value)
    {
        if (!empty($value)) return $value;
        if ($this->jenis === 'SETORAN' && !empty($this->keterangan)) {
            // Misal: "Botol Plastik 2.5 kg" atau "Botol Plastik 2.5 kg (Setor Tunai)"
            if (preg_match('/^([^0-9]+)/u', $this->keterangan, $m)) {
                $nama = trim($m[1]);
                if (!empty($nama) && stripos($nama, 'Tarik Tunai') === false) {
                    return $nama;
                }
            }
        }
        return $this->jenis === 'SETORAN' ? 'Sampah Daur Ulang' : null;
    }

    public function getBeratKgAttribute($value)
    {
        if (!is_null($value)) return (float) $value;
        if (!empty($this->keterangan)) {
            if (preg_match('/([0-9]+(?:\.[0-9]+)?)\s*kg/i', $this->keterangan, $m)) {
                return (float) $m[1];
            }
        }
        return null;
    }

    public function getHargaPerKgAttribute($value)
    {
        if (!is_null($value)) return (int) $value;
        $berat = $this->berat_kg;
        if ($berat && $berat > 0 && $this->jumlah_total > 0) {
            return (int) round($this->jumlah_total / $berat);
        }
        return null;
    }

    public function getMetodePembayaranAttribute($value)
    {
        if (!empty($value)) return $value;
        if (!empty($this->keterangan) && (stripos($this->keterangan, 'Tunai') !== false || stripos($this->keterangan, 'Setor Tunai') !== false)) {
            return 'TUNAI_CASH';
        }
        return 'SALDO';
    }

    public function pengguna()
    {
        return $this->belongsTo(Nasabah::class, 'id_pengguna');
    }

    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'id_pengguna');
    }

    public function mitra()
    {
        return $this->belongsTo(Partner::class, 'id_mitra');
    }

    // Alias untuk kompatibilitas: partner() = mitra()
    public function partner()
    {
        return $this->belongsTo(Partner::class, 'id_mitra');
    }
}
