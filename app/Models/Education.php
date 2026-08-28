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
}
