<?php

namespace App\Http\Livewire\Filters;

use App\Models\Block;
use App\Models\CatechismClass;
use App\Models\GradeLevel;
use App\Models\NamHoc;
use Livewire\Component;
use Illuminate\Support\Collection;

class FilterBar extends Component
{
    public bool $showNamHoc = true;
    public bool $showKhoi = true;
    public bool $showLop = true;
    public bool $showKy = true;

    /**
     * Khi true: đổi bộ lọc phải được Alpine/parent xác nhận (macOS leave-guard).
     * Dùng cho trang có draft chưa lưu (điểm danh).
     */
    public bool $leaveGuard = false;

    /** Đang chờ confirm/cancel — chặn đổi filter chồng chéo. */
    public bool $pendingLeave = false;

    /** @var array{namHoc:mixed,khoi:mixed,lop:mixed,ky:mixed}|null */
    public $guardSnapshot = null;

    /** @var array{namHoc:mixed,khoi:mixed,lop:mixed,ky:mixed}|null */
    public $pendingFilters = null;

    /** @var string|null */
    public $revertField = null;

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
        '0' => 'Cả năm',
        '1' => 'Kỳ 1',
        '2' => 'Kỳ 2',
    ];

    /**
     * Parish context
     * - null  : admin tổng
     * - int   : decen theo giáo xứ
     */
    public int $parish_id;

    protected $listeners = [
        'resetFilters' => 'handleReset',
        'confirmFilterLeave' => 'confirmFilterLeave',
        'cancelFilterLeave' => 'cancelFilterLeave',
    ];

    /**
     * Parish context
     * - null  : admin tổng
     * - int   : decen theo giáo xứ
     */
    public function mount(
        $parishId = null,
        $selectedNamHoc = null,
        $selectedKhoi = null,
        $selectedLop = null,
        $selectedKy = null
    ): void {
        if (!$parishId) {
            session()->flash('warning', 'Vui lòng chọn giáo xứ');
            return;
        }

        $this->parish_id = $parishId;

        if ($selectedNamHoc !== null && $selectedNamHoc !== '') {
            $this->selectedNamHoc = $selectedNamHoc;
        }
        if ($selectedKhoi !== null && $selectedKhoi !== '') {
            $this->selectedKhoi = $selectedKhoi;
        }
        if ($selectedLop !== null && $selectedLop !== '') {
            $this->selectedLop = $selectedLop;
        }
        if ($selectedKy !== null && $selectedKy !== '') {
            $this->selectedKy = $selectedKy;
        }

        $this->namHocs = collect();
        $this->khois   = collect();
        $this->lops    = collect();
        // $this->kys     = collect();

        if ($this->parish_id !== null) {
            $this->loadNamHocs();
        }

        $hadNamHoc = (bool) $this->selectedNamHoc;
        $this->ensureDefaultNamHoc();

        if ($this->selectedNamHoc && $this->namHocs->isNotEmpty()) {
            $this->loadKhois();
            $this->loadLops();
        }

        if (!$this->selectedKy && $this->selectedNamHoc) {
            $this->selectedKy = $this->detectCurrentSemester();
        }

        if ($this->selectedNamHoc && !$hadNamHoc) {
            $this->emitFilter();
        }
    }

    /**
     * Luôn có năm học mặc định khi danh sách không rỗng.
     */
    protected function ensureDefaultNamHoc(): void
    {
        if ($this->selectedNamHoc && $this->namHocs->keys()->contains((int) $this->selectedNamHoc)) {
            $this->selectedNamHoc = (int) $this->selectedNamHoc;
            return;
        }

        $defaultId = $this->resolveDefaultNamHocId();
        $this->selectedNamHoc = $defaultId ? (int) $defaultId : null;
    }

    protected function resolveDefaultNamHocId(): ?int
    {
        $current = NamHoc::where('parish_id', $this->parish_id)
            ->active()
            ->current()
            ->value('id');

        if ($current) {
            return (int) $current;
        }

        $first = $this->namHocs->keys()->first();

        return $first ? (int) $first : null;
    }

    public function handleReset(): void
    {
        if ($this->leaveGuard && $this->pendingLeave) {
            return;
        }

        $this->captureGuardSnapshot();
        $this->selectedKhoi = null;
        $this->selectedLop  = null;
        $this->loadLops();
        $this->requestFilterEmit('đặt lại bộ lọc');
    }

    protected function filterPayload(): array
    {
        return [
            'namHoc' => $this->selectedNamHoc,
            'khoi'   => $this->selectedKhoi,
            'lop'    => $this->selectedLop,
            'ky'     => $this->selectedKy,
        ];
    }

    protected function captureGuardSnapshot(): void
    {
        if (!$this->leaveGuard || $this->pendingLeave) {
            return;
        }

        $this->guardSnapshot = $this->filterPayload();
    }

    protected function beginPendingLeave(): void
    {
        $this->pendingLeave   = true;
        $this->pendingFilters = $this->filterPayload();
    }

    protected function clearPendingLeave(): void
    {
        $this->pendingLeave   = false;
        $this->pendingFilters = null;
        $this->guardSnapshot  = null;
        $this->revertField    = null;
    }

    /**
     * Emit ngay, hoặc nhờ parent xác nhận khi đang leaveGuard.
     */
    protected function requestFilterEmit(string $actionLabel): void
    {
        if (!$this->leaveGuard) {
            $this->emitFilter();
            return;
        }

        $this->beginPendingLeave();

        $this->dispatchBrowserEvent('filter-leave-request', [
            'actionLabel' => $actionLabel,
            'filters'     => $this->filterPayload(),
            'snapshot'    => $this->guardSnapshot,
            'componentId' => $this->id,
        ]);
    }

    public function confirmFilterLeave(): void
    {
        if (!$this->leaveGuard) {
            return;
        }

        $this->clearPendingLeave();
        $this->emitFilter();
    }

    public function cancelFilterLeave(): void
    {
        if (!$this->leaveGuard || !is_array($this->guardSnapshot)) {
            $this->clearPendingLeave();
            return;
        }

        $this->selectedNamHoc = $this->guardSnapshot['namHoc'] ?? null;
        $this->selectedKhoi   = $this->guardSnapshot['khoi'] ?? null;
        $this->selectedLop    = $this->guardSnapshot['lop'] ?? null;
        $this->selectedKy     = $this->guardSnapshot['ky'] ?? null;
        $this->clearPendingLeave();

        if ($this->selectedNamHoc) {
            $this->loadKhois();
            $this->loadLops();
        } else {
            $this->khois = collect();
            $this->lops  = collect();
        }
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
        $this->namHocs = NamHoc::where('parish_id', $this->parish_id)
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
            ->get(['id', 'name'])
            ->map(fn($lop) => ['id' => $lop->id, 'name' => $lop->name]);
    }

    protected function blockIfPending(string $field): bool
    {
        if (!$this->leaveGuard || !$this->pendingLeave) {
            return false;
        }

        $this->revertField = $field;
        return true;
    }

    protected function restorePendingField(string $field): bool
    {
        if ($this->revertField !== $field || !is_array($this->pendingFilters)) {
            return false;
        }

        $this->selectedNamHoc = $this->pendingFilters['namHoc'] ?? null;
        $this->selectedKhoi   = $this->pendingFilters['khoi'] ?? null;
        $this->selectedLop    = $this->pendingFilters['lop'] ?? null;
        $this->selectedKy     = $this->pendingFilters['ky'] ?? null;
        $this->revertField    = null;

        if ($this->selectedNamHoc) {
            $this->loadKhois();
            $this->loadLops();
        }

        return true;
    }

    public function updatingSelectedNamHoc(): void
    {
        if ($this->blockIfPending('namHoc')) {
            return;
        }
        $this->captureGuardSnapshot();
    }

    public function updatingSelectedKhoi(): void
    {
        if ($this->blockIfPending('khoi')) {
            return;
        }
        $this->captureGuardSnapshot();
    }

    public function updatingSelectedLop(): void
    {
        if ($this->blockIfPending('lop')) {
            return;
        }
        $this->captureGuardSnapshot();
    }

    public function updatingSelectedKy(): void
    {
        if ($this->blockIfPending('ky')) {
            return;
        }
        $this->captureGuardSnapshot();
    }

    public function updatedSelectedNamHoc(): void
    {
        if ($this->restorePendingField('namHoc')) {
            return;
        }

        if (!$this->selectedNamHoc || $this->selectedNamHoc === '') {
            $this->ensureDefaultNamHoc();
        } else {
            $this->selectedNamHoc = (int) $this->selectedNamHoc;
        }

        $this->reset(['selectedKhoi', 'selectedLop', 'selectedKy']);

        $this->selectedKy = $this->detectCurrentSemester();

        $this->loadKhois();
        $this->loadLops();

        $this->requestFilterEmit('đổi năm học');
    }

    public function updatedSelectedKhoi(): void
    {
        if ($this->restorePendingField('khoi')) {
            return;
        }

        $this->selectedKhoi = $this->selectedKhoi !== ''
            ? (int) $this->selectedKhoi
            : null;

        $this->reset(['selectedLop']);
        $this->loadLops();

        $this->requestFilterEmit('đổi khối');
    }

    public function updatedSelectedLop()
    {
        if ($this->restorePendingField('lop')) {
            return;
        }

        $this->selectedLop = $this->selectedLop !== ''
            ? (int) $this->selectedLop
            : null;

        $this->requestFilterEmit('đổi lớp');
    }

    public function updatedSelectedKy(): void
    {
        if ($this->restorePendingField('ky')) {
            return;
        }

        if ($this->selectedKy !== '' && $this->selectedKy !== null) {
            $this->selectedKy = (int) $this->selectedKy;
        }

        $this->requestFilterEmit('đổi học kỳ');
    }

    protected function emitFilter(): void
    {
        $this->emit('filterChanged', $this->filterPayload());
    }

    public function render()
    {
        return view('livewire.filters.filter-bar');
    }
}
