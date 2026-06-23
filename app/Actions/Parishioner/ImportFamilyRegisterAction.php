<?php

namespace App\Actions\Parishioner;

use App\Actions\Family\FamilyMembershipService;
use App\Models\Family;
use App\Models\Holymanagement;
use App\Models\Marriage;
use App\Models\ParishNew;
use App\Models\Parishioner;
use App\Models\Sacrament;
use App\Support\ExcelDateParser;
use App\Support\ParishionerEnumResolver;
use Illuminate\Support\Facades\DB;

class ImportFamilyRegisterAction
{
    /**
     * @param  array  $parishioners
     * @param  array  $sacraments
     * @param  array  $marriages
     * @return array{
     *   families_created: int,
     *   parishioners_created: int,
     *   sacraments_created: int,
     *   marriages_created: int,
     *   temp_id_map: array<string, int>,
     *   errors: array
     * }
     */
    public function handle(array $parishioners, array $sacraments, array $marriages, int $defaultParishId): array
    {
        $tempIdMap       = [];
        $familyTempMap   = [];
        $saintMap        = [];
        $parishNameMap   = $this->buildParishNameMap();

        $familiesCreated      = 0;
        $parishionersCreated  = 0;
        $sacramentsCreated    = 0;
        $marriagesCreated     = 0;

        DB::transaction(function () use (
            $parishioners,
            $sacraments,
            $marriages,
            $defaultParishId,
            &$tempIdMap,
            &$familyTempMap,
            &$saintMap,
            $parishNameMap,
            &$familiesCreated,
            &$parishionersCreated,
            &$sacramentsCreated,
            &$marriagesCreated
        ) {
            // 1. Saints
            foreach ($parishioners as $row) {
                $saintName = trim($row['saint_name'] ?? '');
                if ($saintName === '' || isset($saintMap[$saintName])) {
                    continue;
                }
                $saint = Holymanagement::firstOrCreate(['name' => $saintName]);
                $saintMap[$saintName] = $saint->id;
            }

            // 2. Families (unique family_temp_id)
            $familyTempIds = collect($parishioners)
                ->pluck('family_temp_id')
                ->filter()
                ->unique()
                ->values();

            foreach ($familyTempIds as $familyTempId) {
                $members = collect($parishioners)->where('family_temp_id', $familyTempId);
                $husband = $members->firstWhere('family_role', 'husband');
                $wife    = $members->firstWhere('family_role', 'wife');
                $label   = $husband
                    ? trim(($husband['last_name'] ?? '') . ' ' . ($husband['first_name'] ?? ''))
                    : ($wife ? trim(($wife['last_name'] ?? '') . ' ' . ($wife['first_name'] ?? '')) : $familyTempId);

                $family = Family::create([
                    'parish_id' => $defaultParishId,
                    'name'      => 'GĐ ' . $label,
                    'status'    => true,
                ]);

                $familyTempMap[$familyTempId] = $family->id;
                $familiesCreated++;
            }

            // 3. Parishioners — topological order (parents before children)
            $sorted = $this->topologicalSort($parishioners);

            foreach ($sorted as $row) {
                $tempId   = $row['temp_id'];
                $parishId = $this->resolveParishId($row['parish_name'], $parishNameMap, $defaultParishId);

                $data = [
                    'last_name'            => $row['last_name'],
                    'first_name'           => $row['first_name'],
                    'gender'               => ParishionerEnumResolver::parseGender($row['gender']),
                    'birthday'             => !empty($row['birthday']) ? ExcelDateParser::parse($row['birthday']) : null,
                    'birth_place'          => $row['birth_place'] ?: null,
                    'birth_order'          => is_numeric($row['birth_order']) ? (int) $row['birth_order'] : null,
                    'saint_id'             => isset($saintMap[$row['saint_name']]) ? $saintMap[$row['saint_name']] : null,
                    'parish_id'            => $parishId,
                    'family_id'            => $familyTempMap[$row['family_temp_id']] ?? null,
                    'family_role'          => $row['family_role'] ?: null,
                    'father_id'            => $this->resolveTempId($row['father_temp_id'], $tempIdMap),
                    'mother_id'            => $this->resolveTempId($row['mother_temp_id'], $tempIdMap),
                    'note'                 => $row['note'] ?: null,
                    'status'               => true,
                    'is_active'            => true,
                    'is_included_in_stats' => true,
                ];

                $parishioner = Parishioner::create($data);
                $tempIdMap[$tempId] = $parishioner->id;
                $parishionersCreated++;
            }

            // 4. Update family head_id
            foreach ($parishioners as $row) {
                $familyId = $familyTempMap[$row['family_temp_id']] ?? null;
                if (!$familyId) {
                    continue;
                }

                $family = Family::find($familyId);
                if (!$family || $family->head_id) {
                    continue;
                }

                if (in_array($row['family_role'], ['husband', 'wife'], true)) {
                    $realId = $tempIdMap[$row['temp_id']] ?? null;
                    if ($realId) {
                        $family->update(['head_id' => $realId]);
                    }
                }
            }

            // 5. Sacraments
            foreach ($sacraments as $row) {
                $parishionerId = $tempIdMap[$row['parishioner_temp_id']] ?? null;
                if (!$parishionerId) {
                    continue;
                }

                $parishId = $this->resolveParishId($row['parish_name'], $parishNameMap, $defaultParishId);

                Sacrament::create([
                    'parishioner_id'     => $parishionerId,
                    'type'               => $row['type'],
                    'received_date'      => ExcelDateParser::parse($row['received_date']),
                    'certificate_number' => $row['certificate_number'] ?: null,
                    'book_number'        => is_numeric($row['book_number']) ? (int) $row['book_number'] : null,
                    'giver'              => $row['giver'] ?: null,
                    'sponsor'            => $row['sponsor'] ?: null,
                    'parish_id'          => $parishId,
                    'parish_name'        => $row['parish_name'] ?: null,
                    'note'               => $row['note'] ?: null,
                ]);

                $sacramentsCreated++;
            }

            // 6. Marriages
            foreach ($marriages as $row) {
                $husbandId = $tempIdMap[$row['husband_temp_id']] ?? null;
                $wifeId    = $tempIdMap[$row['wife_temp_id']] ?? null;

                if (!$husbandId || !$wifeId) {
                    continue;
                }

                $parishId = $this->resolveParishId($row['parish_name'], $parishNameMap, $defaultParishId);

                Marriage::create([
                    'husband_id'         => $husbandId,
                    'wife_id'            => $wifeId,
                    'married_date'       => ExcelDateParser::parse($row['married_date']),
                    'certificate_number' => $row['certificate_number'] ?: null,
                    'parish_id'          => $parishId,
                    'parish_name'        => $row['parish_name'] ?: null,
                    'witness_1'          => $row['witness_1'] ?: null,
                    'witness_2'          => $row['witness_2'] ?: null,
                    'priest_witness'     => $row['priest_witness'] ?: null,
                    'status'             => $row['status'] ?: Marriage::STATUS_VALID,
                    'note'               => $row['note'] ?: null,
                ]);

                $marriagesCreated++;

                Parishioner::whereIn('id', [$husbandId, $wifeId])->update(['married' => 1]);
            }

            $membershipService = app(FamilyMembershipService::class);
            foreach (array_unique(array_values($familyTempMap)) as $familyId) {
                $family = Family::find($familyId);
                if ($family) {
                    $membershipService->recount($family);
                }
            }
        });

        return [
            'families_created'     => $familiesCreated,
            'parishioners_created' => $parishionersCreated,
            'sacraments_created'   => $sacramentsCreated,
            'marriages_created'    => $marriagesCreated,
            'temp_id_map'          => $tempIdMap,
            'errors'               => [],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function topologicalSort(array $rows): array
    {
        $byTempId = collect($rows)->keyBy('temp_id');
        $sorted   = [];
        $visited  = [];

        $visit = function (string $tempId) use (&$visit, &$sorted, &$visited, $byTempId) {
            if (isset($visited[$tempId])) {
                return;
            }
            $visited[$tempId] = true;

            $row = $byTempId->get($tempId);
            if (!$row) {
                return;
            }

            foreach (['father_temp_id', 'mother_temp_id'] as $fk) {
                $parent = trim($row[$fk] ?? '');
                if ($parent !== '' && $byTempId->has($parent)) {
                    $visit($parent);
                }
            }

            $sorted[] = $row;
        };

        foreach ($rows as $row) {
            $visit($row['temp_id']);
        }

        return $sorted;
    }

    private function resolveTempId(?string $tempId, array $map): ?int
    {
        $tempId = trim($tempId ?? '');

        return $tempId !== '' ? ($map[$tempId] ?? null) : null;
    }

    private function buildParishNameMap(): array
    {
        return ParishNew::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [mb_strtolower(trim($name), 'UTF-8') => $id])
            ->toArray();
    }

    private function resolveParishId(?string $parishName, array $map, int $default): int
    {
        $key = mb_strtolower(trim($parishName ?? ''), 'UTF-8');

        return ($key !== '' && isset($map[$key])) ? $map[$key] : $default;
    }
}
