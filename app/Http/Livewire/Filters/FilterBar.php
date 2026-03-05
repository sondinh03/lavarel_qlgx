<?php

namespace App\Http\Livewire\Filters;

use App\Models\Block;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\Lop;
use App\Models\NamHoc;
use Livewire\Component;
use Illuminate\Support\Collection;

class FilterBar extends Component
{
    public bool $showNamHoc = true;
    public bool $showKhoi = true;
    public bool $showLop = true;
    public bool $showKy = true;

    public $selectedNamHoc;
    public $selectedKhoi;
    public $selectedLop;
    public $selectedKy;

    /** @var Collection<int, string> */
    public $namHocs;

    /** @var Collection<int, string> */
    public $khois;

    /** @var Collection<int, string> */
    public $lops;

    /** @var Collection<int, string> */
    // public $kys;
    public $kys = [
        '1' => 'Kỳ 1',
        '2' => 'Kỳ 2',
    ];

    /** 
     * Parish context
     * - null  : admin tổng
     * - int   : decen theo giáo xứ
     */
    public int $parish_id;

    /** 
     * Parish context
     * - null  : admin tổng
     * - int   : decen theo giáo xứ
     */
    public function mount($parishId = null): void
    {
        if (!$parishId) {
            session()->flash('warning', 'Vui lòng chọn giáo xứ');
            return;
        }

        $this->parish_id = $parishId;

        $this->namHocs = collect();
        $this->khois   = collect();
        $this->lops    = collect();
        $this->kys     = collect();

        if ($this->parish_id !== null) {
            $this->loadNamHocs();
        }

        if ($this->namHocs->isNotEmpty()) {
            $this->loadKhois();
            $this->loadLops();
        }
    }

    public function loadNamHocs()
    {
        $this->namHocs = NamHoc::ofParish($this->parish_id)
            ->active()
            ->orderByDesc('name')
            ->pluck('name', 'id');
    }

    protected function loadKhois(): void
    {
        if (!$this->selectedNamHoc) {
            $this->khois = collect();
            return;
        }

        $this->khois = GradeLevel::active()
            ->orderBy('sort_order')
            ->pluck('name', 'id');
    }

    protected function loadLops(): void
    {
        if (!$this->selectedNamHoc) {
            $this->lops = collect();
            return;
        }

        $this->lops = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->when(
                $this->selectedKhoi,
                fn($q) => $q->where('grade_level_id', $this->selectedKhoi)
            )
            ->active()
            ->pluck('name', 'id');
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->selectedNamHoc
            ? (int) $this->selectedNamHoc
            : null;

        $this->reset(['selectedKhoi', 'selectedLop', 'selectedKy']);

        $this->loadKhois();
        $this->lops = collect();

        $this->emitFilter();
    }

    public function updatedSelectedKhoi(): void
    {
        $this->selectedKhoi = $this->selectedKhoi
            ? (int) $this->selectedKhoi
            : null;

        $this->reset(['selectedLop']);
        $this->loadLops();

        $this->emitFilter();
    }


    public function updatedSelectedLop()
    {
        $this->selectedLop = $this->selectedLop
            ? (int) $this->selectedLop
            : null;

        $this->emitFilter();
    }

    public function updatedSelectedKy()
    {
        $this->emitFilter();
    }

    protected function emitFilter(): void
    {
        $this->emit('filterChanged', [
            'namHoc' => $this->selectedNamHoc,
            'khoi'   => $this->selectedKhoi,
            'lop'    => $this->selectedLop,
            'ky'     => $this->selectedKy,
        ]);
    }

    public function render()
    {
        return view('livewire.filters.filter-bar');
    }
}
