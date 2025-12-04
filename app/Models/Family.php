<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Venturecraft\Revisionable\RevisionableTrait;

class Family extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'family';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'pid',
        'deid',
        'did',
        'paid',
        'name',
        'mother',
        'father', 
        'household', 
        'dien',
        'songuoi',
        'phone',
        'origin', 
        'ward',
        'province', 
        'noio',
        'thongke', 
        'note', 
        'image', 
        'status', 
        'created_at', 
        'updated_at'
    ];
    // protected $hidden = [];
    protected $dates = ['created_at', 'updated_at'];

    //protected $appends = ['date'];
    protected $appends = [
        'date',
        'sohonphoi',
        'marriage_address',
        'marriage_ward',
        'marriage_province',
        'priest',
        'peopleone',
        'peopletwo',
        'tinhtrang',
        'marriage_note',
    ];
    
    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    public function openLink(): string
    {
        $slug = slug($this).config('settings.url_prefix');
        
        return '<a target="_blank" href="'.url($slug).'"><i class="las la-link"></i>Liên kết</a>';
    }
    
    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }
    
    public function children(): MorphToMany
    {
        return $this->morphToMany(Children::class, 'childrengable');
    }
    
    public function getDateAttribute()
    {
        $id = request()->route('id');
        $date = DB::table('marriage')
            ->select('date')
            ->where('idfamily', $id)
            ->orderBy('id', 'ASC')
            ->first();
        if(!empty($date)){
            return $date->date;
        }
    }
    
    public function getSohonphoiAttribute()
    {
        $id = request()->route('id');
        $sohonphoi = DB::table('marriage')
        ->select('sohonphoi')
        ->where('idfamily', $id)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($sohonphoi)){
            return $sohonphoi->sohonphoi;
        }
    }
    
    public function getMarriageAddressAttribute()
    {
        $id = request()->route('id');
        $m_address = DB::table('marriage')
            ->select('marriage_address')
            ->where('idfamily', $id)
            ->orderBy('id', 'ASC')
            ->first();
        if(!empty($m_address)){
            return $m_address->marriage_address;
        }
    }
    
    public function getMarriageWardAttribute()
    {
        $id = request()->route('id');
        $m_ward = DB::table('marriage')
            ->select('marriage_ward')
            ->where('idfamily', $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->first();
        
        if(!empty($m_ward)){
            return $m_ward->marriage_ward;
        }
    }  
    
    public function getMarriageProvinceAttribute()
    {
        $id = request()->route('id');
        $m_province = DB::table('marriage')
            ->select('marriage_province')
            ->where('idfamily', $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->first();
        if(!empty($m_province)){
            return $m_province->marriage_province;
        }
    }  
    
    public function getPriestAttribute()
    {
        $id = request()->route('id');
        $m_priest = DB::table('marriage')
            ->select('priest')
            ->where('idfamily', $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->first();
        if(!empty($m_priest)){
            return $m_priest->priest;
        }
    }
    
    public function getPeopleoneAttribute()
    {
        $id = request()->route('id');
        $m_peopleone = DB::table('marriage')
        ->select('peopleone')
        ->where('idfamily', $id)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($m_peopleone)){
            return $m_peopleone->peopleone;
        }
    }
    
    public function getPeopletwoAttribute()
    {
        $id = request()->route('id');
        $m_peopletwo = DB::table('marriage')
        ->select('peopletwo')
        ->where('idfamily', $id)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($m_peopletwo)){
            return $m_peopletwo->peopletwo;
        }
    }
    
    public function getTinhtrangAttribute()
    {
        $id = request()->route('id');
        $m_tinhtrang = DB::table('marriage')
        ->select('tinhtrang')
        ->where('idfamily', $id)
        ->orderBy('id', 'ASC')
        ->first();  
        if(!empty($m_tinhtrang)){
            return $m_tinhtrang->tinhtrang;
        }
    }
    public function getMarriageNoteAttribute()
    {
        $id = request()->route('id');
        $m_note = DB::table('marriage')
        ->select('marriage_note')
        ->where('idfamily', $id)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($m_note)){
            return $m_note->marriage_note;
        }
    }
    
    
    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

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
