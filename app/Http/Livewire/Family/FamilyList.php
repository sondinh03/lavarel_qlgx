<?php

namespace App\Http\Livewire\Family;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Family;
use App\Models\ParishGroup;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;
use Override;

class FamilyList extends BaseComponent
{
    // ==================== FILTERS ====================
    public string $statusFilter      = '';
    public string $parishGroupFilter = '';

    // ==================== SELECTION ====================
    public array $selectedFamilies = [];
    public bool  $selectAll        = false;

    // ==================== SORT ====================
    protected array $allowedSortFields = ['name', 'status', 'created_at'];
    public string $sortField           = 'name';
    public string $sortDirection       = 'asc';

    // ==================== MODAL (inline create) ====================
    public bool   $showModal = false;
    public ?int   $editingId = null;
    public string $modalName = '';

    // ==================== VALIDATION ====================
    protected $rules = [
        'search'             => 'nullable|string|max:255',
        'perPage'            => 'required|integer|in:10,15,25,50,100',
        'statusFilter'       => 'nullable|in:,0,1',
        'parishGroupFilter'  => 'nullable|integer',
        'selectedFamilies'   => 'nullable|array',
        'selectedFamilies.*' => 'integer',
        'modalName'          => 'required|string|max:100',
    ];

    protected $messages = [
        'search.max'           => 'Tìm kiếm không được quá 255 ký tự.',
        'perPage.in'           => 'Số mục trên trang không hợp lệ.',
        'statusFilter.in'      => 'Trạng thái không hợp lệ.',
        'modalName.required'   => 'Tên gia đình không được để trống.',
        'modalName.max'        => 'Tên gia đình không được quá 100 ký tự.',
    ];

    // ==================== QUERY STRING ====================
    protected function queryString(): array
    {
        return array_merge([
            'statusFilter'      => ['except' => '', 'as' => 'status'],
            'parishGroupFilter' => ['except' => '', 'as' => 'group'],
            'sortField'         => ['except' => 'name', 'as' => 'sort'],
            'sortDirection'     => ['except' => 'asc', 'as' => 'dir'],
        ], parent::queryString());
    }

    // ==================== LISTENERS ====================
    protected $listeners = [
        'refresh'       => 'handleRefresh',
        'familyCreated' => 'handleRefresh',
    ];

    // ==================== LIFECYCLE ====================
    public function mount(): void
    {
        $this->authorize('viewAny', Family::class);
        parent::mount();
        $this->requireParishId();
    }

    #[Override]
    public function loadInitialData(): void {}

    // ==================== PROPERTY UPDATERS ====================
    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        try {
            $this->validateOnly('search');
        } catch (ValidationException $e) {
            $this->search = '';
            $this->emit('toast', 'warning', 'Từ khóa tìm kiếm không hợp lệ.');
        }

        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedParishGroupFilter(): void
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll(bool $value): void
    {
        $this->selectedFamilies = $value
            ? $this->getCurrentFamiliesQuery()->pluck('id')->map(fn($id) => (int) $id)->toArray()
            : [];
    }

    public function updatedSelectedFamilies(): void
    {
        $this->selectedFamilies = array_values(
            array_unique(
                array_map('intval', array_filter($this->selectedFamilies, 'is_numeric'))
            )
        );

        $totalCount    = $this->getCurrentFamiliesQuery()->count();
        $selectedCount = count($this->selectedFamilies);
        $this->selectAll = $totalCount > 0 && $selectedCount >= $totalCount;
    }

    // ==================== SORT ====================
    public function sortBy(string $field): void
    {
        if (!in_array($field, $this->allowedSortFields)) {
            return;
        }

        $this->sortDirection = ($this->sortField === $field && $this->sortDirection === 'asc')
            ? 'desc'
            : 'asc';
        $this->sortField = $field;
        $this->resetPage();
    }

    // ==================== MODAL ====================
    public function create(): void
    {
        $this->authorize('create', Family::class);
        $this->editingId  = null;
        $this->modalName  = '';
        $this->resetValidation('modalName');
        $this->emit('openModal');
    }

    public function edit(int $id): void
    {
        $family = Family::findOrFail($id);
        $this->authorize('update', $family);

        $this->editingId = $id;
        $this->modalName = $family->name;
        $this->resetValidation('modalName');
        $this->emit('openModal');
    }

    public function closeModal(): void
    {
        $this->editingId = null;
        $this->modalName = '';
        $this->resetValidation('modalName');
        $this->emit('closeModal');
    }

    public function save(): void
    {
        $this->validateOnly('modalName');

        try {
            if ($this->editingId) {
                $family = Family::findOrFail($this->editingId);
                $this->authorize('update', $family);
                $family->update(['name' => trim($this->modalName)]);
                $this->emit('toast', 'message', "Đã cập nhật \"{$family->name}\" thành công.");
            } else {
                $this->authorize('create', Family::class);
                $family = Family::create([
                    'parish_id' => $this->parishId,
                    'name'      => trim($this->modalName),
                    'status'    => true,
                ]);
                $this->emit('toast', 'message', "Đã tạo gia đình \"{$family->name}\" thành công.");
            }

            $this->closeModal();
            $this->handleRefresh();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền thực hiện thao tác này.');
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving family (modal)', ['editing_id' => $this->editingId]);
            $this->emit('toast', 'error', 'Có lỗi khi lưu. Vui lòng thử lại.');
        }
    }

    // ==================== DELETE ====================
    public function delete(int $id): void
    {
        try {
            $family = Family::findOrFail($id);
            $this->authorize('delete', $family);

            if ($family->members()->count() > 0) {
                $this->emit('toast', 'warning', 'Không thể xóa gia đình còn thành viên.');
                return;
            }

            $name = $family->name;
            $family->delete();

            $this->emit('toast', 'message', "Đã xóa gia đình \"{$name}\" thành công.");
            $this->handleRefresh();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa gia đình này.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy gia đình.');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting family', ['family_id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa. Vui lòng thử lại.');
        }
    }

    // ==================== BULK ====================
    public function bulkDelete(): void
    {
        if (empty($this->selectedFamilies)) {
            $this->emit('toast', 'warning', 'Vui lòng chọn ít nhất 1 gia đình.');
            return;
        }

        try {
            $this->authorize('deleteAny', Family::class);

            $withMembers = Family::whereIn('id', $this->selectedFamilies)
                ->withCount('members')
                ->get()
                ->filter(fn($f) => $f->members_count > 0);

            if ($withMembers->count() > 0) {
                $names = $withMembers->pluck('name')->join(', ');
                $this->emit('toast', 'warning', "Không thể xóa gia đình còn thành viên: {$names}");
                return;
            }

            $count = Family::whereIn('id', $this->selectedFamilies)->delete();
            $this->emit('toast', 'message', "Đã xóa {$count} gia đình thành công.");
            $this->resetSelection();
            $this->handleRefresh();
        } catch (\Exception $e) {
            $this->logError($e, 'Error bulk deleting families');
            $this->emit('toast', 'error', 'Có lỗi khi xóa. Vui lòng thử lại.');
        }
    }

    // ==================== RESET FILTERS ====================
    public function resetFilters(): void
    {
        if (!$this->search && !$this->statusFilter && !$this->parishGroupFilter) {
            $this->emit('toast', 'warning', 'Không có bộ lọc nào đang được áp dụng.');
            return;
        }

        $this->search            = '';
        $this->statusFilter      = '';
        $this->parishGroupFilter = '';
        $this->resetPage();
        $this->resetSelection();
        $this->emit('toast', 'success', 'Đã đặt lại bộ lọc.');
    }

    // ==================== QUERY HELPERS ====================
    protected function getCurrentFamiliesQuery()
    {
        $query = Family::with(['parishGroup', 'head'])
            ->withCount('members')
            ->ofParish($this->parishId);

        if ($this->statusFilter !== '') {
            $query->where('status', (bool) $this->statusFilter);
        }

        if ($this->parishGroupFilter) {
            $query->ofParishGroup((int) $this->parishGroupFilter);
        }

        if (!empty(trim($this->search))) {
            $search = trim($this->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhereHas('head', function ($q2) use ($search) {
                        $q2->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        if (in_array($this->sortField, $this->allowedSortFields)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        return $query;
    }

    protected function getFamiliesPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getCurrentFamiliesQuery()->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading family list');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách gia đình.');
            return new LengthAwarePaginator([], 0, $this->perPage, $this->page ?? 1);
        }
    }

    // ==================== STATS ====================
    protected function getStats(): array
    {
        try {
            $base = Family::ofParish($this->parishId);
            return [
                'total'    => (clone $base)->count(),
                'active'   => (clone $base)->active()->count(),
                'inactive' => (clone $base)->where('status', false)->count(),
            ];
        } catch (\Exception $e) {
            $this->logError($e, 'Error getting family stats');
            return ['total' => 0, 'active' => 0, 'inactive' => 0];
        }
    }

    // ==================== HELPERS ====================
    public function handleRefresh(): void
    {
        $this->resetPage();
    }

    protected function resetSelection(): void
    {
        $this->selectedFamilies = [];
        $this->selectAll        = false;
    }

    // ==================== RENDER ====================
    public function render()
    {
        $families     = $this->getFamiliesPaginated();
        $stats        = $this->getStats();
        $parishGroups = ParishGroup::where('parish_id', $this->parishId)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.family.family-list', [
            'families'     => $families,
            'stats'        => $stats,
            'parishGroups' => $parishGroups,
        ])
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
