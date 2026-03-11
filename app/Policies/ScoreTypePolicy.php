<?php

namespace App\Policies;

use App\Models\ScoreType;
use App\Models\User;

class ScoreTypePolicy
{
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) return true;
        return null;
    }

    public function viewAny(?User $user): bool
    {
        return true; // Ai cũng xem được
    }

    public function view(?User $user, ScoreType $scoreType): bool
    {
        return true; // Ai cũng xem được
    }

    public function create(User $user): bool
    {
        return $user->isParishAdmin();
    }

    public function update(User $user): bool
    {
        return $user->isParishAdmin();
    }

    public function delete(User $user): bool
    {
        return $user->isParishAdmin();
    }
}
