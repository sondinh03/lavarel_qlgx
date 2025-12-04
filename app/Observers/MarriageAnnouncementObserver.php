<?php

namespace App\Observers;

use App\Models\MarriageAnnouncement;

use App\Models\Slug;
use Cocur\Slugify\Slugify;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Http\Controllers\MarriageAnnouncementController;
use App\Models\MarriageParishioner;

class MarriageAnnouncementObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the MarriageAnnouncement "created" event.
     *
     * @param  \App\Models\MarriageAnnouncement  $marriageAnnouncement
     * @return void
     */
    public function created(MarriageAnnouncement $marriageAnnouncement)
    {
        //
    }
    
    public function saved(MarriageAnnouncement $marriageAnnouncement)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $marriageAnnouncement->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $marriageAnnouncement->id){
                $slugmoi = $sluglink . '-' . $marriageAnnouncement->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $marriageAnnouncement->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => MarriageAnnouncementController::class,
                        'model' => MarriageAnnouncement::class,
                        'sluggable_id' => $marriageAnnouncement->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => MarriageAnnouncementController::class,
                'model' => MarriageAnnouncement::class,
                'sluggable_id' => $marriageAnnouncement->id
            ]);
        }
        
        // Luu thong tin nu
        if(request()->female){
            MarriageParishioner::updateOrCreate(
                [
                    'idannouncement'            => $marriageAnnouncement->id,
                    'idgiaodan'                 => request()->female,
                ],
                [
                    'sex'                       => '0',
                    'status'                    => 1,
                    'diocesesold'               => request()->female_dioceseold,
                    'deanerysold'               => request()->female_deaneryold,
                    'parishmanagementsold'      => request()->female_parishmanagementsold,
                    'parishsold'                => request()->female_parishsold,
                    'dioceses'                  => request()->female_diocese,
                    'deanerys'                  => request()->female_deanery,
                    'parishmanagements'         => request()->female_parishmanagements,
                    'parishs'                   => request()->female_parishs,
                    'diocesesbefore'            => request()->female_diocesebefore,
                    'deanerysbefore'            => request()->female_deanerybefore,
                    'parishmanagementsbefore'   => request()->female_parishmanagementsbefore,
                    'parishsbefore'             => request()->female_parishsbefore,
                ]
            );
        }
        
        // thong tin nam
        if(request()->male){
            MarriageParishioner::updateOrCreate(
                [
                    'idannouncement'            => $marriageAnnouncement->id,
                    'idgiaodan'                 => request()->male,
                ],
                [
                    'sex'                       => '1',
                    'status'                    => 1,
                    'diocesesold'               => request()->male_dioceseold,
                    'deanerysold'               => request()->male_deaneryold,
                    'parishmanagementsold'      => request()->male_parishmanagementsold,
                    'parishsold'                => request()->male_parishsold,
                    'dioceses'                  => request()->male_diocese,
                    'deanerys'                  => request()->male_deanery,
                    'parishmanagements'         => request()->male_parishmanagements,
                    'parishs'                   => request()->male_parishs,
                    'diocesesbefore'            => request()->male_diocesebefore,
                    'deanerysbefore'            => request()->male_deanerybefore,
                    'parishmanagementsbefore'   => request()->male_parishmanagementsbefore,
                    'parishsbefore'             => request()->male_parishsbefore,
                ]
            );
        }
    }

    /**
     * Handle the MarriageAnnouncement "updated" event.
     *
     * @param  \App\Models\MarriageAnnouncement  $marriageAnnouncement
     * @return void
     */
    public function updated(MarriageAnnouncement $marriageAnnouncement)
    {
        //
        Slug::updateOrCreate(
            ['controller' => MarriageAnnouncementController::class, 'model' => MarriageAnnouncement::class, 'sluggable_id' => $marriageAnnouncement->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $marriageAnnouncement->name)]
        );
    }

    /**
     * Handle the MarriageAnnouncement "deleted" event.
     *
     * @param  \App\Models\MarriageAnnouncement  $marriageAnnouncement
     * @return void
     */
    public function deleted(MarriageAnnouncement $marriageAnnouncement)
    {
        //
        Slug::where([
            'controller' => MarriageAnnouncementController::class,
            'model' => MarriageAnnouncement::class,
            'sluggable_id' => $marriageAnnouncement->id,
        ])->forceDelete();
    }

    /**
     * Handle the MarriageAnnouncement "restored" event.
     *
     * @param  \App\Models\MarriageAnnouncement  $marriageAnnouncement
     * @return void
     */
    public function restored(MarriageAnnouncement $marriageAnnouncement)
    {
        //
    }

    /**
     * Handle the MarriageAnnouncement "force deleted" event.
     *
     * @param  \App\Models\MarriageAnnouncement  $marriageAnnouncement
     * @return void
     */
    public function forceDeleted(MarriageAnnouncement $marriageAnnouncement)
    {
        //
        Slug::where([
            'controller' => MarriageAnnouncementController::class,
            'model' => MarriageAnnouncement::class,
            'sluggable_id' => $marriageAnnouncement->id,
        ])->forceDelete();
    }
}
