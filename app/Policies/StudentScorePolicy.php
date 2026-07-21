<?php

namespace App\Policies;

use App\Models\CatechismClass;
use App\Models\ParishNew;
use App\Models\StudentScore;
use App\Models\User;
use App\Services\CatechistAccess;

class StudentScorePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(?User $user): bool
    {
        // Guest: chỉ dùng Landing (không đi qua Gate staff).
        // Staff: route middleware đã chặn role.
        return true;
    }

    public function view(?User $user, StudentScore $studentScore): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $this->enterScores($user);
    }

    public function update(User $user, StudentScore $studentScore): bool
    {
        return $this->enterScores($user);
    }

    public function delete(User $user, StudentScore $studentScore): bool
    {
        return $this->enterScores($user);
    }

    /**
     * Có thể nhập điểm ở mức tài khoản (chưa gắn lớp).
     * Kiểm tra lớp cụ thể qua CatechistAccess / ScoreManager.
     */
    public function enterScores(User $user): bool
    {
        if ($user->canManageCatechism()) {
            return true;
        }

        $access = app(CatechistAccess::class);
        if (! $access->canManageParishScores($user) || ! $user->parish_id) {
            return false;
        }

        return (bool) ParishNew::query()
            ->whereKey($user->parish_id)
            ->value('scores_entry_open');
    }

    /**
     * @deprecated Dùng CatechismClassPolicy::viewScoresForClass khi authorize với model lớp.
     */
    public function viewScoresForClass(User $user, CatechismClass $class): bool
    {
        return app(CatechismClassPolicy::class)->viewScoresForClass($user, $class);
    }

    /**
     * @deprecated Dùng CatechismClassPolicy::enterScoresForClass khi authorize với model lớp.
     */
    public function enterScoresForClass(User $user, CatechismClass $class): bool
    {
        return app(CatechismClassPolicy::class)->enterScoresForClass($user, $class);
    }
}
