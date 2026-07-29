<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\CatechismClass;
use App\Models\ScoreType;
use App\Models\StudentScore;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Tab "Cấu hình loại điểm": thêm, sửa, bật/tắt, xoá các cột điểm.
 *
 * Danh sách $scoreTypes giữ cả loại đã tắt để quản trị viên bật lại được;
 * bảng điểm tự lọc loại đang hoạt động khi hiển thị.
 */
trait ManagesScoreTypes
{
    /** @var \Illuminate\Support\Collection Loại điểm của lớp hiện tại */
    public $scoreTypes;

    /** @var bool Hiển thị modal cấu hình loại điểm */
    public $showScoreTypeForm = false;

    /** @var int|null ID ScoreType đang edit (null = create) */
    public $editingScoreTypeId = null;

    /** @var string Tên loại điểm */
    public $typeName = '';

    /** @var int|null Loại (1-5) */
    public $scoreTypeType = null;

    /** @var int Thứ tự hiển thị */
    public $typeOrder = 0;

    /** @var float Hệ số */
    public $typeCoefficient = 1.0;

    /** @var float Điểm tối đa */
    public $typeMaxScore = 10.0;

    /** @var bool Trạng thái */
    public $typeIsActive = true;

    /** @var string Phạm vi tạo mới: 'class' | 'grade' | 'parish' */
    public $createScope = 'class';

    /** @var int|null Khối khi createScope = 'grade' */
    public $createScopeGradeId = null;

    protected $scoreTypeRules = [
        'typeName'        => 'required|string|max:100',
        'scoreTypeType'   => 'required|integer|in:1,2,3,4,5',
        'typeOrder'       => 'required|integer|min:0|max:99',
        'typeCoefficient' => 'required|numeric|min:0.1|max:10',
        'typeMaxScore'    => 'required|numeric|min:1|max:100',
        'typeIsActive'    => 'required|boolean',
    ];

    protected function loadScoreTypes(): void
    {
        if (!$this->selectedLop) {
            $this->scoreTypes = collect();
            return;
        }

        try {
            $this->scoreTypes = ScoreType::where('class_id', $this->selectedLop)
                ->where('semester', $this->selectedSemester)
                ->orderBy('order')
                ->orderBy('type')
                ->get();
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading score types');
            $this->scoreTypes = collect();
        }
    }

    /** Loại điểm đang bật — dùng cho bảng điểm và chi tiết học sinh. */
    public function activeScoreTypes(): Collection
    {
        return collect($this->scoreTypes ?? [])
            ->where('is_active', true)
            ->values();
    }

    public function createScoreType(): void
    {
        $this->authorize('create', ScoreType::class);

        if (!$this->selectedNamHoc) {
            $this->emit('toast', 'warning', 'Vui lòng chọn năm học trước');
            return;
        }

        $this->resetScoreTypeForm();

        // Pre-fill scope dựa vào filter đang chọn
        if ($this->selectedLop) {
            $this->createScope        = 'class';
            $this->createScopeGradeId = $this->selectedKhoi;
        } elseif ($this->selectedKhoi) {
            $this->createScope        = 'grade';
            $this->createScopeGradeId = $this->selectedKhoi;
        } else {
            $this->createScope        = 'parish';
            $this->createScopeGradeId = null;
        }

        $this->showScoreTypeForm = true;
    }

    public function editScoreType(int $id): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            $st = ScoreType::findOrFail($id);

            $this->editingScoreTypeId = $st->id;
            $this->typeName           = $st->name;
            $this->scoreTypeType      = $st->type;
            $this->typeOrder          = $st->order;
            $this->typeCoefficient    = $st->coefficient;
            $this->typeMaxScore       = $st->max_score;
            $this->typeIsActive       = $st->is_active;

            $this->resetValidation();
            $this->showScoreTypeForm = true;
        } catch (ModelNotFoundException $e) {
            $this->emit('toast', 'error', 'Không tìm thấy loại điểm này');
        }
    }

    public function saveScoreType(): void
    {
        $this->authorize('create', ScoreType::class);
        $this->validate($this->scoreTypeRules, $this->messages);

        if ($this->editingScoreTypeId) {
            $this->updateExistingScoreType();

            return;
        }

        $this->createScoreTypesForScope();
    }

    /** Edit mode → chỉ update record cụ thể, không cần scope. */
    protected function updateExistingScoreType(): void
    {
        $duplicate = ScoreType::where(
            'class_id',
            ScoreType::find($this->editingScoreTypeId)?->class_id
        )
            ->where('semester', $this->selectedSemester)
            ->where('type', $this->scoreTypeType)
            ->where('name', $this->typeName)
            ->where('id', '!=', $this->editingScoreTypeId)
            ->exists();

        if ($duplicate) {
            $this->addError('scoreTypeType', 'Loại điểm này đã tồn tại trong học kỳ');
            return;
        }

        try {
            ScoreType::where('id', $this->editingScoreTypeId)->update([
                'type'        => $this->scoreTypeType,
                'name'        => $this->typeName,
                'order'       => $this->typeOrder,
                'coefficient' => $this->typeCoefficient,
                'max_score'   => $this->typeMaxScore,
                'is_active'   => $this->typeIsActive,
            ]);

            $this->emit('toast', 'message', 'Cập nhật loại điểm thành công');
            $this->closeScoreTypeForm();
            $this->afterScoreTypesChanged();
        } catch (\Exception $e) {
            $this->logError($e, 'Error updating score type');
            $this->emit('toast', 'error', 'Có lỗi khi cập nhật loại điểm');
        }
    }

    /** Create mode → tạo cùng loại điểm cho mọi lớp trong phạm vi đã chọn. */
    protected function createScoreTypesForScope(): void
    {
        $classIds = $this->resolveCreateTargetClassIds();

        if ($classIds->isEmpty()) {
            $this->emit('toast', 'warning', 'Không tìm thấy lớp nào để áp dụng');
            return;
        }

        try {
            DB::beginTransaction();

            $created = 0;
            $skipped = 0;

            foreach ($classIds as $classId) {
                $duplicate = ScoreType::where('class_id', $classId)
                    ->where('semester', $this->selectedSemester)
                    ->where('type', $this->scoreTypeType)
                    ->where('name', $this->typeName)
                    ->exists();

                if ($duplicate) {
                    $skipped++;
                    continue;
                }

                ScoreType::create([
                    'class_id'    => $classId,
                    'semester'    => $this->selectedSemester,
                    'type'        => $this->scoreTypeType,
                    'name'        => $this->typeName,
                    'order'       => $this->typeOrder,
                    'coefficient' => $this->typeCoefficient,
                    'max_score'   => $this->typeMaxScore,
                    'is_active'   => $this->typeIsActive,
                ]);

                $created++;
            }

            DB::commit();

            $msg = "Đã tạo cho {$created} lớp";
            if ($skipped > 0) {
                $msg .= ", bỏ qua {$skipped} lớp đã tồn tại loại điểm này";
            }

            $this->emit('toast', 'message', $msg);
            $this->closeScoreTypeForm();
            $this->afterScoreTypesChanged();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error saving score type');
            $this->emit('toast', 'error', 'Có lỗi khi lưu loại điểm');
        }
    }

    /**
     * Resolve danh sách class_id cho create mode
     */
    protected function resolveCreateTargetClassIds(): Collection
    {
        if ($this->createScope === 'class' && $this->selectedLop) {
            return collect([$this->selectedLop]);
        }

        if ($this->createScope === 'grade' && !$this->createScopeGradeId) {
            $this->addError('createScopeGradeId', 'Vui lòng chọn khối');
            return collect();
        }

        $query = CatechismClass::where('school_year_id', $this->selectedNamHoc)
            ->where('parish_id', $this->parishId)
            ->active();

        return match ($this->createScope) {
            'grade'  => $query->where('grade_level_id', $this->createScopeGradeId)->pluck('id'),
            'parish' => $query->pluck('id'),
            default  => collect(),
        };
    }

    public function delete(int $id): void
    {
        $this->authorize('delete', ScoreType::class);

        try {
            DB::beginTransaction();

            $hasScores = StudentScore::where('score_type_id', $id)->exists();

            if ($hasScores) {
                $this->emit('toast', 'error', 'Không thể xoá loại điểm đã có dữ liệu điểm');
                return;
            }

            ScoreType::findOrFail($id)->delete();

            DB::commit();

            $this->emit('toast', 'message', 'Đã xoá loại điểm');
            $this->afterScoreTypesChanged();
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->emit('toast', 'error', 'Không tìm thấy loại điểm');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError($e, 'Error deleting score type');
            $this->emit('toast', 'error', 'Có lỗi khi xoá loại điểm');
        }
    }

    public function toggleScoreTypeStatus(int $id): void
    {
        $this->authorize('update', ScoreType::class);

        try {
            $st = ScoreType::findOrFail($id);
            $st->update(['is_active' => !$st->is_active]);

            $this->emit('toast', 'message', $st->is_active ? 'Đã kích hoạt' : 'Đã tắt loại điểm');
            $this->afterScoreTypesChanged();
        } catch (\Exception $e) {
            $this->logError($e, 'Error toggling score type');
            $this->emit('toast', 'error', 'Có lỗi khi thay đổi trạng thái');
        }
    }

    /** Sau khi thêm/sửa/tắt/xoá loại điểm: làm mới cột và tính lại TB học tập. */
    protected function afterScoreTypesChanged(): void
    {
        $this->loadScoreTypes();
        $this->forgetCalculatedScores();
        $this->ensureBreakdownsLoaded(true);
        $this->recalculateRatingStats();
    }

    public function closeScoreTypeForm(): void
    {
        $this->showScoreTypeForm = false;
        $this->resetScoreTypeForm();
        $this->resetValidation();
    }

    protected function resetScoreTypeForm(): void
    {
        $this->editingScoreTypeId = null;
        $this->typeName           = '';
        $this->scoreTypeType      = null;
        $this->typeOrder          = 0;
        $this->typeCoefficient    = 1.0;
        $this->typeMaxScore       = 10.0;
        $this->typeIsActive       = true;
        $this->createScope        = 'class';
        $this->createScopeGradeId = null;
    }
}
