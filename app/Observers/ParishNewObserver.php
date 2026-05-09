<?php

namespace App\Observers;

use App\Models\ParishNew;

class ParishNewObserver
{
    public function created(ParishNew $parish): void
    {
        $this->clearCache();
    }
    public function updated(ParishNew $parish): void
    {
        $this->clearCache();
    }
    public function deleted(ParishNew $parish): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        cache()->forget('parishes_list');
    }
}
