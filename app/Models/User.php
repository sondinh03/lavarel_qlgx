<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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
    ];

    public function parish(): BelongsTo
    {
        return $this->belongsTo(ParishNew::class, 'parish_id', 'id');
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

    public function canManage(): bool
    {
        // return $this->isSuperAdmin() || $this->isParishAdmin();
        return $this->hasAnyRole(['super_admin', 'parish_admin']);
    }

    /*
    public function admin()
    {
        return $this->hasOne(SetAdmin::class, 'use', 'id');
    }

    public function decen()
    {
        return $this->hasOne(Decen::class, 'use', 'id');
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'id');
    }

    public function isAdmin(): bool
    {
        return $this->admin && $this->admin->status === 1;
    }



    // public function isCatechist(): bool
    // {
    //     return $this->teacher && $this->teacher->status === 1;
    // }

    */

    public function isDecen(): bool
    {
        return $this->decen && $this->decen->status === 1;
    }

    public function parishName(): ?string
    {
        return $this->parish?->name ?? null;
    }
}
