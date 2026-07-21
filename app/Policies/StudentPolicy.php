<?php

namespace App\Policies;

use App\Models\StudentNew;
use App\Models\User;
use App\Services\CatechistAccess;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
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
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    public function view(User $user, StudentNew $student): bool
    {
        return app(CatechistAccess::class)->canViewStudent($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    public function update(User $user, StudentNew $student): bool
    {
        if (! $user->parish_id || (int) $user->parish_id !== (int) $student->parish_id) {
            return false;
        }

        if ($user->canManageCatechism()) {
            return true;
        }

        return app(CatechistAccess::class)->canEditParishStudents($user);
    }

    public function delete(User $user, StudentNew $student): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $student->parish_id;
    }

    public function linkParishioner(User $user, StudentNew $student): bool
    {
        return $this->delete($user, $student);
    }
}
