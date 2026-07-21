<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Phase 1 (an toàn): chỉ tạo permission, không gán tự động cho GLV hiện hữu.
     * Sau khi quản trị xứ cấp quyền Trưởng/Phó trên UI, Phase 2 là bật enforcement
     * (đã nằm trong policy/Livewire của cùng đợt phát hành này).
     */
    private const PERMISSIONS = [
        'manage_parish_scores',
        'edit_parish_students',
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::query()
            ->where('guard_name', 'web')
            ->whereIn('name', self::PERMISSIONS)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
