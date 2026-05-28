<?php

namespace App\Http\Livewire\Student;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\CatechismClass;
use App\Models\ParishNew;
use App\Models\StudentNew;

/**
 * Component in thẻ học sinh
 *
 * Nhận danh sách student qua query string:
 *   - ?ids=1,2,3,4        → in các student cụ thể
 *   - ?classId=5          → in cả lớp
 *   - ?classId=5&ids=1,2  → ids ưu tiên nếu có cả hai
 *
 * Route: GET /print-cards
 */
class PrintCards extends BaseComponent
{
    // ==================== QUERY STRING INPUT ====================

    /** @var string|null Danh sách IDs, dạng "1,2,3" */
    public ?string $ids = null;

    /** @var int|null Lớp học — dùng khi in cả lớp */
    public ?int $classId = null;

    /**
     * Loại thẻ: permanent = không lớp/năm (dùng nhiều năm), annual = có lớp + năm học.
     */
    public string $cardType = 'permanent';

    /** Tên giáo xứ (ParishNew) hiển thị trên thẻ */
    public string $parishName = '';

    // ==================== DATA ====================

    /** @var \Illuminate\Support\Collection */
    public $students;

    /** @var \App\Models\CatechismClass|null */
    public $lop = null;

    // ==================== LIFECYCLE ====================

    public function mount(): void
    {
        $this->students = collect();

        parent::mount();
        // $this->requireParishId();
    }

    protected function loadInitialData(): void
    {
        if ($this->classId) {
            $this->lop = CatechismClass::with(['schoolYear', 'gradeLevel'])
                ->find($this->classId);
        }

        $this->loadStudents();
        $this->resolveParishName();
    }

    protected function queryString(): array
    {
        return array_merge([
            'ids'      => ['except' => null],
            'classId'  => ['except' => null],
            'cardType' => ['except' => 'permanent'],
        ], parent::queryString());
    }

    public function updatedCardType(string $value): void
    {
        if (!in_array($value, ['permanent', 'annual'], true)) {
            $this->cardType = 'permanent';
        }
    }

    // ==================== DATA LOADING ====================

    protected function loadStudents(): void
    {
        $query = StudentNew::with(['saint', 'parishGroup'])
            ->where('is_active', true)
            ->orderBy('last_name')
            ->orderBy('first_name');

        if ($this->ids) {
            // Ưu tiên: danh sách IDs cụ thể
            $idList = collect(explode(',', $this->ids))
                ->map(fn($id) => (int) trim($id))
                ->filter(fn($id) => $id > 0)
                ->unique()
                ->toArray();

            if (empty($idList)) {
                $this->students = collect();
                return;
            }

            $query->whereIn('id', $idList);
        } elseif ($this->classId) {
            // Fallback: tất cả học sinh trong lớp
            $query->whereHas('classes', fn($q) => $q->where('classes.id', $this->classId));
        } else {
            $this->students = collect();
            session()->flash('error', 'Không có học sinh nào để in');
            return;
        }

        $this->students = $query->get([
            'id',
            'first_name',
            'last_name',
            'student_code',
            'birthday',
            'gender',
            'saint_id',
            'parish_id',
            'parish_group_id',
            'avatar_path',
            'qr_token',
        ]);
    }

    protected function resolveParishName(): void
    {
        $parishId = $this->parishId;

        if (!$parishId && $this->lop?->parish_id) {
            $parishId = (int) $this->lop->parish_id;
        }

        if (!$parishId && $this->students->isNotEmpty()) {
            $parishId = (int) $this->students->first()->parish_id;
        }

        $this->parishName = $parishId
            ? (ParishNew::query()->whereKey($parishId)->value('name') ?? '')
            : '';
    }

    // ==================== ACTIONS ====================

    public function printCards(): void
    {
        if ($this->students->isEmpty()) {
            session()->flash('warning', 'Không có học sinh nào để in nhé!');
            return;
        }

        $this->dispatchBrowserEvent('trigger-print');
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.student.print-cards', [
            'students'   => $this->students,
            'lop'        => $this->lop,
            'cardType'   => $this->cardType,
            'parishName' => $this->parishName,
        ])
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
