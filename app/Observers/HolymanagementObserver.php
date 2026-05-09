<?php

namespace App\Observers;

use App\Models\Holymanagement;

class HolymanagementObserver
{
    public function created(Holymanagement $holy): void
    {
        $this->clearCache();
    }
    public function updated(Holymanagement $holy): void
    {
        $this->clearCache();
    }
    public function deleted(Holymanagement $holy): void
    {
        $this->clearCache();
    }

    private function clearCache(): void
    {
        cache()->forget('saints_list');
    }
}
