<?php

namespace App\Services\Parish;

use App\Models\ParishGroup;
use Illuminate\Support\Facades\Log;

class ParishChildResolver
{
    /**
     * Resolve tên giáo họ → parish_groups.id (scoped theo giáo xứ ParishNew).
     */
    public function resolve(
        ?string $parishChildName,
        int $parishId,
        int $deaneryId = 0,
        int $dioceseId = 0
    ): ?int {
        if (empty($parishChildName) || empty($parishId)) {
            return null;
        }

        $parishChildName = trim($parishChildName);

        try {
            $group = ParishGroup::query()
                ->where('parish_id', $parishId)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($parishChildName)])
                ->first();

            if (! $group) {
                $group = ParishGroup::create([
                    'name'      => $parishChildName,
                    'parish_id' => $parishId,
                    'status'    => true,
                ]);

                Log::info('Created new parish group (giáo họ)', [
                    'name'      => $parishChildName,
                    'id'        => $group->id,
                    'parish_id' => $parishId,
                ]);
            }

            return $group->id;
        } catch (\Exception $e) {
            Log::error('Error resolving parish group name', [
                'name'      => $parishChildName,
                'parish_id' => $parishId,
                'error'     => $e->getMessage(),
            ]);

            return null;
        }
    }
}
