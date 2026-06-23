<?php

namespace App\Models;

use App\Support\FamilyCodeGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParishionerRegistrationRequest extends Model
{
    public const STATUS_PENDING  = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'reference_code',
        'parish_id',
        'status',
        'submitted_name',
        'submitted_phone',
        'payload',
        'sacraments',
        'marriages',
        'avatar_path',
        'parishioner_id',
        'family_id',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'admin_note',
        'ip_address',
    ];

    protected $casts = [
        'payload'     => 'array',
        'sacraments'  => 'array',
        'marriages'   => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function parishioner(): BelongsTo
    {
        return $this->belongsTo(Parishioner::class, 'parishioner_id');
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class, 'family_id');
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

    public function statusLabel(): string
    {
        return config('parishioner-registration.statuses.' . $this->status, $this->status);
    }

    public function familyRoleLabel(): ?string
    {
        $role = $this->payload['family_role'] ?? null;

        return $role
            ? config('parishioner-registration.family_roles.' . $role, $role)
            : null;
    }

    public static function generateReferenceCode(): string
    {
        return FamilyCodeGenerator::generate();
    }
}
