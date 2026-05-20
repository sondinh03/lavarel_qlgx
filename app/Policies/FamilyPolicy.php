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

    /**
     * Xem danh sách giáo dân
     * parish_admin: xem giáo dân trong xứ mình
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('parish_admin')
            || $user->hasRole('catechist');
    }

    /**
     * Xem chi tiết một giáo dân
     * parish_admin và catechist chỉ xem giáo dân trong xứ mình
     */
    public function view(User $user, Family $family): bool
    {
        // parish_admin xem trong xứ mình
        if ($user->hasRole('parish_admin')) {
            return $user->parish_id === $family->parish_id;
        }

        // catechist chỉ xem, không sửa/xóa — check cùng xứ là đủ
        if ($user->hasRole('catechist')) {
            return $user->parish_id === $family->parish_id;
        }

        return false;
    }

    /**
     * Tạo giáo dân mới
     * chỉ parish_admin trong cùng xứ được tạo giáo dân mới
     */
    public function create(User $user): bool
    {
        return $user->hasRole('parish_admin');
    }

    /**
     * Cập nhật giáo dân - parish_admin trong cùng xứ
     */
    public function update(User $user, Family $family): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $family->parish_id;
    }

    /**
     * Xóa giáo dân - parish_admin trong cùng xứ
     */
    public function delete(User $user, Family $family): bool
    {
        return $user->hasRole('parish_admin')
            && $user->parish_id === $family->parish_id;
    }
}
