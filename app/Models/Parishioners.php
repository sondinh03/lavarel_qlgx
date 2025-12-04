<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Venturecraft\Revisionable\RevisionableTrait;

class Parishioners extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'parishioners';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [
        'id',
        'last_name',
        'name', 
        'pid', 
        'deid', 
        'did', 
        'paid', 
        'assid',
        'origin', 
        'ward', 
        'province', 
        'residence',
        'resi_ward',
        'resi_province',    
        'professional_level',
        'study',
        'new_convert',
        'married',
        'statistical',
        'note',
        'baptism_date',
        'baptism_number',
        'baptism_giver',
        'baptism_sponsor',
        'baptism_dioceses',
        'baptism_deanerys',
        'baptism_parish',
        'more_power_date',
        'more_power_number',
        'more_power_giver',
        'more_power_sponsor',        
        'more_power_dioceses',
        'more_power_deanerys',
        'more_power_parish',
        'communion_date',
        'communion_number',
        'communion_giver',
        'communion_dioceses',
        'communion_deanerys',
        'communion_parish',
        'anoint_date',
        'anoint_status',
        'anoint_giver',
        'anoint_note',
        'die_status',
        'die_time',
        'die_lottery',
        'die_death',
        'die_burial',
        'phone',
        'email',
        'image',
        'father',
        'mother',
        'sex',
        'birthday',
        'cccd',
        'holy',
        'ethnic',
        'career',
        'level',
        'position',
        'language',
        'status', 
        'created_at',
        'updated_at'        
    ];
    // protected $hidden = [];
    protected $dates = ['created_at', 'updated_at'];
    
    
    protected $appends = ['chon_student'];
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

    public function getChonStudentAttribute()
    {
        $holy = DB::table('holymanagements')
            ->where('id', $this->holy)
            ->orderBy('id', 'ASC')
            ->first();
            
        if(!empty($holy->name)){
            $holyname = $holy->name;
        }else{
            $holyname = '';
        }        
        if($this->id){
            $date = new \DateTime($this->birthday);
            $now = new \DateTime();
            $interval = $now->diff($date);
            $age = $interval->y;
            
            $this->attributes['name'] = $holyname . ' ' . $this->last_name . ' ' . $this->name . ' - ' . $age . ' tuổi';
            return $this->attributes['name']; //some logic to return numbers
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
