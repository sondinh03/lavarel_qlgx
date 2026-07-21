<?php

namespace App\Services;

use App\Models\ClassTeacher;
use App\Models\NamHoc;
use App\Models\StudentNew;
use App\Models\Teacher;
use App\Models\User;
use App\Support\CatechistPermissions;
use Illuminate\Support\Collection;

class CatechistAccess
{
    public function teacherFor(User $user): ?Teacher
    {
        if ($user->relationLoaded('teacher')) {
            return $user->teacher;
        }

        return Teacher::query()
            ->where('user_id', $user->id)
            ->when($user->parish_id, fn ($q) => $q->where('parish_id', $user->parish_id))
            ->first();
    }

    public function canManageParishScores(User $user): bool
    {
        if ($user->canManageCatechism()) {
            return true;
        }

        return $user->isCatechist()
            && $user->can(CatechistPermissions::MANAGE_PARISH_SCORES);
    }

    public function canEditParishStudents(User $user): bool
    {
        if ($user->canManageCatechism()) {
            return true;
        }

        return $user->isCatechist()
            && $user->can(CatechistPermissions::EDIT_PARISH_STUDENTS);
    }

    public function canGrantElevatedPermissions(User $actor): bool
    {
        return $actor->isSuperAdmin() || $actor->isParishAdmin();
    }

    /**
     * @return list<int>
     */
    public function assignedClassIds(
        User $user,
        ?int $parishId = null,
        ?int $schoolYearId = null
    ): array {
        $teacher = $this->teacherFor($user);
        if (! $teacher) {
            return [];
        }

        $parishId = $parishId ?? $user->parish_id;
        if (! $parishId) {
            return [];
        }

        $query = ClassTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', true)
            ->whereHas('catechismClass', function ($q) use ($parishId, $schoolYearId) {
                $q->where('parish_id', $parishId)
                    ->where('is_active', true);

                if ($schoolYearId) {
                    $q->where('school_year_id', $schoolYearId);
                }
            });

        if ($schoolYearId) {
            $query->where(function ($q) use ($schoolYearId) {
                $q->where('namhoc_id', $schoolYearId)
                    ->orWhereHas(
                        'catechismClass',
                        fn ($c) => $c->where('school_year_id', $schoolYearId)
                    );
            });
        }

        return $query->pluck('class_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function defaultAssignedClassId(
        User $user,
        ?int $parishId = null,
        ?int $schoolYearId = null
    ): ?int {
        $teacher = $this->teacherFor($user);
        if (! $teacher) {
            return null;
        }

        $parishId = $parishId ?? $user->parish_id;
        if (! $parishId) {
            return null;
        }

        $query = ClassTeacher::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', true)
            ->whereHas('catechismClass', function ($q) use ($parishId, $schoolYearId) {
                $q->where('parish_id', $parishId)
                    ->where('is_active', true);

                if ($schoolYearId) {
                    $q->where('school_year_id', $schoolYearId);
                }
            });

        if ($schoolYearId) {
            $query->where(function ($q) use ($schoolYearId) {
                $q->where('namhoc_id', $schoolYearId)
                    ->orWhereHas(
                        'catechismClass',
                        fn ($c) => $c->where('school_year_id', $schoolYearId)
                    );
            });
        }

        $classId = $query->orderByDesc('role')->value('class_id');

        return $classId ? (int) $classId : null;
    }

    public function currentSchoolYearId(?int $parishId): ?int
    {
        if (! $parishId) {
            return null;
        }

        return NamHoc::query()
            ->where('parish_id', $parishId)
            ->active()
            ->current()
            ->value('id');
    }

    public function canViewClass(User $user, int $classId, ?int $parishId = null): bool
    {
        if ($user->canManageCatechism() || $this->canManageParishScores($user) || $this->canEditParishStudents($user)) {
            return true;
        }

        if (! $user->isCatechist()) {
            return false;
        }

        return in_array($classId, $this->assignedClassIds($user, $parishId), true);
    }

    public function canViewStudent(User $user, StudentNew $student): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->parish_id || (int) $user->parish_id !== (int) $student->parish_id) {
            return false;
        }

        if ($user->canManageCatechism() || $this->canEditParishStudents($user)) {
            return true;
        }

        if (! $user->isCatechist()) {
            return false;
        }

        $assigned = $this->assignedClassIds($user, (int) $user->parish_id);
        if ($assigned === []) {
            return false;
        }

        return $student->classes()
            ->whereIn('classes.id', $assigned)
            ->exists();
    }

    public function canViewScoresForClass(User $user, int $classId, ?int $parishId = null): bool
    {
        if ($user->canManageCatechism() || $this->canManageParishScores($user)) {
            return true;
        }

        if (! $user->isCatechist()) {
            return false;
        }

        return in_array($classId, $this->assignedClassIds($user, $parishId), true);
    }

    public function canEnterScoresForClass(User $user, int $classId, bool $scoresEntryOpen, ?int $parishId = null): bool
    {
        if ($user->canManageCatechism()) {
            return true;
        }

        if (! $scoresEntryOpen) {
            return false;
        }

        if (! $this->canManageParishScores($user)) {
            return false;
        }

        // Elevated catechist: any class in own parish (caller must ensure parish match).
        return $user->isCatechist();
    }

    /**
     * Restrict a CatechismClass query for list filters.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function restrictClassQuery($query, User $user, ?int $parishId = null, ?int $schoolYearId = null)
    {
        if ($user->canManageCatechism()
            || $this->canManageParishScores($user)
            || $this->canEditParishStudents($user)
        ) {
            return $query;
        }

        if (! $user->isCatechist()) {
            return $query->whereRaw('1 = 0');
        }

        $ids = $this->assignedClassIds($user, $parishId, $schoolYearId);
        if ($ids === []) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $ids);
    }

    /**
     * @return Collection<int, int>
     */
    public function visibleClassIdsForScores(User $user, ?int $parishId, ?int $schoolYearId = null): Collection
    {
        if ($user->canManageCatechism() || $this->canManageParishScores($user)) {
            return collect(); // empty = no extra restriction (all parish classes)
        }

        return collect($this->assignedClassIds($user, $parishId, $schoolYearId));
    }
}
