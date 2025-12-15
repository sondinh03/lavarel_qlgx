<?php

namespace App\Observers;

use App\Models\Lop;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use App\Http\Controllers\LopController;
use Illuminate\Support\Facades\Cache;

class LopObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    /**
     * Handle the Lop "saved" event: create/update slug and invalidate cache version.
     */
    public function saved(Lop $lop)
    {
        // generate base slug from model name
        $slugBase = $this->slugify->slugify($lop->name ?: 'lop-' . $lop->id);

        // ensure uniqueness
        $slugCandidate = $slugBase;
        $existing = Slug::where('keyword', $slugCandidate)->first();
        if ($existing && $existing->sluggable_id != $lop->id) {
            $slugCandidate = $slugBase . '-' . $lop->id;
        }

        // update existing slug for this lop or create
        $current = $lop->slug()->first();
        if ($current) {
            if ($current->keyword !== $slugCandidate) {
                $current->keyword = $slugCandidate;
                $current->controller = LopController::class;
                $current->model = Lop::class;
                $current->save();
            }
        } else {
            Slug::create([
                'keyword' => $slugCandidate,
                'controller' => LopController::class,
                'model' => Lop::class,
                'sluggable_id' => $lop->id
            ]);
        }

        // Invalidate cached lists for this schoolyear
        $this->incrementVersion($lop->schoolyear);
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
        $this->incrementVersion($lop->schoolyear);
    }

    protected function incrementVersion($namhocId): void
    {
        if (empty($namhocId)) return;
        $key = "lops:version:namhoc:{$namhocId}";
        try {
            if (Cache::has($key)) {
                Cache::increment($key);
            } else {
                Cache::forever($key, 2);
            }
        } catch (\Exception $e) {
            Cache::forever($key, 1);
        }
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
