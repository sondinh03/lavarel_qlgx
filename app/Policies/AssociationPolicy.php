<?php

namespace App\Policies;

use App\Models\Association;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssociationPolicy
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
        return $user->canManageParishioners();
    }

    public function create(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function update(User $user, Association $association): bool
    {
        return $user->canManageParishioners()
            && (int) $user->parish_id === (int) $association->pid;
    }

    public function delete(User $user, Association $association): bool
    {
        return $user->canManageParishioners()
            && (int) $user->parish_id === (int) $association->pid;
    }
}
