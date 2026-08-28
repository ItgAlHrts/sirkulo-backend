<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom foto_url sudah ada di migration utama, migrasi ini tidak lagi diperlukan
        // karena kita menggunakan migrate:fresh. Biarkan kosong agar tidak konflik.
    }

    public function down(): void
    {
        //
    }
};
