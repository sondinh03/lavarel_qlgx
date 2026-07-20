<?php

namespace App\Http\Livewire\Help;

use App\Http\Livewire\Base\BaseComponent;

class AttendanceSetupGuide extends BaseComponent
{
    protected function loadInitialData(): void
    {
        // Trang hướng dẫn — không cần preload
    }

    public function render()
    {
        return view('livewire.help.attendance-setup-guide')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
