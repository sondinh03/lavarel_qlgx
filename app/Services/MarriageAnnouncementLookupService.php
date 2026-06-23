<?php

namespace App\Services;

use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\Priest;

class MarriageAnnouncementLookupService
{
    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function searchPriests(?string $query = '', int $limit = 20): array
    {
        return Priest::query()
            ->when($query, fn ($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function searchDioceses(?string $query = '', int $limit = 50): array
    {
        return Diocese::query()
            ->where('status', 1)
            ->when($query, fn ($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($d) => ['id' => (string) $d->id, 'name' => $d->name])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function searchDeaneries(?int $dioceseId, ?string $query = '', int $limit = 50): array
    {
        if (! $dioceseId) {
            return [];
        }

        return Deanery::query()
            ->where('did', $dioceseId)
            ->where('status', 1)
            ->when($query, fn ($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($d) => ['id' => (string) $d->id, 'name' => $d->name])
            ->values()
            ->all();
    }

    /**
     * Giáo xứ (bảng `parishes`).
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function searchParishes(?int $deaneryId, ?string $query = '', int $limit = 50): array
    {
        if (! $deaneryId) {
            return [];
        }

        return ParishNew::query()
            ->where('deanery_id', $deaneryId)
            ->where('status', 1)
            ->when($query, fn ($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->values()
            ->all();
    }

    /**
     * Giáo họ / quản lý (bảng `parish_groups`).
     *
     * @return array<int, array{id: string, name: string}>
     */
    public function searchParishGroups(?int $parishId, ?string $query = '', int $limit = 50): array
    {
        if (! $parishId) {
            return [];
        }

        return ParishGroup::query()
            ->where('parish_id', $parishId)
            ->where('status', 1)
            ->when($query, fn ($q) => $q->where('name', 'like', '%' . $query . '%'))
            ->orderBy('name')
            ->limit($limit)
            ->get(['id', 'name'])
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->values()
            ->all();
    }

    /** @deprecated Use searchParishes() */
    public function searchParishManagements(?int $deaneryId, ?string $query = '', int $limit = 50): array
    {
        return $this->searchParishes($deaneryId, $query, $limit);
    }

    /** @deprecated Use searchParishGroups() */
    public function searchLegacyParishes(?int $parishId, ?string $query = '', int $limit = 50): array
    {
        return $this->searchParishGroups($parishId, $query, $limit);
    }

    /**
     * @return array<int, array{id: string, name: string}>
     */
    public function searchParishioners(?int $parishId, ?string $gender, ?string $query = '', int $limit = 30): array
    {
        return Parishioner::query()
            ->with('saint')
            ->when($parishId, fn ($q) => $q->where('parish_id', $parishId))
            ->when($gender, fn ($q) => $q->where('gender', $gender))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($inner) use ($query) {
                    $inner->where('last_name', 'like', '%' . $query . '%')
                        ->orWhere('first_name', 'like', '%' . $query . '%');
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($limit)
            ->get()
            ->map(fn ($p) => ['id' => (string) $p->id, 'name' => $p->full_name_with_saint])
            ->values()
            ->all();
    }

    public function resolveDioceseName(?int $id): ?string
    {
        return $id ? Diocese::find($id)?->name : null;
    }

    public function resolveDeaneryName(?int $id): ?string
    {
        return $id ? Deanery::find($id)?->name : null;
    }

    public function resolveParishName(?int $id): ?string
    {
        return $id ? ParishNew::find($id)?->name : null;
    }

    public function resolveParishGroupName(?int $id): ?string
    {
        return $id ? ParishGroup::find($id)?->name : null;
    }

    /** @deprecated */
    public function resolveParishManagementName(?int $id): ?string
    {
        return $this->resolveParishGroupName($id);
    }

    /** @deprecated */
    public function resolveLegacyParishName(?int $id): ?string
    {
        return $this->resolveParishName($id);
    }
}
