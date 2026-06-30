<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use CrudTrait;
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'parish_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'parish_id'         => 'integer',
    ];

    public function parishId()
    {
        return $this->parish_id;
    }

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id', 'id');
    }

    public function teacher(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isParishAdmin(): bool
    {
        return $this->hasRole('parish_admin');
    }

    public function isCatechist(): bool
    {
        return $this->hasRole('catechist');
    }

    /**
     * Giao diện mobile/bottom-nav chỉ dành cho GLV thuần (không phải quản trị xứ).
     */
    public function usesCatechistLayout(): bool
    {
        return $this->isCatechist() && ! $this->canManage();
    }

    public function canManage(): bool
    {
        return $this->hasAnyRole(['super_admin', 'parish_admin']);
    }

    public function parishName(): ?string
    {
        return $this->parish?->name ?? null;
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function setPasswordAttribute($value): void
    {
        if (!empty($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    protected static function booted(): void
    {
        static::deleting(function (User $user) {
            Teacher::where('user_id', $user->id)->update(['user_id' => null]);
        });

        static::saved(function ($user) {
            $request = request();
            if ($request->filled('roles')) {
                $user->syncRoles([$request->roles]);
            }
        });
    }
}
