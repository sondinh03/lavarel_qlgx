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

    protected $listeners = ['resetFilters' => 'handleReset'];

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
        // $this->kys     = collect();

        if ($this->parish_id !== null) {
            $this->loadNamHocs();
        }

        if ($this->namHocs->isNotEmpty()) {
            $this->loadKhois();
            $this->loadLops();
        }

        if (!$this->selectedKy && $this->selectedNamHoc) {
            $this->selectedKy = $this->detectCurrentSemester();
        }
    }

    public function handleReset(): void
    {
        $this->selectedKhoi = null;
        $this->selectedLop  = null;
        $this->loadLops(); 
        $this->emitFilter(); // notify lại cha để đồng bộ
    }

    /**
     * Xác định kỳ hiện tại dựa vào ngày hôm nay và NamHoc đang chọn
     */
    protected function detectCurrentSemester(): ?int
    {
        if (!$this->selectedNamHoc) {
            return null;
        }

        $namHoc = NamHoc::find($this->selectedNamHoc);

        if (!$namHoc) {
            return null;
        }

        $today = now()->toDateString();

        // Trong khoảng kỳ 1
        if ($namHoc->start_date_one && $namHoc->end_date_one) {
            if (
                $today >= $namHoc->start_date_one->toDateString()
                && $today <= $namHoc->end_date_one->toDateString()
            ) {
                return 1;
            }
        }

        // Trong khoảng kỳ 2
        if ($namHoc->start_date_two && $namHoc->end_date_two) {
            if (
                $today >= $namHoc->start_date_two->toDateString()
                && $today <= $namHoc->end_date_two->toDateString()
            ) {
                return 2;
            }
        }

        // Ngoài cả 2 kỳ → fallback theo ngày
        // Trước kỳ 1 hoặc giữa 2 kỳ → chọn kỳ 1
        // Sau kỳ 2 → chọn kỳ 2
        if ($namHoc->end_date_two && $today > $namHoc->end_date_two->toDateString()) {
            return 2;
        }

        return 1; // default kỳ 1
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
            ->orderBy('grade_level_id')
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    public function updatedSelectedNamHoc(): void
    {
        $this->selectedNamHoc = $this->selectedNamHoc
            ? (int) $this->selectedNamHoc
            : null;

        $this->reset(['selectedKhoi', 'selectedLop', 'selectedKy']);

        $this->selectedKy = $this->detectCurrentSemester();

        $this->loadKhois();
        $this->loadLops();

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
