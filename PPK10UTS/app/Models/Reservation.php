<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'facility_id',
        'purpose',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /**
     * Reservasi yang masih "menahan" slot waktu (dipakai untuk cek ketersediaan di katalog).
     * Yang rejected/cancelled tidak lagi menahan slot.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'approved']);
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * US 4: pembatalan mandiri hanya boleh sebelum batas waktu tertentu
     * (di sini: minimal 2 jam sebelum start_time) dan hanya untuk status pending/approved.
     */
    public function canBeCancelledBy(User $user): bool
    {
        return $this->isOwnedBy($user)
            && in_array($this->status, ['pending', 'approved'], true)
            && now()->lt($this->start_time->copy()->subHours(2));
    }
}
