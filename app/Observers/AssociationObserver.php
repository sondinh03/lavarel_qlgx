<?php

namespace App\Observers;

use App\Models\Association;
use App\Http\Controllers\AssociationController;
use App\Models\Slug;
use Cocur\Slugify\Slugify;

class AssociationObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Association "created" event.
     *
     * @param  \App\Models\Association  $association
     * @return void
     */
    public function created(Association $association)
    {
        //
    }
    
    /**
     * Handle the Project "saved" event.
     *
     * @param  Association  $association
     * @return void
     */
    public function saved(Association $association)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $association->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $association->id){
                $slugmoi = $sluglink . '-' . $association->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $association->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => AssociationController::class,
                        'model' => Association::class,
                        'sluggable_id' => $association->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => AssociationController::class,
                'model' => Association::class,
                'sluggable_id' => $association->id
            ]);
        }
    }

    /**
     * Handle the Association "updated" event.
     *
     * @param  \App\Models\Association  $association
     * @return void
     */
    public function updated(Association $association)
    {
        /*
        Slug::updateOrCreate(
            ['controller' => AssociationController::class, 'model' => Association::class, 'sluggable_id' => $association->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $association->name)]
        );
        */
    }

    /**
     * Handle the Association "deleted" event.
     *
     * @param  \App\Models\Association  $association
     * @return void
     */
    public function deleted(Association $association)
    {
        Slug::where([
            'controller' => AssociationController::class,
            'model' => Association::class,
            'sluggable_id' => $association->id,
        ])->forceDelete();
    }

    /**
     * Handle the Association "restored" event.
     *
     * @param  \App\Models\Association  $association
     * @return void
     */
    public function restored(Association $association)
    {
        //
    }

    /**
     * Handle the Association "force deleted" event.
     *
     * @param  \App\Models\Association  $association
     * @return void
     */
    public function forceDeleted(Association $association)
    {
        Slug::where([
            'controller' => AssociationController::class,
            'model' => Association::class,
            'sluggable_id' => $association->id,
        ])->forceDelete();
    }
}
