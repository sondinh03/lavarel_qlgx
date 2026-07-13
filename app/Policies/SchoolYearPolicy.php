<?php

namespace App\Policies;

use App\Models\NamHoc;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SchoolYearPolicy
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
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    public function view(User $user, NamHoc $namHoc): bool
    {
        if ($user->canManageCatechism() || $user->isCatechist()) {
            return $user->parish_id === $namHoc->parish_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    public function update(User $user, NamHoc $namHoc): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $namHoc->parish_id;
    }

    public function delete(User $user, NamHoc $namHoc): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $namHoc->parish_id;
    }
}
