<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Venturecraft\Revisionable\RevisionableTrait;

class Page extends Model
{
    use CrudTrait;
    use RevisionableTrait;
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | GLOBAL VARIABLES
    |--------------------------------------------------------------------------
    */

    protected $table = 'pages';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = ['template', 'name', 'content', 'benefits_advantages', 'registration_steps', 'plan_loan_option', 'customer_story', 'extras', 'created_at', 'contact_information'];
    
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    protected array $fakeColumns = ['extras'];
    
    protected $casts = [
        'extras' => 'object',
    ];
    
    protected bool $revisionCleanup = true;
    
    protected int $historyLimit = 100;
    
    protected $dontKeepRevisionOf = ['deleted_at'];
    
    protected $with = ['slug'];

    /*
    |--------------------------------------------------------------------------
    | FUNCTIONS
    |--------------------------------------------------------------------------
    */
    
    public function getTemplateName(): array|string
    {
        return str_replace('_', ' ', Str::title($this->template));
    }
    
    public function openLink(): string
    {
        $slug = slug($this).config('settings.url_prefix');
        
        return '<a target="_blank" href="'.url($slug).'"><i class="las la-link"></i><span class="ms-1">Liên kết</span></a>';
    }
    
    public function slug(): MorphOne
    {
        return $this->morphOne(Slug::class, 'sluggable', 'model');
    }
    
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
        ->id($this->id)
        ->title($this->name)
        ->summary($this->content ?: '')
        ->updated($this->updated_at)
        ->link(slug($this).config('settings.url_prefix'))
        ->authorName('MasterBank');
    }
    
    public static function getFeedItems(): array|Collection
    {
        return static::all();
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
