<?php

namespace App\Http\Livewire\Lop;

use App\Models\Decen;
use App\Models\SetAdmin;
use App\Traits\FilterTrait;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

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
    protected $listeners = ['refreshLops' => 'loadLops'];

    public function mount()
    {
        $this->initializeUser();
        $this->loadInitialData();
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
        $this->resetPage();
    }

    /**
     * ✅ Khởi tạo thông tin user
     */
    private function initializeUser(): void
    {
        $user = backpack_user();

        if (!$user) {
            return;
        }

        $userId = $user->id;

        $setadmin = SetAdmin::where('use', $userId)
            ->where('status', 1)
            ->first();

        if ($setadmin) {
            // Admin - lấy giáo xứ từ request
            $this->isAdmin = true;
            $this->parish_id = request()->get('giaoxu');
            return;
        }

        $decen = Decen::where('use', $userId)
            ->where('status', 1)
            ->where('student', 1)
            ->first();

        if ($decen) {
            $this->parish_id = $decen->pid;
            $this->isAdmin = false;
        }
    }

    public function loadInitialData(): void
    {
        if (!$this->parish_id) {
            $this->namHocs = [];
            $this->khois = [];
            $this->lops = collect();
            return;
        }

        $data = $this->getNamHocs($this->parish_id);
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
        $this->resetPage();
    }

    public function loadKhois()
    {
        if (!$this->selectedNamHoc) {
            $this->khois = [];
            return;
        }

        $this->khois = $this->getKhois($this->selectedNamHoc);
    }

    // public function loadLops(): void
    // {
    //     if (!$this->selectedNamHoc) {
    //         $this->lops = collect();
    //         return;
    //     }

    //     try {
    //         $this->lops = $this->getLopsDetailed(
    //             $this->selectedNamHoc,
    //             $this->selectedKhoi
    //         );

    //         session()->flash('message', 'Đã tải ' . $this->lops->count() . ' lớp học');

    //         // $this->lops->transform(function ($lop) {
    //         //     $lop->slug_url = $this->generateSlugUrl($lop);
    //         //     return $lop;
    //         // });
    //     } catch (\Exception $e) {
    //         Log::error('LopList: Error loading lops', [
    //             'namhoc_id' => $this->selectedNamHoc,
    //             'khoi_id' => $this->selectedKhoi,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         $this->lops = collect();
    //         session()->flash('error', 'Có lỗi xảy ra khi tải danh sách lớp. Vui lòng thử lại.');
    //     }
    // }

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

        // dd(
        //     $this->selectedNamHoc,
        //     $this->selectedKhoi,
        //     $this->search,
        //     $this->parish_id,
        //     $this->isAdmin,
        //     $this->lops ?? 'lops = NULL TRƯỚC KHI XỬ LÝ',
        //     '--- SAU KHI XỬ LÝ SẼ THÊM DƯỚI ---'
        // );

        $lops_ = collect();

        if ($this->selectedNamHoc) {
            $query = $this->getLopsDetailedQuery($this->selectedNamHoc, $this->selectedKhoi);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('symbol', 'like', "%{$this->search}%");
                });
            }

            // ĐÚNG KIỂU: LengthAwarePaginator
            $lops_ = $query->paginate($this->perPage);

            $lops_->getCollection()->transform(function ($lop) {
                $lop->slug_url = $lop->slug?->slug
                    ? url($lop->slug->slug . config('settings.url_prefix', ''))
                    : route('lop.show', $lop->id);

                // ===== TRẢ VỀ MẢNG TÊN GIÁO VIÊN NGAY TẠI ĐÂY =====
                $teacherIds = $lop->teacher;

                // Chuẩn hóa teacherIds thành mảng số nguyên (xử lý mọi trường hợp bẩn)
                if (empty($teacherIds) || in_array($teacherIds, ['', '[]', 'null', null], true)) {
                    $teacherIds = [];
                } elseif (is_string($teacherIds)) {
                    $decoded = json_decode($teacherIds, true);
                    $teacherIds = (json_last_error() === JSON_ERROR_NONE && is_array($decoded))
                        ? $decoded
                        : preg_split('/[\[\]\s,"\']+/', $teacherIds, -1, PREG_SPLIT_NO_EMPTY);
                } elseif (is_numeric($teacherIds)) {
                    $teacherIds = [(int)$teacherIds];
                } elseif (!is_array($teacherIds)) {
                    $teacherIds = [];
                }

                $teacherIds = array_values(array_unique(array_map('intval', array_filter($teacherIds, 'is_numeric'))));

                // Lấy tên giáo viên (chỉ active, giữ đúng thứ tự)
                if (!empty($teacherIds)) {
                    $teacherNames = \App\Models\Teacher::whereIn('id', $teacherIds)
                        ->where('status', 1)
                        ->orderByRaw('FIELD(id, ' . implode(',', $teacherIds) . ')')
                        ->pluck('name')
                        ->toArray();
                } else {
                    $teacherNames = [];
                }

                // TRẢ VỀ MẢNG TÊN + SỐ LƯỢNG – DỄ DÙNG TRONG BLADE
                $lop->teacher_names      = $teacherNames;                    // mảng tên
                $lop->teacher_names_list = implode(', ', $teacherNames);     // chuỗi nối
                $lop->teacher_count      = count($teacherNames);             // số lượng
                $lop->has_teacher        = $lop->teacher_count > 0;          // boolean tiện dùng

                return $lop;
            });
        }

        return view('livewire.lop.lop-list', [
            'lops' => $lops_,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }

    /**
     * ✅ Trả về Query Builder, KHÔNG phải Collection
     */
    private function getLopsDetailedQuery($namHocId, $khoiId = null)
    {
        $query = \App\Models\Lop::query()
            ->where('schoolyear', $namHocId)
            ->where('status', 1)
            ->with(['blockRelation', 'slug'])
            ->withCount('students');

        if (!empty($khoiId)) {
            $query->where('block', $khoiId);
        }

        return $query->orderBy('name', 'asc');
    }

    // private function generateSlugUrl($lop): string
    // {
    //     try {
    //         if ($lop->relationLoaded('slug') && $lop->slug && !empty($lop->slug->keyword)) {
    //             $keyword = $lop->slug->keyword;
    //             $extension = config('settings.url_prefix', '.html');
    //             return url($keyword . $extension);
    //         }

    //         return route('lop.show', $lop->id);
    //     } catch (\Exception $e) {
    //         Log::warning('LopList: Error generating slug URL', [
    //             'lop_id' => $lop->id,
    //             'error' => $e->getMessage()
    //         ]);

    //         return route('lop.show', $lop->id ?? '#');
    //     }
    // }
}
