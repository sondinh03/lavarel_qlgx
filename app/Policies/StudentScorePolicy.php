<?php

namespace App\Policies;

use App\Models\StudentScore;
use App\Models\User;

class StudentScorePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) return true;
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
        return $user->isParishAdmin();
    }

    public function update(User $user, StudentScore $studentScore): bool
    {
        return $user->isParishAdmin();
    }

    public function delete(User $user, StudentScore $studentScore): bool
    {
        return $user->isParishAdmin();
    }
}
