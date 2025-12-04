<?php

namespace App\Observers;

use App\Models\Diocese;
use App\Http\Controllers\DioceseController;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DioceseObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    /**
     * Handle the Diocese "created" event.
     *
     * @param  \App\Models\Diocese  $diocese
     * @return void
     */
    public function created(Diocese $diocese)
    {
        //
    }
    
    /**
     * Handle the Post "saved" event.
     *
     * @param  Diocese  $Diocese
     * @return void
     */
    public function saved(Diocese $diocese)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $diocese->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $diocese->id){
                $slugmoi = $sluglink . '-' . $diocese->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $diocese->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => DioceseController::class,
                        'model' => Diocese::class,
                        'sluggable_id' => $diocese->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => DioceseController::class,
                'model' => Diocese::class,
                'sluggable_id' => $diocese->id
            ]);
        }
    }

    /**
     * Handle the Diocese "updated" event.
     *
     * @param  \App\Models\Diocese  $diocese
     * @return void
     */
    public function updated(Diocese $diocese)
    {
        Slug::updateOrCreate(
            ['controller' => DioceseController::class, 'model' => Diocese::class, 'sluggable_id' => $diocese->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $diocese->name)]
        );
    }

    /**
     * Handle the Diocese "deleted" event.
     *
     * @param  \App\Models\Diocese  $diocese
     * @return void
     */
    public function deleted(Diocese $diocese)
    {
        Slug::where([
            'controller' => DioceseController::class,
            'model' => Diocese::class,
            'sluggable_id' => $diocese->id,
        ])->forceDelete();   
    }

    /**
     * Handle the Diocese "restored" event.
     *
     * @param  \App\Models\Diocese  $diocese
     * @return void
     */
    public function restored(Diocese $diocese)
    {
        //
    }

    /**
     * Handle the Diocese "force deleted" event.
     *
     * @param  \App\Models\Diocese  $diocese
     * @return void
     */
    public function forceDeleted(Diocese $diocese)
    {
        Slug::where([
            'controller' => DioceseController::class,
            'model' => Diocese::class,
            'sluggable_id' => $diocese->id,
        ])->forceDelete();
    }
}
