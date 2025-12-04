<?php

namespace App\Observers;

use App\Http\Controllers\PageController;
use App\Models\Page;
use App\Models\Slug;
use Cocur\Slugify\Slugify;

class PageObserver
{
    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    /**
     * Handle the Page "created" event.
     *
     * @param  Page  $page
     * @return void
     */
    public function created(Page $page)
    {
        //
    }

    /**
     * Handle the Page "saved" event.
     *
     * @param  Page  $page
     * @return void
     */
    public function saved(Page $page)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $page->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $page->id){
                $slugmoi = $sluglink . '-' . $page->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $page->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => PageController::class,
                        'model' => Page::class,
                        'sluggable_id' => $page->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => PageController::class,
                'model' => Page::class,
                'sluggable_id' => $page->id
            ]);
        }
    }

    /**
     * Handle the Page "deleted" event.
     *
     * @param  Page  $page
     * @return void
     */
    public function deleted(Page $page)
    {
        Slug::where([
            'controller' => PageController::class,
            'model' => Page::class,
            'sluggable_id' => $page->id,
        ])->forceDelete();
    }

    /**
     * Handle the Page "restored" event.
     *
     * @param  Page  $page
     * @return void
     */
    public function restored(Page $page)
    {
    }

    /**
     * Handle the Page "force deleted" event.
     *
     * @param  Page  $page
     * @return void
     */
    public function forceDeleted(Page $page)
    {
        Slug::where([
            'controller' => PageController::class,
            'model' => Page::class,
            'sluggable_id' => $page->id,
        ])->forceDelete();
    }
}
