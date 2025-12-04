<?php

namespace App\Observers;

use App\Models\Children;

class ChildrenObserver
{
    /**
     * Handle the Children "created" event.
     *
     * @param  \App\Models\Children  $children
     * @return void
     */
    public function created(Children $children)
    {
        //
    }
    
    public function saved(Children $children)
    {
        
    }

    /**
     * Handle the Children "updated" event.
     *
     * @param  \App\Models\Children  $children
     * @return void
     */
    public function updated(Children $children)
    {
        //
    }

    /**
     * Handle the Children "deleted" event.
     *
     * @param  \App\Models\Children  $children
     * @return void
     */
    public function deleted(Children $children)
    {
        //
    }

    /**
     * Handle the Children "restored" event.
     *
     * @param  \App\Models\Children  $children
     * @return void
     */
    public function restored(Children $children)
    {
        //
    }

    /**
     * Handle the Children "force deleted" event.
     *
     * @param  \App\Models\Children  $children
     * @return void
     */
    public function forceDeleted(Children $children)
    {
        //
    }
}
