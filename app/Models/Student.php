<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use App\Models\StudentTarget;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Student extends Model
{
    use CrudTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'student';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];

    //protected $appends = ['lop'];
    protected $appends = ['holy_name'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function openLink(): string
    {
        $slug = slug($this) . config('settings.url_prefix');

        return '<a target="_blank" href="' . url($slug) . '"><i class="las la-link"></i>Liên kết</a>';
    }

    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }


    public function lop(): MorphToMany
    {
        return $this->morphToMany(StudentTarget::class, 'target', 'student_target');
    }

    public function lops()
    {
        return $this->belongsToMany(Lop::class, 'students_class', 'student_id', 'class_id')
            ->using(StudentsClass::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function getBirthdayAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value)->format('d-m-Y') : '-';
    }

    public function getHolyNameAttribute()
    {
        return $this->holyRelation->name ?? null;
    }

    public function holyRelation()
    {
        return $this->belongsTo(Holymanagement::class, 'holy');
    }

    // public function schoolYearRelation()
    // {
    //     return $this->belongsTo(NamHoc::class, 'schoolyear');
    // }

    public function paidRelation()
    {
        return $this->belongsTo(Parish::class, 'paid');
    }
}
