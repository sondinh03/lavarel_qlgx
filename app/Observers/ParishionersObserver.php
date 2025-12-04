<?php

namespace App\Observers;

use App\Models\Parishioners;
use App\Http\Controllers\ParishionersController;

use App\Models\Slug;
use Cocur\Slugify\Slugify;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;

class ParishionersObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Parishioners "created" event.
     *
     * @param  \App\Models\Parishioners  $parishioners
     * @return void
     */
    public function created(Parishioners $parishioners)
    {
        //
    }
    
    /**
     * Handle the Parishioners "saved" event.
     *
     * @param  Parishioners  $parishioners
     * @return void
     */
    public function saved(Parishioners $parishioners)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $parishioners->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $parishioners->id){
                $slugmoi = $sluglink . '-' . $parishioners->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $parishioners->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => ParishionersController::class,
                        'model' => Parishioners::class,
                        'sluggable_id' => $parishioners->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => ParishionersController::class,
                'model' => Parishioners::class,
                'sluggable_id' => $parishioners->id
            ]);
        }
    }

    /**
     * Handle the Parishioners "updated" event.
     *
     * @param  \App\Models\Parishioners  $parishioners
     * @return void
     */
    public function updated(Parishioners $parishioners)
    {
        /*
        $slug_parish = Slug::where('keyword', '=' , $_POST['slug'])->get()->toArray();
        print_r($slug_parish);die;
        if(count($slug_parish) > 1){
            $slug = $_POST['slug'] . '-' . $parishioners->id;
            $slug_parish = Slug::where('sluggable_id', '=' , $parishioners->id)->where('controller', ParishionersController::class)->where('model', Parishioners::class)->first();
            $slug_parish->update(['keyword' => $slug]);
        }else{
            Slug::create([
                'keyword' => $this->slugify->slugify(request()->slug ?? $parishioners->name),
                'controller' => ParishionersController::class,
                'model' => Parishioners::class,
                'sluggable_id' => $parishioners->id
            ]);
        }
        
        /*
        $slug_parish = Slug::where('sluggable_id', '=' , $parishioners->id)->where('controller', ParishionersController::class)->where('model', Parishioners::class)->first();
        if(empty($slug_parish->id)){
            $slug_parish = Slug::create([
                'keyword' => $this->slugify->slugify(request()->slug ?? $parishioners->name),
                'controller' => ParishionersController::class,
                'model' => Parishioners::class,
                'sluggable_id' => $parishioners->id
            ]);
        }else{
            $slug_parish->update(['keyword' => $this->slugify->slugify(request()->slug ?? $parishioners->name)]);
        }
        /*
        if(empty($slug_parish->id)){
            Slug::updateOrCreate(
                ['controller' => ParishionersController::class, 'model' => Parishioners::class, 'sluggable_id' => $parishioners->id],
                ['keyword' => $this->slugify->slugify(request()->slug ?? $parishioners->name)]
            );
        }else{
            Slug::where('keyword', $this->slugify->slugify(request()->slug ?? $parishioners->name))->update(
                ['keyword' => $this->slugify->slugify(request()->slug ?? $parishioners->name)],
                ['controller' => ParishionersController::class, 'model' => Parishioners::class, 'sluggable_id' => $parishioners->id],
            );
        }*/
    }

    /**
     * Handle the Parishioners "deleted" event.
     *
     * @param  \App\Models\Parishioners  $parishioners
     * @return void
     */
    public function deleted(Parishioners $parishioners)
    {
        Slug::where([
            'controller' => ParishionersController::class,
            'model' => Parishioners::class,
            'sluggable_id' => $parishioners->id,
        ])->forceDelete();
    }

    /**
     * Handle the Parishioners "restored" event.
     *
     * @param  \App\Models\Parishioners  $parishioners
     * @return void
     */
    public function restored(Parishioners $parishioners)
    {
        //
    }

    /**
     * Handle the Parishioners "force deleted" event.
     *
     * @param  \App\Models\Parishioners  $parishioners
     * @return void
     */
    public function forceDeleted(Parishioners $parishioners)
    {
        Slug::where([
            'controller' => ParishionersController::class,
            'model' => Parishioners::class,
            'sluggable_id' => $parishioners->id,
        ])->forceDelete();
    }
}
