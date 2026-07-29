<?php

namespace App\Services;

use App\Models\ScoreEditLog;
use App\Models\ScoreType;
use App\Models\StudentScore;
use App\Models\StudentsClass;
use Illuminate\Support\Facades\DB;

/**
 * Ghi điểm đã nhập trên bảng điểm vào database, kèm nhật ký sửa điểm.
 *
 * Người dùng có thể sửa nhiều ô rồi lưu một lần, nên phải so từng ô với giá trị
 * cũ: ô để trống là yêu cầu xoá điểm, ô có số là tạo mới hoặc cập nhật.
 */
class ScoreEntryWriter
{
    /**
     * Kiểm tra dữ liệu nhập trước khi ghi.
     *
     * @param  array  $draftScores  [student_class_id => [score_type_id => value]]
     * @return string|null Thông báo lỗi cho người dùng, null nếu hợp lệ
     */
    public function validateDraft(int $classId, array $draftScores): ?string
    {
        // Lấy lại từ database, không tin dữ liệu đã đi qua trình duyệt.
        $scoreTypes = ScoreType::query()
            ->where('class_id', $classId)
            ->get()
            ->keyBy('id');

        $allowedPivotIds = StudentsClass::query()
            ->where('class_id', $classId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($draftScores as $studentClassId => $types) {
            if (! in_array((int) $studentClassId, $allowedPivotIds, true)) {
                return 'Phát hiện dữ liệu điểm không thuộc lớp đang chọn';
            }

            foreach ($types as $scoreTypeId => $value) {
                $scoreType = $scoreTypes->get((int) $scoreTypeId);

                if (! $scoreType) {
                    return 'Loại điểm không thuộc lớp đang chọn';
                }

                if ($value === '' || $value === null) {
                    continue;
                }

                if (! is_numeric($value)) {
                    return 'Điểm không hợp lệ';
                }

                $max = $scoreType->max_score ?? 10;

                if ((float) $value < 0 || (float) $value > $max) {
                    return "Điểm {$scoreType->name} phải từ 0 đến {$max}";
                }
            }
        }

        return null;
    }

    /**
     * Ghi các ô có thay đổi, trả về ma trận điểm mới và số ô đã lưu/xoá.
     *
     * @param  array  $draftScores  [student_class_id => [score_type_id => value]]
     * @param  array  $matrix       Ma trận điểm hiện có, dùng để biết giá trị cũ
     * @return array{matrix: array, saved: int, deleted: int}
     */
    public function save(array $draftScores, array $matrix, ?int $parishId, ?int $userId): array
    {
        $saved   = 0;
        $deleted = 0;

        DB::transaction(function () use ($draftScores, &$matrix, $parishId, $userId, &$saved, &$deleted) {
            foreach ($draftScores as $studentClassId => $types) {
                foreach ($types as $scoreTypeId => $value) {
                    $hasOriginal = isset($matrix[$studentClassId][$scoreTypeId]);
                    $isEmpty     = $value === '' || $value === null;

                    if ($isEmpty && ! $hasOriginal) {
                        continue;
                    }

                    $original = $hasOriginal
                        ? (float) $matrix[$studentClassId][$scoreTypeId]['value']
                        : null;

                    $draft = $isEmpty ? null : (float) $value;

                    if ($draft === $original) {
                        continue;
                    }

                    if ($draft === null) {
                        $this->deleteScore(
                            (int) $studentClassId,
                            (int) $scoreTypeId,
                            $original,
                            $parishId,
                            $userId
                        );

                        unset($matrix[$studentClassId][$scoreTypeId]);
                        $deleted++;

                        continue;
                    }

                    $this->writeScore(
                        (int) $studentClassId,
                        (int) $scoreTypeId,
                        $draft,
                        $original,
                        $hasOriginal,
                        $parishId,
                        $userId
                    );

                    $matrix[$studentClassId][$scoreTypeId] = [
                        'value'   => $draft,
                        'attempt' => 1,
                        'note'    => null,
                    ];
                    $saved++;
                }
            }
        });

        return [
            'matrix'  => $matrix,
            'saved'   => $saved,
            'deleted' => $deleted,
        ];
    }

    private function deleteScore(
        int $studentClassId,
        int $scoreTypeId,
        ?float $original,
        ?int $parishId,
        ?int $userId
    ): void {
        $existing = StudentScore::query()
            ->where('student_class_id', $studentClassId)
            ->where('score_type_id', $scoreTypeId)
            ->first();

        $this->log(
            $parishId,
            $studentClassId,
            $scoreTypeId,
            $existing?->id,
            $original,
            null,
            ScoreEditLog::ACTION_DELETED,
            $userId
        );

        $existing?->delete();
    }

    private function writeScore(
        int $studentClassId,
        int $scoreTypeId,
        float $draft,
        ?float $original,
        bool $hasOriginal,
        ?int $parishId,
        ?int $userId
    ): void {
        $score = StudentScore::updateOrCreate(
            [
                'student_class_id' => $studentClassId,
                'score_type_id'    => $scoreTypeId,
                'attempt'          => 1,
            ],
            ['score_value' => $draft]
        );

        $this->log(
            $parishId,
            $studentClassId,
            $scoreTypeId,
            $score->id,
            $original,
            $draft,
            $hasOriginal ? ScoreEditLog::ACTION_UPDATED : ScoreEditLog::ACTION_CREATED,
            $userId
        );
    }

    /** Không có giáo xứ thì không ghi nhật ký, vì nhật ký được tra theo giáo xứ. */
    private function log(
        ?int $parishId,
        int $studentClassId,
        int $scoreTypeId,
        ?int $studentScoreId,
        ?float $oldValue,
        ?float $newValue,
        string $action,
        ?int $userId
    ): void {
        if (! $parishId) {
            return;
        }

        ScoreEditLog::create([
            'parish_id'        => $parishId,
            'student_class_id' => $studentClassId,
            'score_type_id'    => $scoreTypeId,
            'student_score_id' => $studentScoreId,
            'old_value'        => $oldValue,
            'new_value'        => $newValue,
            'action'           => $action,
            'user_id'          => $userId,
        ]);
    }
}
