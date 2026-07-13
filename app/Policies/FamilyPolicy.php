<?php

namespace App\Policies;

use App\Models\Family;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class FamilyPolicy
{
    use HandlesAuthorization;

    /**
     * SuperAdmin bỏ qua tất cả checks
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageParishioners()
            || $user->isCatechist();
    }

    public function view(User $user, Family $family): bool
    {
        if ($user->canManageParishioners() || $user->isCatechist()) {
            return $user->parish_id === $family->parish_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageParishioners();
    }

    public function update(User $user, Family $family): bool
    {
        return $user->canManageParishioners()
            && $user->parish_id === $family->parish_id;
    }

    public function delete(User $user, Family $family): bool
    {
        return $user->canManageParishioners()
            && $user->parish_id === $family->parish_id;
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageParishioners();
    }
}
