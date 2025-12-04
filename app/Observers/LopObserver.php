<?php

namespace App\Observers;

use App\Models\Lop;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use App\Http\Controllers\LopController;

class LopObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Lop "created" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function created(Lop $lop)
    {
        //
    }
    
    /**
     * Handle the Lop "saved" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function saved(Lop $lop)
    {
        
        if(!empty($lop->id)){
            if(!empty($_POST['start_date_one']) AND !empty($_POST['end_date_one']) AND !empty($_POST['start_date_two']) AND !empty($_POST['end_date_two'])){
                $update_time = Lop::where('id', $lop->id)->update([
                    'start_date_one'    => $_POST['start_date_one'],
                    'end_date_one'      => $_POST['end_date_one'],
                    'start_date_two'    => $_POST['start_date_two'],
                    'end_date_two'      => $_POST['end_date_two'],
                ]);
            }
        }
        
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $lop->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $lop->id){
                $slugmoi = $sluglink . '-' . $lop->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $lop->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => LopController::class,
                        'model' => Lop::class,
                        'sluggable_id' => $lop->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => LopController::class,
                'model' => Lop::class,
                'sluggable_id' => $lop->id
            ]);
        }
        
    }

    /**
     * Handle the Lop "updated" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function updated(Lop $lop)
    {
        /*
        if(!empty($lop->id)){
            $teacher = request()->teacher;
            $teacher = serialize($teacher);
            $lop->update([
                'teacher'   => $teacher,
            ]);
        }
        */
    }

    /**
     * Handle the Lop "deleted" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function deleted(Lop $lop)
    {
        //
    }

    /**
     * Handle the Lop "restored" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function restored(Lop $lop)
    {
        //
    }

    /**
     * Handle the Lop "force deleted" event.
     *
     * @param  \App\Models\Lop  $lop
     * @return void
     */
    public function forceDeleted(Lop $lop)
    {
        //
    }
}
