<?php

namespace App\Http\Livewire\NamHoc;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\NamHoc;

class NamHocSetupGuide extends BaseComponent
{
    protected function loadInitialData(): void
    {
        // Trang hướng dẫn — không cần preload danh sách
    }

    public function mount(): void
    {
        $this->authorize('viewAny', NamHoc::class);
        parent::mount();
    }

    public function render()
    {
        $hasExistingYears = NamHoc::query()->exists();

        return view('livewire.nam-hoc.nam-hoc-setup-guide', [
            'hasExistingYears' => $hasExistingYears,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
