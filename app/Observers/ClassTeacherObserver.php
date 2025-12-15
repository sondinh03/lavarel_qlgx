<?php

namespace App\Observers;

use App\Models\ClassTeacher;
use Illuminate\Support\Facades\Cache;

class ClassTeacherObserver
{
    public function saved(ClassTeacher $ct): void
    {
        $this->incrementVersion($ct->namhoc_id);
    }

    public function deleted(ClassTeacher $ct): void
    {
        $this->incrementVersion($ct->namhoc_id);
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
}
