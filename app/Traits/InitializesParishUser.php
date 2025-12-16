<?php

namespace App\Traits;

use App\Models\Decen;
use App\Models\SetAdmin;
use Illuminate\Support\Facades\Cache;

trait InitializesParishUser
{
    public int $parish_id;
    public bool $isAdmin = false;

    private const USER_CACHE_TTL = 300; // 5 minutes

    /**
     * Initialize parish user context
     */
    protected function initializeParishUser(): void
    {
        $user = backpack_user();

        if (!$user) {
            abort(403, 'Vui lòng đăng nhập');
        }

        [$this->isAdmin, $this->parish_id] = $this->getUserContext($user->id);

        if (!$this->parish_id) {
            abort(403, 'Không có quyền truy cập giáo xứ');
        }
    }

    /**
     * Get user context with caching
     * 
     * @return array [isAdmin, parishId]
     */
    private function getUserContext(int $userId): array
    {
        return Cache::remember(
            "user_context_{$userId}",
            self::USER_CACHE_TTL,
            fn() => $this->fetchUserContext($userId)
        );
    }

    /**
     * Fetch user context from database
     * 
     * @return array [isAdmin, parishId]
     */
    private function fetchUserContext(int $userId): array
    {
        // Check admin first
        $isAdmin = SetAdmin::where('use', $userId)
            ->where('status', 1)
            ->exists();

        if ($isAdmin) {
            $parishId = (int) request()->get('giaoxu');
            return [$isAdmin, $parishId];
        }

        // Check decen
        $parishId = Decen::where('use', $userId)
            ->where('status', 1)
            ->where('student', 1)
            ->value('pid');

        return [false, $parishId ? (int) $parishId : null];
    }

    /**
     * Clear user context cache
     */
    protected function clearUserContextCache(?int $userId = null): void
    {
        $userId = $userId ?? backpack_user()?->id;
        
        if ($userId) {
            Cache::forget("user_context_{$userId}");
        }
    }
}