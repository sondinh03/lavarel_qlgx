<?php

namespace App\Policies;

use App\Models\ParishGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParishGroupPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isParishAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isParishAdmin();
    }

    public function update(User $user, ParishGroup $group): bool
    {
        return $user->isParishAdmin()
            && $user->parish_id === $group->parish_id;
    }

    public function delete(User $user, ParishGroup $group): bool
    {
        return $user->isParishAdmin()
            && $user->parish_id === $group->parish_id;
    }
}
