<?php

namespace App\Observers;

use App\Models\Family;
use App\Http\Controllers\FamilyController;

use App\Models\Slug;
use Cocur\Slugify\Slugify;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Marriage;
use Carbon\Carbon;
use App\Models\GiaDinh;

class FamilyObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Family "created" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function created(Family $family)
    {
        //
    }
    
    /**
     * Handle the Family "saved" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function saved(Family $family)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $family->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $family->id){
                $slugmoi = $sluglink . '-' . $family->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $family->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => FamilyController::class,
                        'model' => Family::class,
                        'sluggable_id' => $family->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => FamilyController::class,
                'model' => Family::class,
                'sluggable_id' => $family->id
            ]);
        }
        
        if(empty(request()->date)){
            $date = Carbon::now()->toDateTimeString();;
        }else{
            $date = request()->date;
        }
        if(!empty(request()->sohonphoi)){
            $sohonphoi = request()->sohonphoi;
        }else{
            $sohonphoi = '';
        }
        if(!empty(request()->marriage_address)){
            $marriage_address = request()->marriage_address;
        }else{
            $marriage_address = '';
        }
        
        if(!empty(request()->marriage_ward)){
            $marriage_ward = request()->marriage_ward;
        }else{
            $marriage_ward = '';
        }
                
        if(!empty(request()->marriage_province)){
            $marriage_province = request()->marriage_province;
        }else{
            $marriage_province = '';
        }
        
        Marriage::updateOrCreate(
            [
                'idfamily'          => $family->id
            ],
            [
                'priest'            => request()->priest,
                'date'              => $date,
                'sohonphoi'         => $sohonphoi,
                'marriage_address'  => $marriage_address,
                'marriage_ward'     => $marriage_ward,
                'marriage_province' => $marriage_province,
                'peopleone'         => request()->peopleone,
                'peopletwo'         => request()->peopletwo,
                'tinhtrang'         => request()->tinhtrang,
                'marriage_note'     => request()->marriage_note,
            ]
        );
    }

    /**
     * Handle the Family "updated" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function updated(Family $family)
    {
        /*
        Slug::updateOrCreate(
            ['controller' => FamilyController::class, 'model' => Family::class, 'sluggable_id' => $family->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $family->name)]
        );
        */
        /*
        Marriage::update(
            [
                'date'              => $date,
                'marriage_address'  => $marriage_address,
                'marriage_ward'     => $marriage_ward,
                'marriage_province' => $marriage_province,
                'peopleone'         => request()->peopleone,
                'peopletwo'         => request()->peopletwo,
                'tinhtrang'         => request()->tinhtrang,
                'marriage_note'     => request()->marriage_note,
            ],
            [
                'priest'            => request()->priest,
                'idfamily' => $family->id,
            ]
        ); */
        /*
        Marriage::updateOrCreate(
            ['family_id' => $family->id],
            ['date' => $_POST['date']],
            ['marriage_address' => (string)$_POST['marriage_address']],
            ['marriage_ward' => $_POST['marriage_ward']],
            ['marriage_province' => $_POST['marriage_province']],
            ['peopleone' => $_POST['peopleone']],
            ['peopletwo' => $_POST['peopletwo']],
            ['tinhtrang' => $_POST['tinhtrang']],
            ['marriage_note' => $_POST['marriage_note']],
        );*/
    }

    /**
     * Handle the Family "deleted" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function deleted(Family $family)
    {
        Slug::where([
            'controller' => FamilyController::class,
            'model' => Family::class,
            'sluggable_id' => $family->id,
        ])->forceDelete();
        /*
        Marriage::where([
            'family_id' => $family->id,
        ])->forceDelete();
        */
    }

    /**
     * Handle the Family "restored" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function restored(Family $family)
    {
        //
    }

    /**
     * Handle the Family "force deleted" event.
     *
     * @param  \App\Models\Family  $family
     * @return void
     */
    public function forceDeleted(Family $family)
    {
        Slug::where([
            'controller' => FamilyController::class,
            'model' => Family::class,
            'sluggable_id' => $family->id,
        ])->forceDelete();
        /*
        Marriage::where([
            'family_id' => $family->id,
        ])->forceDelete();
        */
    }
}
