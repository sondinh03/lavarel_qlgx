<?php

namespace App\Observers;

use App\Models\Parishioner;

class ParishionerObserver
{
    public function created(Parishioner $parishioner): void
    {
        //
    }

    public function saved(Parishioner $parishioner): void
    {
        //
    }

    public function updated(Parishioner $parishioner): void
    {
        //
    }

    public function deleted(Parishioner $parishioner): void
    {
        //
    }

    public function restored(Parishioner $parishioner): void
    {
        //
    }

    public function forceDeleted(Parishioner $parishioner): void
    {
        //
    }
}
