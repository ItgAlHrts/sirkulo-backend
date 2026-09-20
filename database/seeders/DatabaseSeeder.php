<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Nasabah;
use App\Models\PetugasMitra;
use App\Models\Partner;
use App\Models\TrashCategory;
use App\Models\Education;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Buat / Reset Akun Nasabah ────────────────────────────
        $nasabah = Nasabah::updateOrCreate(
            ['email' => 'itang@gmail.com'],
            [
                'nama'       => 'Itang Al Harits',
                'kata_sandi' => Hash::make('password123'),
                'telepon'    => '082342270844',
                'alamat'     => 'Kedungmundu, Tembalang, Kota Semarang',
                'saldo'      => 55000,
                'poin'       => 550,
                'peran'      => 'NASABAH',
            ]
        );

        // ── 1b. Buat / Reset Akun Super Admin Desa ──────────────────
        $superAdmin = Admin::updateOrCreate(
            ['email' => 'superadmin@sirkulo.id'],
            [
                'nama'       => 'Super Administrator',
                'kata_sandi' => Hash::make('superadmin2026'),
                'telepon'    => '081200001111',
                'peran'      => 'SUPER_ADMIN',
            ]
        );

        // ── 1c. Buat / Reset Akun Admin Operasional Desa ─────────────
        $adminOperasional = Admin::updateOrCreate(
            ['email' => 'admin@sirkulo.id'],
            [
                'nama'       => 'Admin Operasional Desa',
                'kata_sandi' => Hash::make('admindesa2026'),
                'telepon'    => '081299887766',
                'peran'      => 'ADMIN',
            ]
        );

        // ── 2. Reset Akun Dummy & Pos Bank Sampah Resmi Desa ─────────
        PetugasMitra::whereIn('email', ['mitrasirkulo@gmail.com', 'udin@gmail.com', 'mitra2@gmail.com', 'mitra3@gmail.com'])->delete();

        // Buat Akun Petugas Resmi Pos 1 (Bpk. Bambang Sutrisno)
        $petugas1 = PetugasMitra::updateOrCreate(
            ['email' => 'bambang@sirkulo.id'],
            [
                'nama'       => 'Bpk. Bambang Sutrisno',
                'kata_sandi' => Hash::make('petugas2026'),
                'telepon'    => '081234567890',
                'alamat'     => 'Jl. Pahlawan No. 1, Semarang Tengah',
                'saldo'      => 0,
                'peran'      => 'MITRA',
            ]
        );

        Partner::updateOrCreate(
            ['nama' => 'Pos 1 - Bank Sampah Maju Jaya'],
            [
                'id_pengguna' => $petugas1->id,
                'pengelola'   => 'Bpk. Bambang Sutrisno',
                'alamat'      => 'Jl. Pahlawan No. 1, Semarang Tengah',
                'lintang'     => -6.9932,
                'bujur'       => 110.4203,
                'jam_buka'    => 'Senin - Sabtu, 08:00 - 16:00 WIB',
            ]
        );

        Partner::updateOrCreate(
            ['nama' => 'Pos 2 - Bank Sampah Berkah Bersih'],
            [
                'pengelola'   => 'Ibu Siti Rahmawati',
                'alamat'      => 'Jl. Pemuda No. 15, Pandansari',
                'lintang'     => -6.9821,
                'bujur'       => 110.4125,
                'jam_buka'    => 'Senin - Jumat, 08:30 - 15:30 WIB',
            ]
        );

        Partner::updateOrCreate(
            ['nama' => 'Pos 3 - Bank Sampah Asri Sejahtera'],
            [
                'pengelola'   => 'Bpk. Budi Santoso',
                'alamat'      => 'Jl. Pandanaran No. 8, Mugassari',
                'lintang'     => -6.9912,
                'bujur'       => 110.4180,
                'jam_buka'    => 'Setiap Hari, 08:00 - 17:00 WIB',
            ]
        );

        // ── 3. Harga Sampah (Dikelola oleh Mitra) ────────────────────
        $daftarKategori = [
            ['nama' => 'Plastik', 'harga_per_kg' => 3000, 'ikon' => 'ic_plastic'],
            ['nama' => 'Kertas',  'harga_per_kg' => 2000, 'ikon' => 'ic_paper'],
            ['nama' => 'Logam',   'harga_per_kg' => 8000, 'ikon' => 'ic_metal'],
            ['nama' => 'Kaca',    'harga_per_kg' => 1000, 'ikon' => 'ic_glass'],
            ['nama' => 'Botol',   'harga_per_kg' => 2500, 'ikon' => 'ic_bottle'],
            ['nama' => 'Kardus',  'harga_per_kg' => 1500, 'ikon' => 'ic_cardboard'],
        ];
        foreach ($daftarKategori as $k) {
            TrashCategory::updateOrCreate(['nama' => $k['nama']], $k);
        }

        // ── 4. Edukasi Daur Ulang ────────────────────────────────────
        $daftarEdukasi = [
            [
                'judul'      => 'Cara Memilah Sampah yang Benar',
                'kategori'   => 'Tips',
                'konten'     => 'Pilah sampah menjadi organik, anorganik, dan B3 (Bahan Berbahaya). Sampah organik bisa dijadikan kompos, sedangkan anorganik seperti plastik, kertas, dan logam bisa dijual ke bank sampah.',
                'url_gambar' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?auto=format&fit=crop&q=80&w=400&h=200',
            ],
            [
                'judul'      => 'Manfaat Bank Sampah bagi Masyarakat',
                'kategori'   => 'Edukasi',
                'konten'     => 'Bank sampah adalah sistem pengelolaan sampah berbasis masyarakat yang tidak hanya mengurangi limbah, tetapi juga memberikan keuntungan finansial bagi warga yang aktif menyetorkan sampah.',
                'url_gambar' => 'https://images.unsplash.com/photo-1604187351574-c75ca79f5807?auto=format&fit=crop&q=80&w=400&h=200',
            ],
            [
                'judul'      => 'Plastik: Musuh atau Kawan?',
                'kategori'   => 'Lingkungan',
                'konten'     => 'Plastik membutuhkan ratusan tahun untuk terurai di alam. Namun dengan mendaur ulangnya melalui SIRKULO, kita bisa mengubah ancaman menjadi nilai ekonomi sekaligus menjaga kelestarian lingkungan.',
                'url_gambar' => 'https://images.unsplash.com/photo-1611284446314-60a58ac0deb9?auto=format&fit=crop&q=80&w=400&h=200',
            ]
        ];
        foreach ($daftarEdukasi as $edu) {
            Education::updateOrCreate(['judul' => $edu['judul']], $edu);
        }
    }
}
