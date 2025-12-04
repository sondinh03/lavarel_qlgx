<?php

namespace App\Observers;

use App\Models\GiaDinh;
use App\Http\Controllers\GiaDinhController;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GiaDinhObserver
{
    
    /**
     * Handle the GiaDinh "created" event.
     *
     * @param  \App\Models\GiaDinh  $giadinh
     * @return void
     */
    public function created(GiaDinh $giadinh)
    {
        //
    }
    
    /**
     * Handle the Post "saved" event.
     *
     * @param  GiaDinh  $giadinh
     * @return void
     */
    public function saved(GiaDinh $giadinh)
    {
        
    }

    /**
     * Handle the GiaDinh "updated" event.
     *
     * @param  \App\Models\GiaDinh  $giadinh
     * @return void
     */
    public function updated(GiaDinh $giadinh)
    {
        
    }

    /**
     * Handle the GiaDinh "deleted" event.
     *
     * @param  \App\Models\GiaDinh  $giadinh
     * @return void
     */
    public function deleted(GiaDinh $giadinh)
    {
        
    }

    /**
     * Handle the GiaDinh "restored" event.
     *
     * @param  \App\Models\GiaDinh  $giadinh
     * @return void
     */
    public function restored(GiaDinh $giadinh)
    {
        //
    }

    /**
     * Handle the GiaDinh "force deleted" event.
     *
     * @param  \App\Models\GiaDinh  $giadinh
     * @return void
     */
    public function forceDeleted(GiaDinh $giadinh)
    {
        
    }
}
