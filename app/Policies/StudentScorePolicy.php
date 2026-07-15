<?php

namespace App\Policies;

use App\Models\ParishNew;
use App\Models\StudentScore;
use App\Models\User;

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
        return true; // Guest xem được — phụ huynh tra cứu
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
     * Ban quản trị luôn nhập/sửa điểm.
     * GLV chỉ được sửa khi giáo xứ mở cửa sổ nhập điểm.
     */
    public function enterScores(User $user): bool
    {
        if ($user->canManageCatechism()) {
            return true;
        }

        if (! $user->isCatechist() || ! $user->parish_id) {
            return false;
        }

        return (bool) ParishNew::query()
            ->whereKey($user->parish_id)
            ->value('scores_entry_open');
    }
}
