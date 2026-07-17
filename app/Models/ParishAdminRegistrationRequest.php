<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParishAdminRegistrationRequest extends Model
{
    use CrudTrait;

    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reference_code',
        'parish_id',
        'diocese_id',
        'deanery_id',
        'custom_parish_name',
        'requested_parish_groups',
        'status',
        'name',
        'email',
        'phone',
        'password_hash',
        'note',
        'requested_roles',
        'user_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'ip_address',
    ];

    protected $casts = [
        'reviewed_at'              => 'datetime',
        'requested_roles'          => 'array',
        'requested_parish_groups'  => 'array',
    ];

    protected $hidden = [
        'password_hash',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function diocese(): BelongsTo
    {
        return $this->belongsTo(Diocese::class, 'diocese_id');
    }

    public function deanery(): BelongsTo
    {
        return $this->belongsTo(Deanery::class, 'deanery_id');
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

    public function parishDisplayName(): string
    {
        if ($this->parish) {
            return $this->parish->name;
        }

        return $this->custom_parish_name
            ? $this->custom_parish_name . ' (mới)'
            : '—';
    }

    /** Đăng ký cần tạo giáo xứ mới (chưa có parish_id). */
    public function createsNewParish(): bool
    {
        return $this->parish_id === null
            && trim((string) $this->custom_parish_name) !== '';
    }

    /** @return list<string> */
    public function requestedParishGroupNames(): array
    {
        return collect($this->requested_parish_groups ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => mb_strtolower($name))
            ->values()
            ->all();
    }

    public function parishGroupNamesLabel(): string
    {
        $names = $this->requestedParishGroupNames();

        return $names === [] ? '—' : implode(', ', $names);
    }

    public function requestedRoleLabels(): array
    {
        $catalog = config('parish-admin-registration.roles', []);

        return collect($this->requested_roles ?? [])
            ->map(fn ($role) => $catalog[$role]['label'] ?? $role)
            ->values()
            ->all();
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
