<?php

namespace App\Http\Livewire\Family;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Family;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use Illuminate\Support\Facades\DB;

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

    // ==================== HEAD SEARCH ====================
    public array $parishionerOptions = [];

    // ==================== DROPDOWN DATA ====================
    public $parishGroups = [];

    // ==================== VALIDATION ====================
    protected array $formRules = [
        'parishGroupId' => 'nullable|integer|exists:parish_groups,id',

        'childrenIds'   => 'array',
        'childrenIds.*' => 'integer|exists:parishioners,id',

        'note'   => 'nullable|string|max:2000',
        'status' => 'boolean',
    ];

    protected $messages = [
        'name.required'        => 'Tên gia đình không được để trống.',
        'name.max'             => 'Tên gia đình không được quá 100 ký tự.',
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

        $members = Parishioner::where('family_id', $family->id)
            ->get(['id', 'gender', 'father_id', 'mother_id']);

        $memberIds = $members->pluck('id');

        // Cha: male, không có father_id trỏ vào thành viên khác trong gia đình
        $father = $members
            ->where('gender', 'male')
            ->filter(fn($m) => !$memberIds->contains($m->father_id))
            ->first();

        // Mẹ: female, không có mother_id trỏ vào thành viên khác trong gia đình  
        $mother = $members
            ->where('gender', 'female')
            ->filter(fn($m) => !$memberIds->contains($m->mother_id))
            ->first();

        $this->fatherId = $father?->id;
        $this->motherId = $mother?->id;

        // Con: member còn lại (không phải cha, không phải mẹ)
        $parentIds = collect([$this->fatherId, $this->motherId])->filter();

        $this->childrenIds = $members
            ->whereNotIn('id', $parentIds)
            ->pluck('id')
            ->toArray();
    }

    protected function loadParishionerOptions(): void
    {
        $query = Parishioner::ofParish($this->parishId);

        if ($this->isEdit) {

            $family = Family::find($this->familyId);

            $allowedIds = Parishioner::where('family_id', $family?->id)
                ->pluck('id');

            $query->where(function ($q) use ($allowedIds) {
                $q->whereNull('family_id')
                    ->orWhereIn('id', $allowedIds);
            });
        } else {

            $query->whereNull('family_id');
        }

        $this->parishionerOptions = $query
            ->with('saint')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get([
                'id',
                'last_name',
                'first_name',
                'gender',
                'birthday',
                'saint_id',
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

            DB::beginTransaction();

            // ===== VALIDATE MEMBERS =====

            $head = Parishioner::ofParish($this->parishId)
                ->findOrFail($this->fatherId);

            $children = Parishioner::ofParish($this->parishId)
                ->whereIn('id', $this->childrenIds)
                ->get();

            // ===== FAMILY NAME =====
            $familyName = 'Gia đình ' . $head->full_name_with_saint;

            // ===== FAMILY DATA =====

            $data = [
                'parish_id'       => $this->parishId,
                'parish_group_id' => $this->parishGroupId,
                'head_id'         => $head->id,
                'name'            => $familyName,
                'note'            => trim($this->note) ?: null,
                'status'          => $this->status,
            ];

            // ===== CREATE / UPDATE =====

            if ($this->isEdit) {

                $family = Family::findOrFail($this->familyId);

                $this->authorize('update', $family);

                $oldMemberIds = Parishioner::where('family_id', $family->id)
                    ->pluck('id');

                $family->update($data);
            } else {
                $this->authorize('create', Family::class);

                $family = Family::create($data);

                $oldMemberIds = collect();
            }

            // ===== NEW MEMBER IDS =====

            $newMemberIds = collect([
                $this->fatherId,
                $this->motherId,
            ])
                ->merge($this->childrenIds)
                ->filter()
                ->unique()
                ->values();

            // ===== REMOVE OLD MEMBERS =====

            Parishioner::whereIn('id', $oldMemberIds)
                ->whereNotIn('id', $newMemberIds)
                ->update([
                    'family_id' => null,
                    'father_id' => null,
                    'mother_id' => null,
                ]);

            // ===== HEAD + SPOUSE =====

            $head->update([
                'family_id' => $family->id,
            ]);

            if ($this->motherId) {

                $mother = Parishioner::ofParish($this->parishId)
                    ->findOrFail($this->motherId);

                $mother->update([
                    'family_id' => $family->id,
                ]);
            }

            // ===== CHILDREN =====

            foreach ($children as $child) {

                $child->update([
                    'family_id' => $family->id,

                    // cha
                    'father_id' => $head->gender === 'male'
                        ? $head->id
                        : (
                            $mother?->gender === 'male'
                            ? $mother->id
                            : null
                        ),

                    // mẹ
                    'mother_id' => $head->gender === 'female'
                        ? $head->id
                        : (
                            $mother?->gender === 'female'
                            ? $mother->id
                            : null
                        ),
                ]);
            }

            // ===== MEMBER COUNT =====

            $family->update([
                'member_count' => $newMemberIds->count(),
            ]);

            DB::commit();

            $this->emit(
                'toast',
                'success',
                $this->isEdit
                    ? 'Đã cập nhật gia đình.'
                    : 'Đã tạo gia đình.'
            );

            $this->redirect(
                route('families.show', $family->id)
            );
        } catch (\Exception $e) {

            DB::rollBack();

            $this->logError($e, 'Error saving family');

            $this->emit(
                'toast',
                'error',
                'Có lỗi khi lưu gia đình.'
            );
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
