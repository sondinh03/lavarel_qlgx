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
    public string $activeTab  = 'members';

    protected $usePagination = false;

    // ==================== CACHE ====================
    private ?Family $cachedFamily = null;

    // ==================== ADD MEMBER MODAL ====================
    public bool   $showAddMemberModal    = false;
    public array  $selectedParishioners  = [];
    public bool   $selectAllParishioners = false;
    public string $memberSearch          = '';

    // ==================== SET ROLE MODAL ====================
    public bool   $showRoleModal    = false;
    public ?int   $roleParishionerId = null;
    public string $roleValue         = '';
    public string $roleMemberName    = '';

    // ==================== REMOVE MODAL ====================
    public bool   $showRemoveModal    = false;
    public ?int   $removingMemberId   = null;
    public string $removingMemberName = '';

    // ==================== QUERY STRING ====================
    protected function queryString(): array
    {
        return [
            'activeTab' => ['except' => 'members', 'as' => 'tab'],
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
                'head.saint',
                'members' => fn($q) => $q->with(['saint'])
                    ->orderByRaw("FIELD(family_role, 'husband', 'wife', 'child', 'other')")
                    ->orderBy('birth_order')
                    ->orderBy('birthday'),
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
        $members = $family->members;

        $husband  = $members->firstWhere('family_role', 'husband');
        $wife     = $members->firstWhere('family_role', 'wife');
        $children = $members
            ->filter(fn($m) => $m->family_role === 'child')
            ->sortBy('birth_order')
            ->values();
        $others   = $members
            ->filter(fn($m) => !in_array($m->family_role, ['husband', 'wife', 'child']))
            ->values();

        return [
            'id'        => $family->id,
            'name'      => $family->name,
            'parish_id' => $family->parish_id,

            'status'       => (bool) $family->status,
            'status_label' => $family->status ? 'Hoạt động' : 'Không hoạt động',
            'status_badge' => $family->status
                ? 'bg-emerald-100 text-emerald-700'
                : 'bg-slate-100 text-slate-500',

            'parish_name'       => $family->parish->name ?? '',
            'parish_group_name' => $family->parishGroup->name ?? '',
            'note'              => $family->note ?? '',

            'address'              => $family->address ?? '',
            'province'             => $family->province ?? '',
            'ward_id'              => $family->ward_id,
            'level'                => $family->level,
            'level_label'          => config('family.level.' . $family->level, $family->level ? (string) $family->level : ''),
            'is_transferred'       => (bool) ($family->is_transferred ?? false),
            'is_included_in_stats' => (bool) ($family->is_included_in_stats ?? true),

            'head' => $family->head ? [
                'id'   => $family->head->id,
                'name' => $family->head->full_name_with_saint,
                'url'  => route('parishioners.show', $family->head->id),
            ] : null,

            'member_count' => $members->count(),

            // Vai trò rõ ràng
            'husband'  => $husband  ? $this->mapMember($husband,  'Cha') : null,
            'wife'     => $wife     ? $this->mapMember($wife,     'Mẹ')    : null,
            'children' => $children->map(function ($child, $index) {
                $order = $child->birth_order ?? ($index + 1);
                $label = $order === 1 ? 'Con đầu' : 'Con thứ ' . $order;
                return $this->mapMember($child, $label);
            })->toArray(),
            'others' => $others->map(
                fn($m) => $this->mapMember($m, 'Thành viên')
            )->toArray(),

            // Flat list (dùng cho @php $knownIds trong blade)
            'members' => $members->map(
                fn($m) => $this->mapMember($m, $this->resolveRoleLabel($m))
            )->toArray(),

            'created_at' => $family->created_at?->format('d/m/Y H:i') ?? '',
            'updated_at' => $family->updated_at?->format('d/m/Y H:i') ?? '',
        ];
    }

    private function mapMember(Parishioner $m, string $role): array
    {
        return [
            'id'          => $m->id,
            'role'        => $role,
            'family_role' => $m->family_role ?? '',
            'name'        => trim(($m->last_name ?? '') . ' ' . ($m->first_name ?? '')),
            'full_name'   => trim(
                ($m->saint?->name ?? '') . ' ' .
                    ($m->last_name ?? '') . ' ' .
                    ($m->first_name ?? '')
            ),
            'saint_name'  => $m->saint?->name ?? '',
            'gender'      => $m->gender === 'male' ? 'Nam' : 'Nữ',
            'gender_raw'  => $m->gender,
            'birthday'    => $m->birthday?->format('d/m/Y') ?? '',
            'age'         => $m->birthday ? (int) $m->birthday->age : null,
            'phone'       => $m->phone ?? '',
            'avatar'      => $m->avatar_path ?? '',
            'birth_order' => $m->birth_order,
            'status'      => (bool) ($m->is_active ?? true),
            'url'         => route('parishioners.show', $m->id),
            'initials'    => strtoupper(
                mb_substr($m->last_name ?? '', 0, 1) .
                    mb_substr($m->first_name ?? '', 0, 1)
            ),
        ];
    }

    private function resolveRoleLabel(Parishioner $m): string
    {
        return match ($m->family_role) {
            'husband' => 'Chồng',
            'wife'    => 'Vợ',
            'child'   => $m->birth_order ? 'Con thứ ' . $m->birth_order : 'Con',
            default   => 'Thành viên',
        };
    }

    // ==================== TABS ====================
    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['info', 'members'])) {
            $this->activeTab = $tab;
        }
    }

    // ==================== SET ROLE ====================
    public function openRoleModal(int $parishionerId, string $name, string $currentRole): void
    {
        $this->authorize('update', $this->getFamily());
        $this->roleParishionerId = $parishionerId;
        $this->roleMemberName    = $name;
        $this->roleValue         = $currentRole;
        $this->showRoleModal     = true;
    }

    public function saveRole(): void
    {
        $this->authorize('update', $this->getFamily());

        if (!in_array($this->roleValue, ['husband', 'wife', 'child', 'other'])) {
            $this->emit('toast', 'warning', 'Vai trò không hợp lệ.');
            return;
        }

        try {
            $parishioner = Parishioner::where('family_id', $this->familyId)
                ->findOrFail($this->roleParishionerId);

            // Nếu gán husband/wife → kiểm tra đã có người giữ vai đó chưa
            if (in_array($this->roleValue, ['husband', 'wife'])) {
                $exists = Parishioner::where('family_id', $this->familyId)
                    ->where('family_role', $this->roleValue)
                    ->where('id', '!=', $parishioner->id)
                    ->exists();

                if ($exists) {
                    $label = $this->roleValue === 'husband' ? 'chồng' : 'vợ';
                    $this->emit('toast', 'warning', "Gia đình đã có người giữ vai trò {$label}.");
                    return;
                }
            }

            $parishioner->update(['family_role' => $this->roleValue]);

            $this->emit('toast', 'message', "Đã cập nhật vai trò của {$this->roleMemberName}.");
            $this->closeRoleModal();
            $this->loadFamilyData();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy thành viên.');
            $this->closeRoleModal();
        } catch (\Exception $e) {
            $this->logError($e, 'Error updating member role', [
                'family_id'      => $this->familyId,
                'parishioner_id' => $this->roleParishionerId,
            ]);
            $this->emit('toast', 'error', 'Có lỗi khi cập nhật vai trò.');
        }
    }

    public function closeRoleModal(): void
    {
        $this->showRoleModal     = false;
        $this->roleParishionerId = null;
        $this->roleMemberName    = '';
        $this->roleValue         = '';
    }

    // ==================== ADD MEMBER MODAL ====================
    public function openAddMemberModal(): void
    {
        $this->authorize('update', $this->getFamily());
        $this->resetAddMemberForm();
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

            $parishioner->update([
                'family_id'   => null,
                'family_role' => null,
                'father_id'   => null,
                'mother_id'   => null,
            ]);

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
                $this->emit('toast', 'warning', 'Không thể xóa gia đình còn thành viên.');
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
        $query = Parishioner::whereNull('family_id');

        if (trim($this->memberSearch)) {
            $search = trim($this->memberSearch);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(last_name, ' ', first_name) LIKE ?", ["%{$search}%"]);
            });
        }

        return $query->orderBy('last_name')->orderBy('first_name');
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
