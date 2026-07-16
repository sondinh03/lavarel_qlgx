<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parishioner;
use App\Models\StudentNew;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ParishionersManager extends BaseComponent
{
    public string $selectedGender   = '';
    public string $selectedAgeGroup = '';
    public string $selectedMarried  = '';
    public string $selectedStatus   = '';
    public string $selectedGroup    = '';
    public string $selectedAssociation = '';
    public string $selectedDeceased = '';

    public $showAdvancedFilters = false;

    public array $ageGroups = [
        '0-12'  => 'Thiếu nhi (0-12)',
        '13-18' => 'Thiếu niên (13-18)',
        '19-35' => 'Thanh niên (19-35)',
        '36-60' => 'Trung niên (36-60)',
        '60+'   => 'Cao niên (60+)',
    ];

    private ?LengthAwarePaginator $_parishionersCache = null;

    /** @var array<int, string> */
    public array $parishGroups = [];

    /** @var array<int, string> */
    public array $associations = [];

    protected function queryString(): array
    {
        return array_merge(parent::queryString(), [
            'selectedGender'   => ['except' => '', 'as' => 'gender'],
            'selectedAgeGroup' => ['except' => '', 'as' => 'age'],
            'selectedMarried'  => ['except' => '', 'as' => 'married'],
            'selectedStatus'   => ['except' => '', 'as' => 'status'],
            'selectedGroup'    => ['except' => '', 'as' => 'group'],
            'selectedAssociation' => ['except' => '', 'as' => 'association'],
            'selectedDeceased' => ['except' => '', 'as' => 'deceased'],
        ]);
    }

    protected $listeners = ['refresh' => '$refresh'];

    public function mount(): void
    {
        $this->authorize('viewAny', Parishioner::class);
        parent::mount();
        $this->requireParishId();
        $this->parishGroups = $this->loadParishGroups();
        $this->associations = $this->loadAssociations();
    }

    protected function loadInitialData(): void {}

    private function loadParishGroups(): array
    {
        if (!$this->parishId) {
            return [];
        }

        return DB::table('parish_groups')
            ->where('parish_id', $this->parishId)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function loadAssociations(): array
    {
        if (! $this->parishId) {
            return [];
        }

        return DB::table('associations')
            ->where('pid', $this->parishId)
            ->where('status', 1)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function sanitizeQueryString(): void
    {
        parent::sanitizeQueryString();

        if (!in_array($this->selectedGender, ['male', 'female', ''], true)) {
            $this->selectedGender = '';
        }
        if (!in_array($this->selectedMarried, ['0', '1', '2', '3', ''], true)) {
            $this->selectedMarried = '';
        }
        if (!in_array($this->selectedStatus, ['0', '1', ''], true)) {
            $this->selectedStatus = '';
        }
        if (!in_array($this->selectedDeceased, ['0', '1', ''], true)) {
            $this->selectedDeceased = '0';
        }
        if (!array_key_exists($this->selectedAgeGroup, $this->ageGroups) && $this->selectedAgeGroup !== '') {
            $this->selectedAgeGroup = '';
        }
    }

    public function updatedSelectedGender(): void { $this->resetPage(); }
    public function updatedSelectedAgeGroup(): void { $this->resetPage(); }
    public function updatedSelectedMarried(): void { $this->resetPage(); }
    public function updatedSelectedStatus(): void { $this->resetPage(); }
    public function updatedSelectedGroup(): void { $this->resetPage(); }
    public function updatedSelectedDeceased(): void { $this->resetPage(); }

    public function toggleStatus(int $id): void
    {
        try {
            $p = Parishioner::query()->findOrFail($id);
            $this->authorize('update', $p);
            $p->update(['status' => !$p->status]);
            $this->emit('toast', 'message', $p->status ? 'Đã kích hoạt' : 'Đã tắt');
        } catch (ModelNotFoundException) {
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling status', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    public function delete(int $id): void
    {
        try {
            DB::beginTransaction();

            $p = Parishioner::query()->findOrFail($id);
            $this->authorize('delete', $p);

            if (StudentNew::where('parishioner_id', $p->id)->exists()) {
                DB::rollBack();
                $this->emit('toast', 'error', 'Không thể xóa — giáo dân đang có học sinh liên kết');
                return;
            }

            if ($p->avatar_path) {
                delete_stored_media($p->avatar_path);
            }

            $p->delete();
            DB::commit();

            $this->emit('toast', 'message', 'Đã xóa giáo dân thành công');
        } catch (ModelNotFoundException) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy giáo dân');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting parishioner', ['id' => $id]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa');
        }
    }

    public function resetFilters(): void
    {
        $this->selectedGender   = '';
        $this->selectedAgeGroup = '';
        $this->selectedMarried  = '';
        $this->selectedStatus   = '';
        $this->selectedGroup    = '';
        $this->selectedAssociation = '';
        $this->selectedDeceased = '0';
        $this->search           = '';
        $this->resetPage();
        $this->emit('toast', 'message', 'Đã đặt lại bộ lọc');
    }

    private function getParishioners(): LengthAwarePaginator
    {
        try {
            $query = Parishioner::query()
                ->with(['saint', 'parishGroup', 'association', 'student']);

            if ($this->selectedDeceased === '1') {
                $query->deceased();
            } elseif ($this->selectedDeceased === '0' || $this->selectedDeceased === '') {
                $query->alive();
            }

            if ($this->selectedGender !== '') {
                $query->byGender($this->selectedGender);
            }
            if ($this->selectedMarried !== '') {
                $query->byMarriedStatus((int) $this->selectedMarried);
            }
            if ($this->selectedStatus !== '') {
                $query->where('status', (bool) $this->selectedStatus);
            }
            if ($this->selectedGroup !== '') {
                $query->ofParishGroup((int) $this->selectedGroup);
            }
            if ($this->selectedAssociation !== '') {
                $query->ofAssociation((int) $this->selectedAssociation);
            }
            if ($this->selectedAgeGroup !== '') {
                [$min, $max] = str_contains($this->selectedAgeGroup, '+')
                    ? [(int) $this->selectedAgeGroup, null]
                    : explode('-', $this->selectedAgeGroup);
                $query->byAgeRange((int) $min, $max ? (int) $max : null);
            }
            if (!empty(trim($this->search))) {
                $query->search($this->search);
            }

            return $query
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->paginate($this->perPage);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading parishioners');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách');
            return new LengthAwarePaginator([], 0, $this->perPage);
        }
    }

    public function getParishionersProperty(): LengthAwarePaginator
    {
        return $this->_parishionersCache ??= $this->getParishioners();
    }

    public function render()
    {
        return view('livewire.parishioners.parishioners-manager', [
            'parishGroups' => $this->parishGroups,
            'associations' => $this->associations,
        ])->extends('frontend.layout.parishioner')->section('content');
    }
}
