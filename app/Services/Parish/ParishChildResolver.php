<?php

namespace App\Services\Parish;

use App\Models\Parish;
use Illuminate\Support\Facades\Log;

class ParishChildResolver
{
    /**
     * Resolve parish name to parish_id (scoped by parishId)
     * 
     * @param string|null $parishChildName
     * @param int $parishId
     * @param int $deaneryId
     * @param int $dioceseId
     * @return int|null
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
            // ✅ Tìm (case-insensitive + scoped)
            $parish = Parish::query()
                ->where('pid', $parishId)
                ->whereRaw('LOWER(name) = ?', [strtolower($parishChildName)])
                ->first();

            // ✅ Tạo mới nếu chưa có
            if (!$parish) {
                $parish = Parish::create([
                    'name' => $parishChildName,
                    'pid' => $parishId,
                    'deid' => $deaneryId,
                    'did' => $dioceseId,
                    'status' => 1,
                ]);

                Log::info('Created new parish (giáo họ)', [
                    'name' => $parishChildName,
                    'id' => $parish->id,
                    'parish_id' => $parishId,
                ]);
            }

            return $parish->id;
        } catch (\Exception $e) {
            Log::error('Error resolving parish name', [
                'name' => $parishChildName,
                'parish_id' => $parishId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
