<?php

namespace App\Models;

use App\Traits\BelongsToParish;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use BelongsToParish;
    use CrudTrait;
    use HasFactory;

    protected $table = 'teachers';

    protected $fillable = [
        'parish_id',
        'parish_group_id',
        'user_id',
        'saint_id',
        'last_name',
        'first_name',
        'gender',
        'birthday',
        'phone_number',
        'email',
        'address',
        'avatar_path',
        'is_active',
        'note',
    ];

    protected $casts = [
        'birthday'  => 'date',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(ParishNew::class, 'parish_id');
    }

    public function parishGroup()
    {
        return $this->belongsTo(ParishGroup::class, 'parish_group_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saint()
    {
        return $this->belongsTo(Holymanagement::class, 'saint_id');
    }

    public function classes()
    {
        return $this->belongsToMany(
            CatechismClass::class,
            'class_teachers',
            'teacher_id',
            'class_id'
        )->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }

    public function getFullNameWithSaintAttribute(): string
    {
        $saintName = $this->saint?->name ?? '';
        return trim($saintName . ' ' . $this->full_name);
    }

    public function getGenderTextAttribute(): string
    {
        return match ($this->gender) {
            'male'   => 'Nam',
            'female' => 'Nữ',
            default  => '—',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function openLink(): string
    {
        return '<a target="_blank" href="' . route('catechists.index') . '"><i class="las la-link"></i>Liên kết</a>';
    }
}
