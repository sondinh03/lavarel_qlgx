<?php

namespace App\Policies;

use App\Models\CatechismClass;
use App\Models\ParishNew;
use App\Models\User;
use App\Services\CatechistAccess;
use Illuminate\Auth\Access\HandlesAuthorization;

class CatechismClassPolicy
{
    use HandlesAuthorization;

    /**
     * Super Admin bỏ qua tất cả checks
     */
    public function before(User $user)
    {
        if ($user->isSuperAdmin()) {
            return true;
        }
    }

    /**
     * Xem danh sách lớp
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageCatechism()
            || $user->isCatechist();
    }

    /**
     * Xem chi tiết — admin giáo lý / GLV cùng xứ xem mọi lớp trong giáo xứ
     */
    public function view(User $user, CatechismClass $class): bool
    {
        if ($user->canManageCatechism() || $user->isCatechist()) {
            return $user->parish_id === $class->parish_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->canManageCatechism();
    }

    public function update(User $user, CatechismClass $class): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $class->parish_id;
    }

    public function delete(User $user, CatechismClass $class): bool
    {
        return $user->canManageCatechism()
            && $user->parish_id === $class->parish_id;
    }

    /**
     * Xem điểm của một lớp (GLV thường: lớp được giao; elevated: toàn xứ).
     */
    public function viewScoresForClass(User $user, CatechismClass $class): bool
    {
        if ($user->canManageCatechism()) {
            return (int) $user->parish_id === (int) $class->parish_id;
        }

        if (! $user->parish_id || (int) $user->parish_id !== (int) $class->parish_id) {
            return false;
        }

        return app(CatechistAccess::class)->canViewScoresForClass(
            $user,
            (int) $class->id,
            (int) $user->parish_id
        );
    }

    /**
     * Nhập/sửa điểm của một lớp (chỉ admin hoặc GLV có manage_parish_scores + cửa sổ mở).
     */
    public function enterScoresForClass(User $user, CatechismClass $class): bool
    {
        if ($user->canManageCatechism()) {
            return (int) $user->parish_id === (int) $class->parish_id;
        }

        if (! $user->parish_id || (int) $user->parish_id !== (int) $class->parish_id) {
            return false;
        }

        $open = (bool) ParishNew::query()
            ->whereKey($user->parish_id)
            ->value('scores_entry_open');

        return app(CatechistAccess::class)->canEnterScoresForClass(
            $user,
            (int) $class->id,
            $open,
            (int) $user->parish_id
        );
    }
}
