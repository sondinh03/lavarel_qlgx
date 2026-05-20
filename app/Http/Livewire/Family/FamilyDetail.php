<?php

namespace App\Http\Livewire\Family;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Family;
use App\Models\Parishioner;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class FamilyDetail extends BaseComponent
{
    // ==================== STATE ====================
    public int    $familyId   = 0;
    public array  $familyData = [];
    public bool   $isLoading  = true;
    public string $activeTab  = 'info';

    protected $usePagination = false;

    // ==================== CACHE ====================
    private ?Family $cachedFamily = null;

    // ==================== ADD MEMBER MODAL ====================
    public bool   $showAddMemberModal    = false;
    public array  $selectedParishioners  = [];
    public bool   $selectAllParishioners = false;
    public string $memberSearch          = '';

    // ==================== REMOVE MODAL ====================
    public bool   $showRemoveModal    = false;
    public ?int   $removingMemberId   = null;
    public string $removingMemberName = '';

    // ==================== QUERY STRING ====================
    protected function queryString(): array
    {
        return [
            'activeTab' => ['except' => 'info', 'as' => 'tab'],
        ];
    }

    // ==================== LISTENERS ====================
    protected $listeners = [
        'refresh'       => 'loadFamilyData',
        'familyUpdated' => 'loadFamilyData',
    ];

    // ==================== LIFECYCLE ====================
    public function mount($id = null): void
    {
        $this->familyId = (int) $id;
        parent::mount();
    }

    protected function loadInitialData(): void
    {
        $this->loadFamilyData();
    }

    // ==================== DATA LOADING ====================
    private function getFamily(): Family
    {
        return $this->cachedFamily ??= Family::findOrFail($this->familyId);
    }

    public function loadFamilyData(): void
    {
        try {
            $this->isLoading    = true;
            $this->cachedFamily = null;

            $family = $this->getFamily();
            $family->load([
                'parish',
                'parishGroup',
                'head',
                'members' => fn($q) => $q->with(['saint'])
                    ->orderBy('last_name')
                    ->orderBy('first_name'),
            ]);

            $this->authorize('view', $family);
            $this->familyData = $this->mapFamilyData($family);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, 'Bạn không có quyền xem gia đình này.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy gia đình.');
            $this->redirect(route('families.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load family data', ['family_id' => $this->familyId]);
            $this->emit('toast', 'error', 'Có lỗi xảy ra khi tải thông tin gia đình.');
        } finally {
            $this->isLoading = false;
        }
    }

    protected function mapFamilyData(Family $family): array
    {
        return [
            'id'        => $family->id,
            'name'      => $family->name,
            'parish_id' => $family->parish_id, // cần cho getAvailableParishionersQuery
            'status'       => (bool) $family->status,
            'status_label' => $family->status ? 'Hoạt động' : 'Không hoạt động',
            'status_badge' => $family->status
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-slate-100 text-slate-500',

            'parish_name'       => $family->parish->name ?? '',
            'parish_group_name' => $family->parishGroup->name ?? '',
            'note'              => $family->note ?? '',

            'head_id'   => $family->head_id,
            'head_name' => $family->head
                ? trim(($family->head->last_name ?? '') . ' ' . ($family->head->first_name ?? ''))
                : '',
            'head_url' => $family->head_id
                ? route('parishioners.show', $family->head_id)
                : null,

            'member_count' => $family->members->count(),
            'members'      => $family->members->map(fn($m) => [
                'id'         => $m->id,
                'name'       => trim(($m->last_name ?? '') . ' ' . ($m->first_name ?? '')),
                'saint_name' => $m->saint->name ?? '',
                'birthday'   => $m->birthday?->format('d/m/Y') ?? '',
                'phone'      => $m->phone ?? '',
                'gender'     => $m->gender === 'male' ? 'Nam' : 'Nữ',
                'avatar'     => $m->avatar_path ?? '',
                'url'        => route('parishioners.show', $m->id),
                'is_head'    => $family->head_id === $m->id,
                'initials'   => strtoupper(
                    mb_substr($m->last_name ?? '', 0, 1) .
                    mb_substr($m->first_name ?? '', 0, 1)
                ),
            ])->toArray(),

            'created_at' => $family->created_at?->format('d/m/Y H:i') ?? '',
            'updated_at' => $family->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    // ==================== TABS ====================
    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['info', 'members'])) {
            $this->activeTab = $tab;
        }
    }

    // ==================== ADD MEMBER MODAL ====================
    public function openAddMemberModal(): void
    {
        $this->authorize('update', $this->getFamily());
        $this->resetAddMemberForm();

        // Chỉ dùng emit — blade lắng nghe qua Livewire.on('openModal')
        // KHÔNG set $this->showAddMemberModal để tránh xung đột với @entangle trong blade
        $this->emit('openModal');
    }

    public function closeAddMemberModal(): void
    {
        $this->emit('closeModal');
        $this->resetAddMemberForm();
    }

    private function resetAddMemberForm(): void
    {
        $this->selectedParishioners  = [];
        $this->selectAllParishioners = false;
        $this->memberSearch          = '';
        $this->resetPage('member_page');
    }

    public function updatedMemberSearch(): void
    {
        $this->memberSearch          = trim($this->memberSearch);
        $this->selectedParishioners  = [];
        $this->selectAllParishioners = false;
        $this->resetPage('member_page');
    }

    public function updatedSelectAllParishioners(bool $value): void
    {
        $this->selectedParishioners = $value
            ? $this->getAvailableParishionersQuery()->pluck('id')->toArray()
            : [];
    }

    public function addMembers(): void
    {
        $this->authorize('update', $this->getFamily());

        if (empty($this->selectedParishioners)) {
            $this->emit('toast', 'warning', 'Vui lòng chọn ít nhất 1 giáo dân.');
            return;
        }

        try {
            DB::beginTransaction();

            $count = Parishioner::whereIn('id', $this->selectedParishioners)
                ->whereNull('family_id')
                ->update(['family_id' => $this->familyId]);

            DB::commit();

            $this->emit('toast', 'message', "Đã thêm {$count} thành viên vào gia đình.");
            $this->closeAddMemberModal();
            $this->loadFamilyData();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error adding members to family', [
                'family_id'             => $this->familyId,
                'selected_parishioners' => $this->selectedParishioners,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi thêm thành viên. Vui lòng thử lại.');
        }
    }

    // ==================== SET HEAD ====================
    public function setAsHead(int $parishionerId): void
    {
        $this->authorize('update', $this->getFamily());

        try {
            $parishioner = Parishioner::where('family_id', $this->familyId)
                ->findOrFail($parishionerId);

            $this->getFamily()->update(['head_id' => $parishioner->id]);

            $name = trim(($parishioner->last_name ?? '') . ' ' . ($parishioner->first_name ?? ''));
            $this->emit('toast', 'message', "Đã đặt {$name} làm chủ hộ.");
            $this->loadFamilyData();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy thành viên.');
        } catch (\Exception $e) {
            $this->logError($e, 'Error setting family head', [
                'family_id'      => $this->familyId,
                'parishioner_id' => $parishionerId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi cập nhật chủ hộ.');
        }
    }

    // ==================== REMOVE MEMBER ====================
    public function confirmRemoveMember(int $parishionerId, string $name): void
    {
        $this->authorize('update', $this->getFamily());
        $this->removingMemberId   = $parishionerId;
        $this->removingMemberName = $name;
        $this->showRemoveModal    = true;
    }

    public function removeMember(): void
    {
        if (!$this->removingMemberId) {
            return;
        }

        $this->authorize('update', $this->getFamily());

        try {
            $parishioner = Parishioner::where('family_id', $this->familyId)
                ->findOrFail($this->removingMemberId);

            if ($this->getFamily()->head_id === $parishioner->id) {
                $this->getFamily()->update(['head_id' => null]);
                $this->cachedFamily = null;
            }

            $parishioner->update(['family_id' => null]);

            $this->emit('toast', 'message', "Đã xóa {$this->removingMemberName} khỏi gia đình.");
            $this->closeRemoveModal();
            $this->loadFamilyData();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy thành viên.');
            $this->closeRemoveModal();
        } catch (\Exception $e) {
            $this->logError($e, 'Error removing member', [
                'family_id'      => $this->familyId,
                'parishioner_id' => $this->removingMemberId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa thành viên. Vui lòng thử lại.');
        }
    }

    public function closeRemoveModal(): void
    {
        $this->showRemoveModal    = false;
        $this->removingMemberId   = null;
        $this->removingMemberName = '';
    }

    // ==================== DELETE FAMILY ====================
    public function deleteFamily(): void
    {
        $this->authorize('delete', $this->getFamily());

        try {
            if ($this->getFamily()->members()->count() > 0) {
                $this->emit('toast', 'warning', 'Không thể xóa gia đình còn thành viên. Vui lòng xóa thành viên trước.');
                return;
            }

            $name = $this->getFamily()->name;
            $this->getFamily()->delete();

            $this->emit('toast', 'message', "Đã xóa gia đình \"{$name}\" thành công.");
            $this->redirect(route('families.index'));
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->emit('toast', 'error', 'Bạn không có quyền xóa gia đình này.');
        } catch (\Exception $e) {
            $this->logError($e, 'Error deleting family', ['family_id' => $this->familyId]);
            $this->emit('toast', 'error', 'Có lỗi khi xóa gia đình. Vui lòng thử lại.');
        }
    }

    // ==================== QUERY HELPERS ====================
    protected function getAvailableParishionersQuery()
    {
        $parishId = $this->familyData['parish_id'] ?? $this->parishId;

        return Parishioner::ofParish($parishId)
            ->whereNull('family_id')
            ->when(trim($this->memberSearch), function ($q, $search) {
                $q->where(function ($q2) use ($search) {
                    $q2->where('first_name', 'like', "%{$search}%")
                       ->orWhere('last_name', 'like', "%{$search}%")
                       ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name');
    }

    protected function getAvailableParishionersPaginated(): LengthAwarePaginator
    {
        try {
            return $this->getAvailableParishionersQuery()
                ->paginate(15, ['*'], 'member_page');
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading available parishioners');
            return new LengthAwarePaginator([], 0, 15, 1);
        }
    }

    // ==================== RENDER ====================
    public function render()
    {
        $availableParishioners = $this->showAddMemberModal
            ? $this->getAvailableParishionersPaginated()
            : null;

        return view('livewire.family.family-detail', [
            'family'                => $this->familyData,
            'isLoading'             => $this->isLoading,
            'availableParishioners' => $availableParishioners,
            'familyModel'           => $this->isLoading ? null : $this->getFamily(),
        ])
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}