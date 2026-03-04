<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\BelongsToParish;

class StudentNew extends Model
{
    use HasFactory, BelongsToParish;

    protected $table = 'students';

    protected $fillable = [
        'student_code',
        'qr_token',
        'avatar_path',
        'parishioner_id',
        'parish_id',
        'parish_group_id',
        'saint_id',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'birthday',
        'gender',
        'phone',
        'email',
        'is_active',
        'note',
    ];

    protected $casts = [
        'birthday' => 'date',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioners::class);
    }

    public function parishGroup()
    {
        return $this->belongsTo(ParishGroup::class);
    }

    public function saint()
    {
        return $this->belongsTo(Holymanagement::class);
    }

    public function students()
    {
        return $this->belongsToMany(
            StudentNew::class,
            'students_class',
            'class_id',
            'student_id'
        )
            ->withTimestamps();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }
}
