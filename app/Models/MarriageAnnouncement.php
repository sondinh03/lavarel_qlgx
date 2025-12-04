<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Venturecraft\Revisionable\RevisionableTrait;


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;

use Backpack\Settings\app\Models\Setting;

//Class \"App\\Models\\Config\" not found 

class MarriageAnnouncement extends Model
{
    use CrudTrait;
    use RevisionableTrait;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'marriage_announcements';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected $appends = [
        'female_diocese',
        'female_deanerys',
        'female_parishmanagements',
        'female_parishs',
        'female',
        'male_diocese',
        'male_deanerys',
        'male_parishmanagements',
        'male_parishs',
        'male',
        'female_dioceseold',
        'female_deaneryold',
        'female_parishmanagementsold',
        'female_parishsold',
        'female_diocesebefore',
        'female_deanerys',
        'female_deanerybefore',
        'female_parishmanagementsbefore',
        'female_parishsbefore',
        'male_dioceseold',
        'male_deaneryold',
        'male_deanerys',
        'male_parishmanagementsold',
        'male_parishsold',
        'male_diocesebefore',
        'male_deanerybefore',
        'male_parishmanagementsbefore',
        'male_parishsbefore',
    ];
    
    protected $with = ['slug'];

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

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */    
    public function getFemaleDioceseAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->dioceses;
        }
    }
    
    public function getFemaleDeaneryAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->deanerys;
        }
    }
    
    public function getFemaleParishmanagementsAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->parishmanagements;
        }
    }
    
    public function getFemaleParishsAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishs;
        }
    }
    
    public function getFemaleAttribute()
    {
        $id = request()->route('id');
        
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->select('parishioners.id', 'parishioners.name')
        ->Join('parishioners', 'marriage_announcements_parishioners.idgiaodan', '=', 'parishioners.id')
        ->where('marriage_announcements_parishioners.idannouncement', '=', $id)
        ->where('marriage_announcements_parishioners.sex', '=', 0)
        ->where('parishioners.status', '=', 1)
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->id;
        }
        
    }
    
    public function getMaleDioceseAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->dioceses;
        }
    }
    
    public function getMaleDeaneryAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->deanerys;
        }
    }
    
    public function getMaleParishmanagementsAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->parishmanagements;
        }
    }
    
    public function getMaleParishsAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishs;
        }
    }
    
    public function getMaleAttribute()
    {
        $id = request()->route('id');
        
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->select('parishioners.id', 'parishioners.name')
        ->Join('parishioners', 'marriage_announcements_parishioners.idgiaodan', '=', 'parishioners.id')
        ->where('marriage_announcements_parishioners.idannouncement', '=', $id)
        ->where('marriage_announcements_parishioners.sex', '=', 1)
        ->where('parishioners.status', '=', 1)
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->id;
        }
        
    }
    
    //female_parish_management
    public function getFemaleDioceseoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->diocesesold;
        }
    }
    public function getFemaleDeaneryoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->deanerysold;
        }
    }
    public function getFemaleParishmanagementsoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishmanagementsold;
        }
    }
    public function getFemaleParishsoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishsold;
        }
    }
    public function getFemaleDiocesebeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->diocesesbefore;
        }
    }
    
    public function getFemaleDeanerysAttribute(){
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->deanerys;
        }else{
            return '';
        }
    }
    
    public function getFemaleDeanerybeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->deanerysbefore;
        }
    }
    public function getFemaleParishmanagementsbeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishmanagementsbefore;
        }
    }
    public function getFemaleParishsbeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishsbefore;
        }
    }
    public function getMaleDioceseoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->diocesesold;
        }
    }
    public function getMaleDeaneryoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->deanerysold;
        }
    }
    public function getMaleParishmanagementsoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishmanagementsold;
        }
    }
    public function getMaleParishsoldAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishsold;
        }
    }
    public function getMaleDiocesebeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->diocesesbefore;
        }
    }
    public function getMaleDeanerybeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->deanerysbefore;
        }
    }
    public function getMaleDeanerysAttribute(){
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 0)
        ->orderBy('id', 'ASC')
        ->first();
        
        if(!empty($parishioners)){
            return $parishioners->deanerys;
        }else{
            return '';
        }
    }
    public function getMaleParishmanagementsbeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishmanagementsbefore;
        }
    }
    public function getMaleParishsbeforeAttribute()
    {
        $id = request()->route('id');
        $parishioners = DB::table('marriage_announcements_parishioners')
        ->where('idannouncement', $id)
        ->where('sex', 1)
        ->orderBy('id', 'ASC')
        ->first();
        if(!empty($parishioners)){
            return $parishioners->parishsbefore;
        }
    }
    

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
