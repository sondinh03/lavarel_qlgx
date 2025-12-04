<?php

namespace App\Observers;

use App\Models\Block;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use App\Http\Controllers\BlockController;

class BlockObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Block "created" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function created(Block $block)
    {
        //
    }
    
    /**
     * Handle the Project "saved" event.
     *
     * @param  Block  $student
     * @return void
     */
    public function saved(Block $block)
    {
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            $sluglink = $this->slugify->slugify(request()->slug ?? $block->name);
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $block->id){
                $slugmoi = $sluglink . '-' . $block->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $block->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => BlockController::class,
                        'model' => Block::class,
                        'sluggable_id' => $block->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => BlockController::class,
                'model' => Block::class,
                'sluggable_id' => $block->id
            ]);
        }
    }

    /**
     * Handle the Block "updated" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function updated(Block $block)
    {
        Slug::updateOrCreate(
            ['controller' => BlockController::class, 'model' => Block::class, 'sluggable_id' => $block->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $block->name)]
        );
    }

    /**
     * Handle the Block "deleted" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function deleted(Block $block)
    {
        Slug::where([
            'controller' => BlockController::class,
            'model' => Block::class,
            'sluggable_id' => $block->id,
        ])->forceDelete();
    }

    /**
     * Handle the Block "restored" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function restored(Block $block)
    {
        //
    }

    /**
     * Handle the Block "force deleted" event.
     *
     * @param  \App\Models\Block  $block
     * @return void
     */
    public function forceDeleted(Block $block)
    {
        Slug::where([
            'controller' => BlockController::class,
            'model' => Block::class,
            'sluggable_id' => $block->id,
        ])->forceDelete();
    }
}
