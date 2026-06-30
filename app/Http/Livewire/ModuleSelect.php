<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ModuleSelect extends Component
{
    public array $modules = [];

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            redirect('/admin/dashboard');
        }

        $this->modules = $this->resolveModules($user);

        // Nếu chỉ có 1 module → redirect thẳng, không hiện trang chọn
        if (count($this->modules) === 1) {
            redirect()->route($this->modules[0]['route']);
        }
    }

    private function resolveModules($user): array
    {
        $all = [
            [
                'key'         => 'parishioner',
                'label'       => 'Giáo dân',
                'description' => 'Quản lý hồ sơ giáo dân, gia đình, bí tích, hôn phối',
                'route'       => 'parishioners.index',
                'icon'        => 'parishioner',
                'roles'       => ['parish_admin'],         // chỉ parish_admin
            ],
            [
                'key'         => 'catechism',
                'label'       => 'Giáo lý',
                'description' => 'Quản lý lớp học, học sinh, giáo viên, điểm số',
                'route'       => 'parish-admin.dashboard',
                'icon'        => 'catechism',
                'roles'       => ['parish_admin', 'catechist'], // cả 2
            ],
        ];

        return array_values(array_filter($all, fn($m) => $user->hasAnyRole($m['roles'])));
    }

    public function render()
    {
        return view('livewire.module-select')
            ->extends('frontend.layout.blank') 
            ->section('content');
    }
}
