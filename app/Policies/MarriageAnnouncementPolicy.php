<?php

namespace App\Policies;

use App\Models\MarriageAnnouncement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MarriageAnnouncementPolicy
{
    use HandlesAuthorization;

    public function before(User $user): ?bool
    {
        return $user->isSuperAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin') || $user->hasRole('catechist');
    }

    public function view(User $user, MarriageAnnouncement $announcement): bool
    {
        if ($user->hasRole('parish_admin') || $user->hasRole('catechist')) {
            return (int) $user->parish_id === (int) $announcement->pid;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    public function update(User $user, MarriageAnnouncement $announcement): bool
    {
        return $user->hasRole('parish_admin')
            && (int) $user->parish_id === (int) $announcement->pid;
    }

    public function delete(User $user, MarriageAnnouncement $announcement): bool
    {
        return $user->hasRole('parish_admin')
            && (int) $user->parish_id === (int) $announcement->pid;
    }

    public function createMarriage(User $user, MarriageAnnouncement $announcement): bool
    {
        return $this->update($user, $announcement)
            && (int) $announcement->status === 1;
    }
}
