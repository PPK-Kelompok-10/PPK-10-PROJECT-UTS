<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Membuat akun admin pertama. Diperlukan karena tidak ada jalur
     * registrasi mandiri untuk role admin di sistem ini.
     * Jalankan: php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@ppk2026.test'],
            [
                'name' => 'Admin Fasilitas',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
                'verification_status' => 'verified',
            ]
        );
    }
}
