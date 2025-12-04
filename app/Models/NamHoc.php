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

    // protected $appends = ['schoolyear', 'namhoc', 'display_name'];
    protected $appends = ['display_name'];

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
    */
    public function getDisplayNameAttribute(): string
    {
        $start = $this->start_date_one?->format('Y');
        $end  = $this->end_date_two?->format('Y');

        // Ưu tiên học kỳ 1
        if ($start && $end) {
            return "{$start} - {$end}";
        }

        // Dự phòng nếu chỉ có name
        return $this->name ?? 'N/A';
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

    public function scopeCurrent($q)
    {
        $today = now()->toDateString();

        return $q->whereDate('start_date_one', '<=', $today)
                 ->whereDate('end_date_two', '>=', $today);
    }

    public function scopeOfParish($query, $parishId)
    {
        return $query->where('parish_id', $parishId)
            ->where('status', 1)->orderBy('name');
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
