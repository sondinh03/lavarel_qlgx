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

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $student) {
            if (empty($student->student_code)) {
                $student->student_code = static::generateCode($student->parish_id);
            }

            if (empty($student->qr_token)) {
                $student->qr_token = (string) \Illuminate\Support\Str::uuid();
            }
        });

        static::updating(function (self $student) {
            if (empty($student->student_code)) {
                $student->student_code = static::generateCode($student->parish_id);
            }

            if (empty($student->qr_token)) {
                $student->qr_token = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    private static function generateCode($parishId): string
    {
        $parishCode = ParishNew::find($parishId)?->code ?? 'GXU';

        $year = substr(now()->year, -2); // 2025 → "25"
        $prefix = "{$parishCode}-{$year}-";

        $last = static::where('parish_id', $parishId)
            ->where('student_code', 'like', "{$prefix}%")
            ->max('student_code'); // VD: "HDO-25-0012"

        $lastNumber = $last
            ? (int) substr($last, strlen($prefix)) // "0012" → 12
            : 0;

        $sequence = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);

        return "{$prefix}{$sequence}";
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(ParishNew::class);
    }

    public function parishioner()
    {
        return $this->belongsTo(Parishioner::class);
    }

    public function parishGroup()
    {
        return $this->belongsTo(ParishGroup::class);
    }

    public function saint()
    {
        return $this->belongsTo(Holymanagement::class);
    }

    public function classes()
    {
        return $this->belongsToMany(
            \App\Models\CatechismClass::class,
            'students_class',
            'student_id',
            'class_id'
        )->withTimestamps();
    }

    public function studentsClass()
    {
        return $this->hasMany(StudentsClass::class, 'student_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getGenderTextAttribute()
    {
        return match ($this->gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            null => '—',
            default => '—',
        };
    }

    public function getSaintNameAttribute(): string
    {
        return $this->saint?->name ?? '-';
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name . ' ' . $this->first_name);
    }

    public function getFullNameWithSaintAttribute(): string
    {
        $saintName = $this->saint?->name ?? '';

        return trim($saintName . ' ' . $this->full_name);
    }
}
