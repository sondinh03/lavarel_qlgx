<?php

namespace App\Http\Livewire;

use App\Models\Decen;
use App\Models\SetAdmin;
use App\Traits\FilterTrait;
use Livewire\Component;
use Livewire\WithPagination;

class LopFilter extends Component
{
    use FilterTrait;
    use WithPagination;

    public $parish_id;
    public $selectedNamHoc;
    public $selectedKhoi;
    public $namHocs;
    public $khois;
    public $lops;
    public $isAdmin = false;

    protected $paginationTheme = 'tailwind';

    /**
     * ✅ Query string để share URL
     */
    protected $queryString = [
        'selectedNamHoc' => ['except' => ''],
        'selectedKhoi' => ['except' => ''],
    ];

    public function mount()
    {
        $this->initializeUser();
        $this->loadInitialData();
    }

    /**
     * ✅ Khởi tạo thông tin user
     */
    private function initializeUser()
    {
        $user = backpack_user();
        $userId = $user->id;

        $setadmin = SetAdmin::where('use', $userId)
            ->where('status', 1)
            ->first();

        $decen = Decen::where('use', $userId)
            ->where('status', 1)
            ->first();

        if (!empty($decen) && $decen->student == 1) {
            // User thường
            $this->parish_id = $decen->pid;
            $this->isAdmin = false;
        } elseif (!empty($setadmin)) {
            // Admin
            $this->parish_id = request()->get('giaoxu', null);
            $this->isAdmin = true;
        }
    }

    public function loadInitialData()
    {
        if (!$this->parish_id) {
            return; // Nếu admin chưa chọn giáo xứ thì không load
        }

        $data = $this->getNamHocs($this->parish_id);
        $this->namHocs = $data['namHocs'];

        // ✅ Nếu chưa chọn năm học, lấy năm học mới nhất
        if (!$this->selectedNamHoc && $data['selectedId']) {
            $this->selectedNamHoc = $data['selectedId'];
        }

        if ($this->selectedNamHoc) {
            $this->loadKhois();
            $this->loadLops();
        }
    }

    public function updatedSelectedNamHoc()
    {
        $this->selectedKhoi = null;
        $this->loadKhois();
        $this->loadLops();
        $this->resetPage();
    }

    public function updatedSelectedKhoi()
    {
        $this->loadLops();
        $this->resetPage();
    }

    public function loadKhois()
    {
        $this->khois = $this->getKhois($this->selectedNamHoc);
    }

    public function loadLops()
    {
        $this->lops = $this->getLopsDetailed($this->selectedNamHoc, $this->selectedKhoi);
    }

    public function resetFilters()
    {
        $this->selectedKhoi = null;
        $this->loadLops();
        $this->resetPage();
    }

    /**
     * ✅ Computed property để tránh re-render
     */
    public function getLopsProperty()
    {
        return $this->lops ?? collect();
    }

    public function render()
    {
        // ✅ DEBUG: Xem dữ liệu trước khi render
        // dd([
        //     'parish_id' => $this->parish_id,
        //     'namHocs' => $this->namHocs,
        //     'selectedNamHoc' => $this->selectedNamHoc,
        //     'khois' => $this->khois,
        //     'lops' => $this->lops,
        // ]);
        return view('livewire.lop-filter')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
