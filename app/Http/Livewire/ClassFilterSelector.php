<?php

namespace App\Http\Livewire;

use App\Traits\FilterTrait;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ClassFilterSelector extends Component
{
    use FilterTrait;

    // Props từ parent
    public $parish_id;
    public $showNamHoc = true;
    public $showKhoi = true;
    public $showLop = true;
    public $showKy = false; // Mặc định ẩn

    // Selected values
    public $selectedNamHoc = '';
    public $selectedKhoi = '';
    public $selectedLop = '';
    public $selectedKy = '';

    // Data
    public $namHocs = [];
    public $khois = [];
    public $lops = [];
    public $kys = [];

    protected $listeners = ['resetFilters' => 'resetFiltersHandler'];

    public function mount()
    {
        Log::info('ClassFilterSelector mount', ['parish_id' => $this->parish_id, 'showNamHoc' => $this->showNamHoc]);

        if ($this->parish_id && $this->showNamHoc) {
            $this->loadNamHocs();
        } else {
            Log::info('ClassFilterSelector mount skipped loadNamHocs', ['parish_id' => $this->parish_id, 'showNamHoc' => $this->showNamHoc]);
        }

        // If parent pre-filled selected values (e.g. when embedded in LopDetail), populate dependent lists
        if ($this->selectedNamHoc) {
            if ($this->showKhoi) {
                $this->loadKhois();
            }

            if ($this->showLop) {
                // load lops will respect selectedKhoi if present
                $this->loadLops();
            }
        }
    }

    public function loadNamHocs()
    {
        $data = $this->getNamHocs((int) $this->parish_id);
        $this->namHocs = $data['namHocs'];

        Log::info('ClassFilterSelector loadNamHocs result', ['parish_id' => $this->parish_id, 'count' => is_object($this->namHocs) ? $this->namHocs->count() : count($this->namHocs), 'selectedId' => $data['selectedId']]);

        // Auto-select năm học mới nhất
        if (!$this->selectedNamHoc && $data['selectedId']) {
            $this->selectedNamHoc = $data['selectedId'];
            $this->updatedSelectedNamHoc();
        }
    }

    public function updatedSelectedNamHoc()
    {
        $this->selectedKhoi = '';
        $this->selectedLop = '';
        $this->selectedKy = '';

        if ($this->showKhoi) {
            $this->loadKhois();
        }

        if ($this->showKy) {
            $this->loadKys();
        }

        $this->emitFilters();
    }

    public function updatedSelectedKhoi()
    {
        $this->selectedLop = '';

        if ($this->showLop) {
            $this->loadLops();
        }

        $this->emitFilters();
    }

    public function updatedSelectedLop()
    {
        $this->emitFilters();
    }

    public function updatedSelectedKy()
    {
        $this->emitFilters();
    }

    public function loadKhois()
    {
        if (!$this->selectedNamHoc) {
            $this->khois = [];
            return;
        }

        $this->khois = $this->getKhois($this->selectedNamHoc);
    }

    public function loadLops()
    {
        if (!$this->selectedNamHoc) {
            $this->lops = [];
            return;
        }

        // Use FilterTrait::getLops to get cached, consistent list
        // $lops = $this->getLops((int) $this->selectedNamHoc, $this->selectedKhoi);
        // $this->lops = is_object($lops) ? $lops->toArray() : (array) $lops;

        $this->lops = $this->getLops(
            (int) $this->selectedNamHoc,
            $this->selectedKhoi
        )->toArray();
    }

    public function loadKys()
    {
        // Logic load kỳ dựa vào năm học
        // Ví dụ:
        $this->kys = [
            1 => 'Kỳ 1',
            2 => 'Kỳ 2',
        ];
    }

    public function resetFiltersHandler()
    {
        $this->selectedNamHoc = '';
        $this->selectedKhoi = '';
        $this->selectedLop = '';
        $this->selectedKy = '';

        $this->khois = [];
        $this->lops = [];
        $this->kys = [];

        $this->emitFilters();
    }

    private function emitFilters()
    {
        $this->emit('filtersChanged', [
            'namHoc' => $this->selectedNamHoc,
            'khoi' => $this->selectedKhoi,
            'lop' => $this->selectedLop,
            'ky' => $this->selectedKy,
        ]);
    }

    public function render()
    {
        return view('livewire.class-filter-selector')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
