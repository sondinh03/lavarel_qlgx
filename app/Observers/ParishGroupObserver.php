<?php

namespace App\Observers;

use App\Models\ParishGroup;
use App\Support\CacheKeys;

class ParishGroupObserver
{
    public function created(ParishGroup $group): void
    {
        $this->clearCache($group->parish_id);
    }
    public function updated(ParishGroup $group): void
    {
        $this->clearCache($group->parish_id);
    }
    public function deleted(ParishGroup $group): void
    {
        $this->clearCache($group->parish_id);
    }

    private function clearCache(int $parishId): void
    {
        cache()->forget(CacheKeys::parishGroups($parishId));
    }
}
