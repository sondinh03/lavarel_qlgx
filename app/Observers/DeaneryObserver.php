<?php

namespace App\Observers;

use App\Models\Deanery;
use App\Http\Controllers\DeaneryController;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeaneryObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Deanery "created" event.
     *
     * @param  \App\Models\Deanery  $deanery
     * @return void
     */
    public function created(Deanery $deanery)
    {
        //
    }
    
    /**
     * Handle the Post "saved" event.
     *
     * @param  Deanery  $deanery
     * @return void
     */
    public function saved(Deanery $deanery)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $deanery->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $deanery->id){
                $slugmoi = $sluglink . '-' . $deanery->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $deanery->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => DeaneryController::class,
                        'model' => Deanery::class,
                        'sluggable_id' => $deanery->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => DeaneryController::class,
                'model' => Deanery::class,
                'sluggable_id' => $deanery->id
            ]);
        }
    }

    /**
     * Handle the Deanery "updated" event.
     *
     * @param  \App\Models\Deanery  $deanery
     * @return void
     */
    public function updated(Deanery $deanery)
    {
        Slug::updateOrCreate(
            ['controller' => DeaneryController::class, 'model' => Deanery::class, 'sluggable_id' => $deanery->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $deanery->name)]
        );
    }

    /**
     * Handle the Deanery "deleted" event.
     *
     * @param  \App\Models\Deanery  $deanery
     * @return void
     */
    public function deleted(Deanery $deanery)
    {
        Slug::where([
            'controller' => DeaneryController::class,
            'model' => Deanery::class,
            'sluggable_id' => $deanery->id,
        ])->forceDelete();       
    }

    /**
     * Handle the Deanery "restored" event.
     *
     * @param  \App\Models\Deanery  $deanery
     * @return void
     */
    public function restored(Deanery $deanery)
    {
        //
    }

    /**
     * Handle the Deanery "force deleted" event.
     *
     * @param  \App\Models\Deanery  $deanery
     * @return void
     */
    public function forceDeleted(Deanery $deanery)
    {
        Slug::where([
            'controller' => DeaneryController::class,
            'model' => Deanery::class,
            'sluggable_id' => $deanery->id,
        ])->forceDelete();
    }
}
