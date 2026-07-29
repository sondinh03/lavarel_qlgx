<?php

namespace App\Http\Livewire\Score\Concerns;

use App\Models\StudentNew;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Danh sách học sinh của lớp cho bảng điểm.
 *
 * Sắp xếp theo điểm trung bình và lọc theo xếp loại cần TB của cả lớp, nên hai
 * trường hợp đó phải lấy hết học sinh rồi mới phân trang thủ công.
 */
trait ListsClassStudents
{
    protected function applySorting($query)
    {
        return $query->orderByRaw(
            "students.first_name {$this->sortDirection},
             students.last_name {$this->sortDirection}"
        );
    }

    public function computedStudents()
    {
        return $this->getStudentsPaginated();
    }

    protected function getStudentsPaginated()
    {
        if (! $this->selectedLop) {
            return $this->emptyPaginator();
        }

        try {
            $needsFullList = $this->sortField === 'avg' || $this->filterByRating;

            $query = $this->buildStudentsQuery();
            $this->applySorting($query);

            if (! $needsFullList) {
                $students = $query->paginate($this->perPage);

                $this->loadScoresMatrix($students->pluck('pivot_id')->toArray());
                $this->ensureBreakdownsLoaded();
                $this->recalculateRatingStats();

                return $students;
            }

            $allStudents = $query->get();

            $this->loadScoresMatrix($allStudents->pluck('pivot_id')->toArray());
            $this->ensureBreakdownsLoaded();

            $collection = $this->sortByAverage($this->filterByRatingLevel($allStudents));

            $this->recalculateRatingStats();

            return $this->paginateCollection($collection);
        } catch (\Exception $e) {
            $this->logError($e, 'Error loading students');
            $this->emit('toast', 'error', 'Có lỗi khi tải danh sách học sinh');

            return $this->emptyPaginator();
        }
    }

    /**
     * Query học sinh trong lớp (chưa phân trang).
     */
    protected function buildStudentsQuery()
    {
        $query = StudentNew::query()
            ->with('saint')
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.class_id', $this->selectedLop)
            ->select(
                'students_class.id as pivot_id',
                'students_class.student_id',
                'students.*',
            );

        if (! empty(trim($this->search ?? ''))) {
            $term = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('students.first_name', 'like', $term)
                    ->orWhere('students.last_name', 'like', $term)
                    ->orWhere('students.student_code', 'like', $term);
            });
        }

        return $query;
    }

    protected function filterByRatingLevel(Collection $students): Collection
    {
        if (! $this->filterByRating) {
            return $students;
        }

        return $students->filter(function ($student) {
            $avg = $this->averages[$student->pivot_id] ?? null;

            return $avg !== null
                && $this->getStudentRating((float) $avg) === $this->filterByRating;
        })->values();
    }

    /** Học sinh chưa có TB xuống cuối khi sắp xếp tăng dần. */
    protected function sortByAverage(Collection $students): Collection
    {
        if ($this->sortField !== 'avg') {
            return $students;
        }

        return $students->sortBy(
            fn ($student) => $this->averages[$student->pivot_id] ?? -1,
            SORT_REGULAR,
            $this->sortDirection === 'desc'
        )->values();
    }

    /**
     * Phân trang thủ công sau khi filter/sort trên collection.
     */
    protected function paginateCollection(Collection $items): LengthAwarePaginator
    {
        $page    = max(1, (int) ($this->page ?? 1));
        $perPage = $this->perPage;

        return new LengthAwarePaginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path'  => Paginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, 1);
    }

    /**
     * Học sinh đang mở modal chi tiết — có thể không nằm trong trang hiện tại.
     *
     * @param  \Illuminate\Contracts\Pagination\Paginator|\Illuminate\Support\Collection  $students
     */
    protected function resolveViewingStudent($students)
    {
        if (! $this->viewingPivotId || ! $this->selectedLop) {
            return null;
        }

        $fromPage = collect($students->items())->firstWhere('pivot_id', $this->viewingPivotId);
        if ($fromPage) {
            return $fromPage;
        }

        $student = StudentNew::query()
            ->with('saint')
            ->join('students_class', 'students.id', '=', 'students_class.student_id')
            ->where('students_class.id', $this->viewingPivotId)
            ->where('students_class.class_id', $this->selectedLop)
            ->select(
                'students_class.id as pivot_id',
                'students_class.student_id',
                'students.*',
            )
            ->first();

        if (! $student) {
            $this->viewingPivotId = null;

            return null;
        }

        if (! isset($this->scoresMatrix[$student->pivot_id])) {
            $this->loadScoresMatrix([(int) $student->pivot_id]);
        }

        $this->ensureBreakdownsLoaded();

        return $student;
    }
}
