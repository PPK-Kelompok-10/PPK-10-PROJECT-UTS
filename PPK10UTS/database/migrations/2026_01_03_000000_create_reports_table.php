<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();

            // Pelapor (role pengguna) & fasilitas yang dilaporkan (US 6)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();

            // Daftar kategori ada di App\Models\Report::CATEGORIES dan divalidasi di StoreReportRequest.
            // Sengaja pakai string (bukan enum) supaya kategori bisa ditambah tanpa migration baru.
            $table->string('category', 50);
            $table->text('description');

            // Path foto bukti di disk privat ('local' => storage/app/private).
            // Wajib saat diajukan lewat form, tapi nullable supaya data seeder/dummy
            // (bagian Anggota 3) tidak harus menyertakan file foto.
            $table->string('photo_path')->nullable();

            // baru     : baru diajukan Pengguna
            // diproses : sedang ditangani Petugas (US 11)
            // selesai  : sudah ditangani, wajib ada catatan resolusi
            // ditolak  : laporan tidak valid / bukan kerusakan, wajib ada catatan resolusi
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->text('resolution_note')->nullable();

            // Petugas terakhir yang menangani laporan
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // Mempercepat antrean Petugas (filter status) & rekap per fasilitas (US 17)
            $table->index(['status', 'created_at']);
            $table->index(['facility_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
