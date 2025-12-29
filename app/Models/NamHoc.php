<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\This;

class NamHoc extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'nam_hoc';
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'parish_id',
        'start_date_one',
        'end_date_one',
        'start_date_two',
        'end_date_two',
        'status',  //1 hoạt động, 0 lưu trữ
    ];

    // protected $appends = ['display_name'];

    protected $casts = [
        'start_date_one' => 'date',
        'end_date_one'   => 'date',
        'start_date_two' => 'date',
        'end_date_two'   => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    // */
    // public function getDisplayNameAttribute(): string
    // {
    //     $start = $this->start_date_one?->format('Y');
    //     $end  = $this->end_date_two?->format('Y');

    //     // Ưu tiên học kỳ 1
    //     if ($start && $end) {
    //         return "{$start} - {$end}";
    //     }

    //     // Dự phòng nếu chỉ có name
    //     return $this->name ?? 'N/A';
    // }

    public function getCurrentSemesterAttribute(): ?int
    {
        $today = now()->toDateString();

        if (
            $this->start_date_one && $this->end_date_one &&
            $today >= $this->start_date_one->toDateString() &&
            $today <= $this->end_date_one->toDateString()
        ) {
            return 1;
        }

        if (
            $this->start_date_two && $this->end_date_two &&
            $today >= $this->start_date_two->toDateString() &&
            $today <= $this->end_date_two->toDateString()
        ) {
            return 2;
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function parish()
    {
        return $this->belongsTo(Parish::class, 'parish_id', 'id');
    }

    public function lops()
    {
        return $this->hasMany(Lop::class, 'schoolyear');
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    public function scopeArchived($q)
    {
        return $q->where('status', 0);
    }

    public function scopeCurrent($q)
    {
        $today = now()->toDateString();

        return $q->whereDate('start_date_one', '<=', $today)
            ->whereDate('end_date_two', '>=', $today);
    }

    public function scopeOfParish($query, $parishId)
    {
        return $query->where('parish_id', $parishId);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | MUTATORS
    |--------------------------------------------------------------------------
    */
}
