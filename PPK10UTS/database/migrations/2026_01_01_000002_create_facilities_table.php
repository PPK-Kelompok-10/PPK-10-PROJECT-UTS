<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // ruang kelas, aula, laboratorium, alat, lapangan
            $table->string('location');
            $table->unsignedInteger('capacity')->default(0);
            $table->text('description')->nullable();

            // aktif           : bisa direservasi
            // dalam_perbaikan : ditandai oleh Petugas terkait laporan kerusakan (US 12)
            // nonaktif        : dinonaktifkan oleh Admin (US 16)
            $table->enum('status', ['aktif', 'dalam_perbaikan', 'nonaktif'])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
