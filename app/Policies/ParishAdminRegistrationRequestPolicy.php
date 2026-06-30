<?php

namespace App\Policies;

use App\Models\ParishAdminRegistrationRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ParishAdminRegistrationRequestPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function view(User $user, ParishAdminRegistrationRequest $request): bool
    {
        return $user->isSuperAdmin();
    }

    public function approve(User $user, ParishAdminRegistrationRequest $request): bool
    {
        return $user->isSuperAdmin() && $request->isPending();
    }

    public function reject(User $user, ParishAdminRegistrationRequest $request): bool
    {
        return $this->approve($user, $request);
    }
}
