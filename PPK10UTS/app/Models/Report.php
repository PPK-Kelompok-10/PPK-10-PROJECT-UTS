<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /** Kategori laporan: key disimpan di DB, value untuk tampilan. */
    public const CATEGORIES = [
        'kerusakan_fisik' => 'Kerusakan fisik',
        'kelistrikan' => 'Kelistrikan & pencahayaan',
        'peralatan' => 'Peralatan & perangkat',
        'kebersihan' => 'Kebersihan',
        'gangguan' => 'Gangguan & keamanan',
        'lainnya' => 'Lainnya',
    ];

    public const STATUSES = [
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    /** Laporan yang masih perlu ditangani Petugas. */
    public const OPEN_STATUSES = ['baru', 'diproses'];

    /** Status akhir: wajib catatan resolusi dan tidak bisa diubah lagi. */
    public const CLOSED_STATUSES = ['selesai', 'ditolak'];

    /**
     * US 11: alur status yang diizinkan. Laporan yang sudah selesai/ditolak bersifat final,
     * kalau masalahnya muncul lagi Pengguna cukup membuat laporan baru.
     */
    public const TRANSITIONS = [
        'baru' => ['diproses', 'selesai', 'ditolak'],
        'diproses' => ['selesai', 'ditolak'],
        'selesai' => [],
        'ditolak' => [],
    ];

    /** Fasilitas yang boleh dilaporkan. Yang nonaktif (dimatikan Admin, US 16) tidak ditampilkan. */
    public const REPORTABLE_FACILITY_STATUSES = ['aktif', 'dalam_perbaikan'];

    /** Ketentuan upload foto bukti, dipakai di validasi, tampilan, dan test. */
    public const PHOTO_DISK = 'local';
    public const PHOTO_DIRECTORY = 'reports';
    public const PHOTO_MIMES = ['jpg', 'jpeg', 'png', 'webp'];
    public const PHOTO_MAX_KB = 2048;

    protected $fillable = [
        'user_id',
        'facility_id',
        'category',
        'description',
        'photo_path',
        'status',
        'resolution_note',
        'handled_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    /** Petugas yang terakhir menangani laporan. */
    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', self::OPEN_STATUSES);
    }

    public function isOwnedBy(User $user): bool
    {
        return (int) $this->user_id === (int) $user->id;
    }

    /** Foto bukti hanya boleh dilihat pelapor, Petugas, dan Admin. */
    public function canBeViewedBy(User $user): bool
    {
        return $this->isOwnedBy($user) || $user->isPetugas() || $user->isAdmin();
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::OPEN_STATUSES, true);
    }

    public function isClosed(): bool
    {
        return in_array($this->status, self::CLOSED_STATUSES, true);
    }

    public function canTransitionTo(?string $status): bool
    {
        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    /** Pilihan status berikutnya untuk dropdown Petugas, format [value => label]. */
    public function allowedNextStatuses(): array
    {
        return array_intersect_key(self::STATUSES, array_flip(self::TRANSITIONS[$this->status] ?? []));
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
