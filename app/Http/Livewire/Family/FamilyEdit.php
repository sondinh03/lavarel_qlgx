<?php

namespace App\Http\Livewire\Family;

use App\Actions\Family\FamilyMembershipService;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\Family;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use InvalidArgumentException;

class FamilyEdit extends BaseComponent
{
    // ==================== STATE ====================
    public ?int $familyId = null;
    public bool $isEdit    = false;
    public bool $isLoading = true;

    protected $usePagination = false;

    // ==================== FORM FIELDS ====================
    public string $name          = '';
    public ?int   $parishGroupId = null;
    public ?int $fatherId = null;
    public ?int $motherId = null;
    public ?int $selectedChildId = null;
    public array $childrenIds = [];
    public string $note          = '';
    public bool   $status        = true;
    public string $activeTab     = 'info';

    public ?string $address              = null;
    public ?string $province             = null;
    public ?int    $wardId               = null;
    public ?int    $level                = null;
    public bool    $isTransferred        = false;
    public bool    $isIncludedInStats    = true;

    // ==================== HEAD SEARCH ====================
    public array $parishionerOptions = [];

    // ==================== DROPDOWN DATA ====================
    public $parishGroups = [];

    // ==================== VALIDATION ====================
    protected array $formRules = [
        'name'          => 'nullable|string|max:255',
        'fatherId'      => 'required|integer|exists:parishioners_new,id',
        'parishGroupId' => 'nullable|integer|exists:parish_groups,id',
        'address'       => 'nullable|string|max:255',
        'province'      => 'nullable|string|max:100',
        'wardId'        => 'nullable|integer',
        'level'         => 'nullable|integer',
        'childrenIds'   => 'array',
        'childrenIds.*' => 'integer|exists:parishioners_new,id',
        'note'          => 'nullable|string|max:2000',
        'status'        => 'boolean',
    ];

    protected $messages = [
        'name.required'        => 'Tên gia đình không được để trống.',
        'name.max'             => 'Tên gia đình không được quá 100 ký tự.',
        'fatherId.required'    => 'Vui lòng chọn chủ hộ (chồng).',
        'fatherId.exists'      => 'Giáo dân không tồn tại.',
        'parishGroupId.exists' => 'Giáo họ không tồn tại.',
        'note.max'             => 'Ghi chú không được quá 2000 ký tự.',
    ];

    // ==================== LIFECYCLE ====================
    public function mount(?int $id = null): void
    {
        $this->familyId = $id;
        $this->isEdit   = $this->familyId !== null;
        parent::mount();
        $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        try {
            if ($this->isEdit) {
                $family = Family::findOrFail($this->familyId);
                $this->authorize('update', $family);
            } else {
                $this->authorize('create', Family::class);
            }

            $this->loadDropdownData();
            $this->loadParishionerOptions();

            if ($this->isEdit) {
                $this->mapToForm(Family::with('head')->findOrFail($this->familyId));
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy gia đình.');
            $this->redirect(route('families.index'));
        } catch (\Exception $e) {
            $this->logError($e, 'Failed to load initial data for FamilyEdit');
            $this->emit('toast', 'error', 'Có lỗi khi tải dữ liệu.');
        } finally {
            $this->isLoading = false;
        }
    }

    // ==================== DATA LOADING ====================
    protected function loadDropdownData(): void
    {
        $this->parishGroups = ParishGroup::where('parish_id', $this->parishId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function getFamilyNameProperty(): string
    {
        $head = collect($this->parishionerOptions)
            ->firstWhere('id', $this->fatherId);

        return $head['family_name'] ?? '';
    }

    protected function mapToForm(Family $family): void
    {
        $this->name          = $family->name;
        $this->parishGroupId = $family->parish_group_id;
        $this->note          = $family->note ?? '';
        $this->status        = (bool) $family->status;
        $this->address       = $family->address;
        $this->province      = $family->province;
        $this->wardId        = $family->ward_id;
        $this->level         = $family->level;
        $this->isTransferred     = (bool) ($family->is_transferred ?? false);
        $this->isIncludedInStats = (bool) ($family->is_included_in_stats ?? true);

        $members = Parishioner::where('family_id', $family->id)
            ->get(['id', 'family_role']);

        $husband = $members->firstWhere('family_role', 'husband');
        $wife    = $members->firstWhere('family_role', 'wife');

        $this->fatherId = $husband?->id;
        $this->motherId = $wife?->id;

        $this->childrenIds = $members
            ->where('family_role', 'child')
            ->pluck('id')
            ->toArray();
    }

    protected function loadParishionerOptions(): void
    {
        $allowedIds = $this->isEdit && $this->familyId
            ? Parishioner::where('family_id', $this->familyId)->pluck('id')
            : collect();

        $query = Parishioner::query()
            ->with('saint')
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($allowedIds->isNotEmpty()) {
            $query->where(function ($q) use ($allowedIds) {
                $q->whereNull('family_id')->orWhereIn('id', $allowedIds);
            });
        } else {
            $query->whereNull('family_id');
        }

        $this->parishionerOptions = $query->get([
            'id', 'last_name', 'first_name', 'gender', 'birthday', 'saint_id',
        ])
            ->map(fn($p) => [
                'id'       => $p->id,
                'name'     => $p->full_name_with_saint,
                'gender'   => $p->gender,
                'birthday' => optional($p->birthday)?->format('d/m/Y'),
            ])
            ->toArray();
    }

    protected function getAllMemberIds(): array
    {
        return collect([
            $this->fatherId,
            $this->motherId,
        ])
            ->merge($this->childrenIds)
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    public function addChild(): void
    {
        if (!$this->selectedChildId) {
            return;
        }

        // tránh trùng
        if (in_array($this->selectedChildId, $this->childrenIds)) {

            $this->emit(
                'toast',
                'warning',
                'Người này đã được thêm.'
            );

            return;
        }

        // tránh add cha/mẹ vào con
        if (
            $this->selectedChildId === $this->fatherId ||
            $this->selectedChildId === $this->motherId
        ) {

            $this->emit(
                'toast',
                'warning',
                'Không thể thêm cha/mẹ vào danh sách con.'
            );

            return;
        }

        $this->childrenIds[] = $this->selectedChildId;

        $this->childrenIds = collect($this->childrenIds)
            ->unique()
            ->values()
            ->toArray();

        $this->selectedChildId = null;
    }

    public function removeChild(int $id): void
    {
        $this->childrenIds = collect($this->childrenIds)
            ->reject(fn($childId) => $childId == $id)
            ->values()
            ->toArray();
    }

    // ==================== ACTIONS ====================
    public function save(): void
    {
        $this->validate($this->formRules, $this->messages);

        try {
            $head = Parishioner::query()->findOrFail($this->fatherId);

            $familyName = trim($this->name)
                ?: ('Gia đình ' . $head->full_name_with_saint);

            $data = [
                'parish_id'            => $this->parishId,
                'parish_group_id'      => $this->parishGroupId,
                'name'                 => $familyName,
                'note'                 => trim($this->note) ?: null,
                'status'               => $this->status,
                'address'              => $this->address ?: null,
                'province'             => $this->province ?: null,
                'ward_id'              => $this->wardId,
                'level'                => $this->level,
                'is_transferred'       => $this->isTransferred,
                'is_included_in_stats' => $this->isIncludedInStats,
            ];

            if ($this->isEdit) {
                $family = Family::findOrFail($this->familyId);
                $this->authorize('update', $family);
                $oldMemberIds = Parishioner::where('family_id', $family->id)->pluck('id');
                $family->update($data);
            } else {
                $this->authorize('create', Family::class);
                $family = Family::create($data);
                $oldMemberIds = collect();
            }

            $otherMemberIds = $this->isEdit
                ? Parishioner::where('family_id', $family->id)
                    ->where('family_role', 'other')
                    ->pluck('id')
                    ->toArray()
                : [];

            app(FamilyMembershipService::class)->assignMembers(
                $family,
                $this->fatherId,
                $this->motherId,
                $this->childrenIds,
                $oldMemberIds,
                $otherMemberIds
            );

            $this->emit(
                'toast',
                'success',
                $this->isEdit
                    ? 'Đã cập nhật gia đình.'
                    : 'Đã tạo gia đình.'
            );

            $this->redirect(route('families.show', $family->id));
        } catch (InvalidArgumentException $e) {
            $this->emit('toast', 'warning', $e->getMessage());
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving family');
            $this->emit('toast', 'error', 'Có lỗi khi lưu gia đình.');
        }
    }

    public function switchTab(string $tab): void
    {
        if (in_array($tab, ['info', 'members'], true)) {
            $this->activeTab = $tab;
        }
    }

    public function cancel(): void
    {
        $this->isEdit && $this->familyId
            ? $this->redirect(route('families.show', $this->familyId))
            : $this->redirect(route('families.index'));
    }

    // ==================== RENDER ====================
    public function render()
    {
        return view('livewire.family.family-edit', [
            'isLoading' => $this->isLoading,
        ])
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
