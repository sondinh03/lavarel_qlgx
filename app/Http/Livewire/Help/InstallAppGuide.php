<?php

namespace App\Http\Livewire\Help;

use App\Http\Livewire\Base\BaseComponent;

class InstallAppGuide extends BaseComponent
{
    protected function loadInitialData(): void
    {
        // Trang hướng dẫn — không cần preload
    }

    public function render()
    {
        return view('livewire.help.install-app-guide')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
