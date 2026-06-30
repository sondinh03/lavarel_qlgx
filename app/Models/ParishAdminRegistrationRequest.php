<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParishAdminRegistrationRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reference_code',
        'parish_id',
        'status',
        'name',
        'email',
        'phone',
        'password_hash',
        'note',
        'user_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'ip_address',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function statusLabel(): string
    {
        return config('parish-admin-registration.statuses.' . $this->status, $this->status);
    }

    public static function generateReferenceCode(): string
    {
        do {
            $code = 'QTX' . now()->format('ymd') . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }

    public static function emailIsBlocked(string $email): bool
    {
        $normalized = strtolower(trim($email));

        if (User::whereRaw('LOWER(email) = ?', [$normalized])->exists()) {
            return true;
        }

        return self::query()
            ->whereRaw('LOWER(email) = ?', [$normalized])
            ->where('status', self::STATUS_PENDING)
            ->exists();
    }
}
