<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\CatechismClass;
use App\Models\StudentScore;
use App\Services\ScoreEntryWriter;

/**
 * Nhập và lưu điểm trên bảng điểm.
 *
 * Người dùng sửa nhiều ô rồi bấm Lưu một lần, nên điểm đang nhập được giữ ở
 * $draftScores và chỉ so với $scoresMatrix (giá trị trong database) khi lưu.
 */
trait ManagesScoreEntry
{
    /** @var array Ma trận điểm [student_class_id => [score_type_id => [...]] ] */
    public $scoresMatrix = [];

    /** @var array Draft điểm [student_class_id => [score_type_id => value]] */
    public $draftScores = [];

    /** @var bool Có thay đổi chưa lưu */
    public $hasDraft = false;

    /** @var bool Đã load matrix điểm cho lớp/kỳ hiện tại (public để Livewire giữ qua request) */
    public bool $scoresLoaded = false;

    /** GLV: đang xem chi tiết điểm của học sinh (students_class.id) */
    public ?int $viewingPivotId = null;

    public function getScoreValue(int $studentClassId, int $scoreTypeId): ?float
    {
        return $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
    }

    public function openStudentScoreDetail(int $pivotId): void
    {
        $this->viewingPivotId = $pivotId;
    }

    public function closeStudentScoreDetail(): void
    {
        $this->viewingPivotId = null;
    }

    /** Đổi lớp/kỳ là đổi ngữ cảnh: bỏ điểm đã nạp, draft và TB đã tính của lớp cũ. */
    protected function resetScoreContext(): void
    {
        $this->forgetCalculatedScores();
        $this->scoresMatrix   = [];
        $this->averages       = [];
        $this->draftScores    = [];
        $this->hasDraft       = false;
        $this->scoresLoaded   = false;
        $this->viewingPivotId = null;
        $this->resetPage();
    }

    public function updatedDraftScores(): void
    {
        if (! $this->canEditScores) {
            $this->hasDraft = false;

            return;
        }

        $this->hasDraft = $this->hasAnyDraftChange();
    }

    protected function hasAnyDraftChange(): bool
    {
        foreach ($this->draftScores as $studentClassId => $types) {
            foreach ($types as $scoreTypeId => $value) {
                $original = $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? null;
                $draft    = ($value === '' || $value === null) ? null : (float) $value;

                if ($draft !== $original) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Bỏ điểm đang nhập rồi áp dụng thay đổi bộ lọc mà người dùng đã xác nhận. */
    public function confirmDiscard(string $action, $value): void
    {
        $this->resetScoreContext();

        match ($action) {
            'changeLop'      => $this->selectedLop = $this->toInt($value),
            'changeSemester' => $this->selectedSemester = $this->normalizeSemester($value),
            default          => null,
        };

        $this->loadScoreTypes();
    }

    public function saveAllScores(): void
    {
        $this->refreshScorePermissions();

        if (! $this->selectedLop) {
            $this->emit('toast', 'error', 'Vui lòng chọn lớp');
            return;
        }

        $class = CatechismClass::query()
            ->when($this->parishId, fn ($q) => $q->where('parish_id', $this->parishId))
            ->find($this->selectedLop);

        if (! $class) {
            $this->emit('toast', 'error', 'Lớp không hợp lệ');
            return;
        }

        if (! auth()->user()?->can('enterScoresForClass', $class) || ! $this->canEditScores) {
            $this->emit('toast', 'error', 'Bạn không có quyền nhập hoặc sửa điểm');
            return;
        }

        $writer = app(ScoreEntryWriter::class);

        $error = $writer->validateDraft(
            (int) $this->selectedLop,
            $this->draftScores
        );

        if ($error !== null) {
            $this->emit('toast', 'error', $error);
            return;
        }

        try {
            $result = $writer->save(
                $this->draftScores,
                $this->scoresMatrix,
                $this->parishId ? (int) $this->parishId : null,
                auth()->id()
            );
        } catch (\Exception $e) {
            $this->logError($e, 'Error saving scores');
            $this->emit('toast', 'error', 'Có lỗi khi lưu điểm');
            return;
        }

        $this->scoresMatrix = $result['matrix'];

        $this->loadScoreTypes();
        $this->ensureBreakdownsLoaded(true);
        $this->recalculateRatingStats();

        $this->scoresLoaded = true;
        $this->hasDraft     = false;
        $this->syncDraftWithSavedScores();

        $msg = "Đã lưu {$result['saved']} điểm";
        if ($result['deleted'] > 0) {
            $msg .= ", xóa {$result['deleted']} điểm";
        }

        $this->emit('toast', 'message', $msg);
    }

    /** Sau khi lưu, ô nhập hiển thị đúng giá trị vừa ghi vào database. */
    protected function syncDraftWithSavedScores(): void
    {
        $draft = [];

        foreach ($this->draftScores as $studentClassId => $types) {
            foreach (array_keys($types) as $scoreTypeId) {
                $draft[$studentClassId][$scoreTypeId] =
                    $this->scoresMatrix[$studentClassId][$scoreTypeId]['value'] ?? '';
            }
        }

        $this->draftScores = $draft;
    }

    protected function loadScoresMatrix(array $studentClassIds): void
    {
        $scoreTypes = collect($this->scoreTypes);

        if (empty($studentClassIds) || $scoreTypes->isEmpty()) {
            if (! $this->hasDraft) {
                $this->scoresMatrix = [];
                $this->draftScores  = [];
            }
            return;
        }

        if ($this->scoresLoaded) {
            return;
        }

        try {
            $scoreTypeIds = $scoreTypes->pluck('id')->toArray();

            $scores = StudentScore::whereIn('student_class_id', $studentClassIds)
                ->whereIn('score_type_id', $scoreTypeIds)
                ->get();

            $matrix = [];
            foreach ($scores as $score) {
                $matrix[$score->student_class_id][$score->score_type_id] = [
                    'id'      => $score->id,
                    'value'   => (float) $score->score_value,
                    'attempt' => $score->attempt,
                    'note'    => $score->note,
                ];
            }

            $this->scoresMatrix = $matrix;

            // Không ghi đè draft đang nhập (hasDraft) — tránh mất dữ liệu mỗi lần render.
            if (! $this->hasDraft) {
                $draft = [];
                foreach ($studentClassIds as $scId) {
                    foreach ($scoreTypeIds as $stId) {
                        $draft[$scId][$stId] = isset($matrix[$scId][$stId])
                            ? $matrix[$scId][$stId]['value']
                            : '';
                    }
                }
                $this->draftScores = $draft;
            }

            $this->scoresLoaded = true;
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading scores matrix');
            $this->scoresMatrix = [];
            if (! $this->hasDraft) {
                $this->draftScores = [];
            }
        }
    }
}
