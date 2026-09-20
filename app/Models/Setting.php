<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $table = 'pengaturan_sistem';

    protected $fillable = [
        'kunci',
        'nilai',
        'keterangan',
    ];

    /**
     * Ambil nilai pengaturan berdasarkan kunci.
     */
    public static function get(string $kunci, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$kunci}", 3600, function () use ($kunci, $default) {
            try {
                $setting = static::where('kunci', $kunci)->first();
                return $setting ? $setting->nilai : $default;
            } catch (\Throwable $e) {
                return $default;
            }
        });
    }

    /**
     * Simpan atau perbarui nilai pengaturan.
     */
    public static function set(string $kunci, mixed $nilai, ?string $keterangan = null): self
    {
        Cache::forget("setting_{$kunci}");

        $data = ['nilai' => $nilai];
        if ($keterangan !== null) {
            $data['keterangan'] = $keterangan;
        }

        return static::updateOrCreate(
            ['kunci' => $kunci],
            $data
        );
    }

    /**
     * Ambil seluruh data pengaturan sebagai associative array [kunci => nilai].
     */
    public static function getAll(): array
    {
        try {
            return static::pluck('nilai', 'kunci')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }
}
