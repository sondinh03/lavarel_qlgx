<?php

namespace App\Http\Livewire\Lop;

use App\Services\LopService;
use App\Traits\FilterTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

class LopList extends Component
{
    use FilterTrait;
    use WithPagination;

    public $parish_id;
    public $selectedNamHoc;
    public $selectedKhoi = '';
    public $namHocs = [];
    public $khois = [];
    public $lops_;
    public $isAdmin = false;

    public $search = '';
    public $perPage = 15;
    protected $perPageOptions = [10, 15, 25, 50];

    /**
     * Validation rules for Livewire props
     */
    protected $rules = [
        'selectedNamHoc' => 'nullable|integer|exists:nam_hoc,id',
        'selectedKhoi' => 'nullable|integer',
        'perPage' => 'required|integer|in:10,15,25,50',
    ];

    protected $paginationTheme = 'tailwind';

    /**
     * ✅ Query string để share URL
     */
    protected $queryString = [
        'selectedNamHoc' => ['except' => ''],
        'selectedKhoi' => ['except' => ''],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    /**
     * Listeners cho Livewire events
     */
    protected $listeners = [
        'refreshLops' => 'loadLops',
        'filtersChanged' => 'handleFiltersChanged',
    ];

    public function mount()
    {
        $this->initializeUser();
        $this->loadInitialData();
        $this->sanitizeQueryString();
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // If validation fails for incoming query string values, reset to safe defaults
            $this->selectedNamHoc = $this->selectedNamHoc ?: null;
            $this->selectedKhoi = '';
            $this->perPage = 15;
        }
    }

    /**
     * ✅ Auto reset page khi search
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * ✅ Auto reset page khi thay đổi perPage
     */
    public function updatedPerPage()
    {
        // sanitize incoming perPage value
        $this->perPage = is_numeric($this->perPage) ? (int) $this->perPage : 15;
        if (!in_array($this->perPage, $this->perPageOptions)) {
            $this->perPage = 15;
        }

        $this->validateOnly('perPage');
        $this->resetPage();
    }

    /**
     * ✅ Khởi tạo thông tin user
     */
    private function initializeUser(): void
    {
        $this->parish_id = session('parish_id');
        $this->isAdmin = session('isAdmin', false);

        if (!$this->parish_id) {
            abort(403, 'Không có quyền truy cập');
        }
    }

    public function loadInitialData(): void
    {
        if (!$this->parish_id) {
            $this->namHocs = [];
            $this->khois = [];
            $this->lops_ = collect();
            return;
        }

        Log::info('Loading initial data for parish_id: ' . $this->parish_id);

        $data = $this->getNamHocs($this->parish_id);
        Log::info('NamHocs:', $data['namHocs']->toArray());
        $this->namHocs = $data['namHocs'];

        // ✅ Nếu chưa chọn năm học, lấy năm học mới nhất
        if (!$this->selectedNamHoc && $data['selectedId']) {
            $this->selectedNamHoc = $data['selectedId'];
        }

        if ($this->selectedNamHoc) {
            $this->loadKhois();
        }
    }

    /**
     * Khi thay đổi năm học
     */
    public function updatedSelectedNamHoc(): void
    {
        // sanitize and validate selectedNamHoc
        $this->selectedNamHoc = is_numeric($this->selectedNamHoc) ? (int) $this->selectedNamHoc : null;
        try {
            $this->validateOnly('selectedNamHoc');
        } catch (ValidationException $e) {
            // if invalid (e.g. id doesn't exist), reset to null and notify
            $this->selectedNamHoc = null;
            session()->flash('warning', 'Năm học không hợp lệ, đã đặt lại lựa chọn.');
        }

        $this->selectedKhoi = '';
        $this->khois = [];
        $this->search = '';

        $this->loadKhois();
        $this->resetPage();
    }

    /**
     * Khi thay đổi khối
     */
    public function updatedSelectedKhoi(): void
    {
        // allow empty string meaning "all"; otherwise cast to int
        if ($this->selectedKhoi === '' || $this->selectedKhoi === null) {
            $this->selectedKhoi = '';
        } else {
            $this->selectedKhoi = is_numeric($this->selectedKhoi) ? (int) $this->selectedKhoi : '';
        }

        $this->validateOnly('selectedKhoi');
        $this->resetPage();
    }

    /**
     * Clean and coerce query string inputs to safe types
     */
    private function sanitizeQueryString(): void
    {
        // selectedNamHoc: null or int
        if ($this->selectedNamHoc === '' || $this->selectedNamHoc === null) {
            $this->selectedNamHoc = $this->selectedNamHoc ?: null;
        } else {
            $this->selectedNamHoc = is_numeric($this->selectedNamHoc) ? (int) $this->selectedNamHoc : null;
        }

        // selectedKhoi: '' or int
        if ($this->selectedKhoi === '' || $this->selectedKhoi === null) {
            $this->selectedKhoi = '';
        } else {
            $this->selectedKhoi = is_numeric($this->selectedKhoi) ? (int) $this->selectedKhoi : '';
        }

        // perPage: ensure allowed value
        $this->perPage = is_numeric($this->perPage) ? (int) $this->perPage : 15;
        if (!in_array($this->perPage, $this->perPageOptions)) {
            $this->perPage = 15;
        }
    }

    public function loadKhois()
    {
        if (!$this->selectedNamHoc) {
            $this->khois = [];
            return;
        }

        $this->khois = $this->getKhois($this->selectedNamHoc);
    }

    /**
     * Handle filters emitted by ClassFilterSelector
     */
    public function handleFiltersChanged($filters)
    {
        // Expecting ['namHoc' => id, 'khoi' => id, 'lop' => id, 'ky' => id]
        $this->selectedNamHoc = $filters['namHoc'] ?? $this->selectedNamHoc;
        $this->selectedKhoi = $filters['khoi'] ?? $this->selectedKhoi;

        // clear search & reset pagination when filters change
        $this->search = '';
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->selectedKhoi = '';
        $this->search = '';
        $this->resetPage();

        session()->flash('message', 'Đã đặt lại bộ lọc');
    }

    /**
     * ✅ Refresh manual
     */
    public function refresh()
    {
        $this->resetPage();
        session()->flash('message', 'Đã làm mới danh sách');
    }

    public function render()
    {
        // Ensure $lops_ is always a paginator to avoid calling paginator methods on plain collections
        $lops_ = new LengthAwarePaginator([], 0, $this->perPage, 1);

        if ($this->selectedNamHoc) {
            // Use LopService to fetch paginated, transformed lops (expected to return a LengthAwarePaginator)
            $lops_ = app(LopService::class)->paginateLops(
                $this->selectedNamHoc,
                $this->selectedKhoi,
                $this->perPage,
                $this->search,
                $this->page ?? 1
            );
            // If service returns a collection for some reason, wrap it into a paginator
            if (! $lops_ instanceof LengthAwarePaginator) {
                $items = is_countable($lops_) ? (array) $lops_ : [];
                $total = count($items);
                $lops_ = new LengthAwarePaginator($items, $total, $this->perPage, $this->page ?? 1);
            }
        }

        return view('livewire.lop.lop-list', [
            'lops' => $lops_,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
