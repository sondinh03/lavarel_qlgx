<?php

namespace App\Observers;

use App\Models\Marriage;

class MarriageObserver
{
    /**
     * Handle the Marriage "created" event.
     *
     * @param  \App\Models\Marriage  $marriage
     * @return void
     */
    public function created(Marriage $marriage)
    {
        //
    }
    
    /**
    * Handle the Post "saved" event.
    *
    * @param  \App\Models\Marriage  $marriage
    * @return void
    */
    public function saved(Marriage $marriage)
    {
        
    }

    /**
     * Handle the Marriage "updated" event.
     *
     * @param  \App\Models\Marriage  $marriage
     * @return void
     */
    public function updated(Marriage $marriage)
    {
        //
    }

    /**
     * Handle the Marriage "deleted" event.
     *
     * @param  \App\Models\Marriage  $marriage
     * @return void
     */
    public function deleted(Marriage $marriage)
    {
        //
    }

    /**
     * Handle the Marriage "restored" event.
     *
     * @param  \App\Models\Marriage  $marriage
     * @return void
     */
    public function restored(Marriage $marriage)
    {
        //
    }

    /**
     * Handle the Marriage "force deleted" event.
     *
     * @param  \App\Models\Marriage  $marriage
     * @return void
     */
    public function forceDeleted(Marriage $marriage)
    {
        //
    }
}
