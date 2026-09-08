<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // Role: admin, petugas, pengguna
            // Pengunjung TIDAK punya akun (tidak login), jadi tidak masuk enum ini.
            $table->enum('role', ['admin', 'petugas', 'pengguna'])->default('pengguna');

            // Status verifikasi khusus untuk akun 'pengguna' hasil registrasi mandiri.
            // - pending  : baru daftar sendiri, menunggu verifikasi admin
            // - verified : sudah diverifikasi admin, boleh login
            // - rejected : ditolak admin
            // Akun yang didaftarkan LANGSUNG oleh admin (petugas & pengguna) otomatis 'verified'.
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('verified');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
