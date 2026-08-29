<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Education extends Model
{
    use HasUuids;

    protected $table = 'edukasi';

    const CREATED_AT = 'dibuat_pada';
    const UPDATED_AT = 'diperbarui_pada';

    protected $fillable = [
        'judul', 'kategori', 'konten', 'url_gambar',
    ];

    public function getUrlGambarAttribute($value): string
    {
        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600&auto=format&fit=crop&q=60';
        }

        // Jika sudah berupa URL eksternal selain localhost (misal unsplash)
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?(/.*)?$#i', $value, $matches)) {
                $path = $matches[3] ?? '';
                return request()->getSchemeAndHttpHost() . $path;
            }
            return $value;
        }

        // Jika path relatif (misal 'storage/edukasi/xxx.jpg' atau 'edukasi/xxx.jpg')
        $cleanPath = ltrim($value, '/');
        if (!str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = 'storage/' . $cleanPath;
        }

        return request()->getSchemeAndHttpHost() . '/' . $cleanPath;
    }
}
