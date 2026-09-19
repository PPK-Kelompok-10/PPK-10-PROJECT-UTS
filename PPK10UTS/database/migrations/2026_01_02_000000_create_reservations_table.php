<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained()->cascadeOnDelete();
            $table->string('purpose');
            $table->dateTime('start_time');
            $table->dateTime('end_time');

            // pending   : baru diajukan, menunggu diproses Petugas (US 8, US 9 - bagian Anggota 3)
            // approved  : disetujui Petugas
            // rejected  : ditolak Petugas
            // cancelled : dibatalkan mandiri oleh Pengguna (US 4) ATAU oleh Petugas dalam kondisi mendesak (US 10)
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');

            $table->timestamps();

            // Cegah pencarian bentrok jadi lebih cepat saat nanti dicek oleh Anggota 3.
            $table->index(['facility_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
