<?php

namespace App\Observers;

use App\Http\Controllers\ParishManagementController;
use App\Models\ParishManagement;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ParishManagementObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the ParishManagement "created" event.
     *
     * @param  \App\Models\ParishManagement  $parishManagement
     * @return void
     */
    public function created(ParishManagement $parishManagement)
    {
        
    }

    /**
     * Handle the Post "saved" event.
     *
     * @param  Post  $parishManagement
     * @return void
     */
    public function saved(ParishManagement $parishManagement)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $parishManagement->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $parishManagement->id){
                $slugmoi = $sluglink . '-' . $parishManagement->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $parishManagement->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => ParishManagementController::class,
                        'model' => ParishManagement::class,
                        'sluggable_id' => $parishManagement->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => ParishManagementController::class,
                'model' => ParishManagement::class,
                'sluggable_id' => $parishManagement->id
            ]);
        }
    }

    /**
     * Handle the ParishManagement "updated" event.
     *
     * @param  \App\Models\ParishManagement  $parishManagement
     * @return void
     */
    public function updated(ParishManagement $parishManagement)
    {
        /*
        Slug::updateOrCreate(
            ['controller' => ParishManagementController::class, 'model' => ParishManagement::class, 'sluggable_id' => $parishManagement->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $parishManagement->name)]
        );
        */
    }

    /**
     * Handle the ParishManagement "deleted" event.
     *
     * @param  \App\Models\ParishManagement  $parishManagement
     * @return void
     */
    public function deleted(ParishManagement $parishManagement)
    {        
        Slug::where([
            'controller' => ParishManagementController::class,
            'model' => ParishManagement::class,
            'sluggable_id' => $parishmanagement->id,
        ])->forceDelete();        
        
    }

    /**
     * Handle the ParishManagement "restored" event.
     *
     * @param  \App\Models\ParishManagement  $parishManagement
     * @return void
     */
    public function restored(ParishManagement $parishManagement)
    {
        //
    }

    /**
     * Handle the ParishManagement "force deleted" event.
     *
     * @param  \App\Models\ParishManagement  $parishManagement
     * @return void
     */
    public function forceDeleted(ParishManagement $parishManagement)
    {
        //
        Slug::where([
            'controller' => ParishManagementController::class,
            'model' => ParishManagement::class,
            'sluggable_id' => $parishmanagement->id,
        ])->forceDelete();
    }
}
