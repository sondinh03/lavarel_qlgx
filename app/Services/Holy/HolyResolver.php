<?php
namespace app\Services\Holy;

use App\Models\Holymanagement;

class HolyResolver
{
    /**
     * Resolve holy name (string) to holy_id
     */
    public function resolve(?string $holyName): ?int
    {
        if (empty($holyName)) {
            return null;
        }

        $holyName = trim($holyName);

        $holy = Holymanagement::firstOrCreate(
            ['name' => $holyName]
        );

        return $holy->id;
    }
}