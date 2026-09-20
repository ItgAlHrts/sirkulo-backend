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
        'judul', 'kategori', 'konten', 'url_gambar', 'url_video',
    ];

    protected $appends = [
        'youtube_id', 'has_video',
    ];

    /**
     * URL gambar sampul artikel.
     */
    public function getUrlGambarAttribute($value): string
    {
        // Jika artikel memiliki video YouTube, gunakan thumbnail video YouTube sebagai foto sampul
        if ($this->youtube_id) {
            return "https://img.youtube.com/vi/{$this->youtube_id}/hqdefault.jpg";
        }

        if (empty($value)) {
            return 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=600&auto=format&fit=crop&q=60';
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            if (preg_match('#^https?://(localhost|127\.0\.0\.1|192\.168\.\d+\.\d+|10\.\d+\.\d+\.\d+|172\.(1[6-9]|2\d|3[01])\.\d+\.\d+)(:\d+)?(/.*)?$#i', $value, $matches)) {
                $path = $matches[3] ?? '';
                return request()->getSchemeAndHttpHost() . $path;
            }
            return $value;
        }

        $cleanPath = ltrim($value, '/');
        if (!str_starts_with($cleanPath, 'storage/')) {
            $cleanPath = 'storage/' . $cleanPath;
        }

        return request()->getSchemeAndHttpHost() . '/' . $cleanPath;
    }

    /**
     * Ekstrak YouTube Video ID dari berbagai format URL YouTube.
     * Mendukung: youtu.be/xxx, youtube.com/watch?v=xxx, youtube.com/shorts/xxx, youtube.com/embed/xxx
     */
    public function getYoutubeIdAttribute(): ?string
    {
        $url = $this->attributes['url_video'] ?? null;
        if (empty($url)) return null;

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Apakah artikel ini memiliki video YouTube yang valid.
     */
    public function getHasVideoAttribute(): bool
    {
        return !empty($this->youtube_id);
    }
}
