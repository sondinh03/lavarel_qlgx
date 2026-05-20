<?php

namespace App\Observers;

use App\Models\Family;
use App\Http\Controllers\FamilyController;
use App\Models\Slug;
use Cocur\Slugify\Slugify;

class FamilyObserver
{
    private Slugify $slugify;

    public function __construct()
    {
        $this->slugify = new Slugify();
    }

    /**
     * Handle the Family "created" event.
     */
    public function created(Family $family): void
    {
        //
    }

    /**
     * Handle the Family "saved" event.
     *
     * NOTE: Phần Marriage::updateOrCreate đã tạm bỏ vì bảng marriages
     * dùng column 'idfamily' không khớp — sẽ bổ sung lại khi cần.
     */
    public function saved(Family $family): void
    {
        // Slug
        if (!empty($_POST['slug'])) {
            $sluglink = $_POST['slug'];
        } else {
            $sluglink = $this->slugify->slugify(request()->slug ?? $family->name);
        }

        $slug = Slug::where('keyword', $sluglink)->first();

        if (!empty($slug)) {
            if ($slug->sluggable_id != $family->id) {
                $slugmoi   = $sluglink . '-' . $family->id;
                $checkslug = Slug::where('keyword', $slugmoi)
                    ->where('sluggable_id', $family->id)
                    ->first();

                if (empty($checkslug)) {
                    Slug::create([
                        'keyword'      => $slugmoi,
                        'controller'   => FamilyController::class,
                        'model'        => Family::class,
                        'sluggable_id' => $family->id,
                    ]);
                }
            }
        } else {
            Slug::create([
                'keyword'      => $sluglink,
                'controller'   => FamilyController::class,
                'model'        => Family::class,
                'sluggable_id' => $family->id,
            ]);
        }

        // TODO: Bổ sung lại khi bảng marriages đã chuẩn hoá column name
        // Marriage::updateOrCreate(
        //     ['idfamily' => $family->id],
        //     [
        //         'priest'            => request()->priest,
        //         'date'              => request()->date ?? now(),
        //         'peopleone'         => request()->peopleone,
        //         'peopletwo'         => request()->peopletwo,
        //         ...
        //     ]
        // );
    }

    /**
     * Handle the Family "updated" event.
     */
    public function updated(Family $family): void
    {
        //
    }

    /**
     * Handle the Family "deleted" event.
     */
    public function deleted(Family $family): void
    {
        Slug::where([
            'controller'   => FamilyController::class,
            'model'        => Family::class,
            'sluggable_id' => $family->id,
        ])->forceDelete();
    }

    /**
     * Handle the Family "restored" event.
     */
    public function restored(Family $family): void
    {
        //
    }

    /**
     * Handle the Family "force deleted" event.
     */
    public function forceDeleted(Family $family): void
    {
        Slug::where([
            'controller'   => FamilyController::class,
            'model'        => Family::class,
            'sluggable_id' => $family->id,
        ])->forceDelete();
    }
}