<?php

namespace App\Actions\Parishioner;

use App\Actions\Family\FamilyMembershipService;
use App\Models\Family;
use App\Models\Holymanagement;
use App\Models\Marriage;
use App\Models\Parishioner;
use App\Models\ParishionerRegistrationRequest;
use App\Models\Sacrament;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ApproveParishionerRegistrationAction
{
    /**
     * @return array{parishioner: Parishioner, request: ParishionerRegistrationRequest, family: ?Family}
     */
    public function handle(ParishionerRegistrationRequest $request, User $reviewer, ?string $adminNote = null): array
    {
        if (! $request->isPending()) {
            throw new InvalidArgumentException('Yêu cầu đã được xử lý.');
        }

        $payload = $request->payload;

        if (($payload['version'] ?? 1) >= 2 && ! empty($payload['members'])) {
            return $this->approveFamilyRegister($request, $reviewer, $adminNote);
        }

        return $this->approveSinglePerson($request, $reviewer, $adminNote);
    }

    /**
     * @return array{parishioner: Parishioner, request: ParishionerRegistrationRequest, family: ?Family}
     */
    private function approveSinglePerson(
        ParishionerRegistrationRequest $request,
        User $reviewer,
        ?string $adminNote
    ): array {
        return DB::transaction(function () use ($request, $reviewer, $adminNote) {
            $payload = $request->payload;
            $saintId = $this->resolveSaintId($payload);

            $data = [
                'last_name'            => $payload['last_name'],
                'first_name'           => $payload['first_name'],
                'gender'               => $payload['gender'],
                'birthday'             => $payload['birthday'] ?? null,
                'birth_place'          => $payload['birth_place'] ?? null,
                'birth_order'          => $payload['birth_order'] ?? null,
                'saint_id'             => $saintId,
                'cccd'                 => $payload['cccd'] ?? null,
                'phone'                => $payload['phone'] ?? null,
                'email'                => $payload['email'] ?? null,
                'note'                 => $this->buildLegacyNote($payload, $request->reference_code),
                'parish_id'            => $request->parish_id,
                'parish_area_id'       => $payload['parish_area_id'] ?? null,
                'origin'               => $payload['origin'] ?? null,
                'permanent_province'   => $payload['permanent_province'] ?? null,
                'permanent_ward_id'    => $payload['permanent_ward_id'] ?? null,
                'permanent_residence'  => $payload['permanent_residence'] ?? null,
                'temporary_province'   => $payload['temporary_province'] ?? null,
                'temporary_ward_id'    => $payload['temporary_ward_id'] ?? null,
                'temporary_residence'  => $payload['temporary_residence'] ?? null,
                'father_name'          => $payload['father_name'] ?? null,
                'mother_name'          => $payload['mother_name'] ?? null,
                'family_role'          => $payload['family_role'] ?? null,
                'married'              => (int) ($payload['married'] ?? 0),
                'ethnic'               => $payload['ethnic'] ?? null,
                'career'               => $payload['career'] ?? null,
                'education_level'      => $payload['education_level'] ?? null,
                'status'               => true,
                'is_active'            => true,
                'is_new_convert'       => (bool) ($payload['is_new_convert'] ?? false),
                'is_included_in_stats' => true,
            ];

            if ($request->avatar_path) {
                $data['avatar_path'] = $this->moveAvatarToParishionerFolder($request->avatar_path);
            }

            $parishioner = Parishioner::create($data);

            foreach ($request->sacraments ?? [] as $row) {
                $this->createSacrament($parishioner->id, $row, $request->parish_id);
            }

            $request->update([
                'status'         => ParishionerRegistrationRequest::STATUS_APPROVED,
                'parishioner_id' => $parishioner->id,
                'reviewed_by'    => $reviewer->id,
                'reviewed_at'    => now(),
                'admin_note'     => $adminNote,
            ]);

            return [
                'parishioner' => $parishioner,
                'request'     => $request->fresh(),
                'family'      => null,
            ];
        });
    }

    /**
     * @return array{parishioner: Parishioner, request: ParishionerRegistrationRequest, family: Family}
     */
    private function approveFamilyRegister(
        ParishionerRegistrationRequest $request,
        User $reviewer,
        ?string $adminNote
    ): array {
        return DB::transaction(function () use ($request, $reviewer, $adminNote) {
            $payload  = $request->payload;
            $familyData = $payload['family'] ?? [];
            $members  = $payload['members'] ?? [];
            $refMap   = [];
            $familyCode = $familyData['code'] ?? $request->reference_code;

            $family = Family::create([
                'code'            => $familyCode,
                'parish_id'       => $request->parish_id,
                'parish_group_id' => $familyData['parish_area_id'] ?? null,
                'name'            => $familyData['name'] ?? 'Gia đình',
                'address'         => $familyData['address'] ?? null,
                'province'        => $familyData['province'] ?? null,
                'ward_id'         => $familyData['ward_id'] ?? null,
                'status'          => true,
                'is_included_in_stats' => true,
            ]);

            $sorted = $this->topologicalSortMembers($members);

            foreach ($sorted as $row) {
                $ref = $row['ref'];
                $saintId = ! empty($row['saint_id']) ? (int) $row['saint_id'] : null;

                $parishioner = Parishioner::create([
                    'last_name'            => $row['last_name'],
                    'first_name'           => $row['first_name'],
                    'gender'               => $row['gender'],
                    'birthday'             => $row['birthday'] ?? null,
                    'birth_place'          => $row['birth_place'] ?? null,
                    'birth_order'          => $row['birth_order'] ?? null,
                    'saint_id'             => $saintId,
                    'cccd'                 => $row['cccd'] ?? null,
                    'phone'                => ($row['ref'] ?? '') === ($payload['submitter_ref'] ?? '')
                        ? ($payload['contact_phone'] ?? null) : null,
                    'father_name'          => $row['father_name'] ?? null,
                    'mother_name'          => $row['mother_name'] ?? null,
                    'father_id'            => $this->resolveRef($row['father_ref'] ?? null, $refMap),
                    'mother_id'            => $this->resolveRef($row['mother_ref'] ?? null, $refMap),
                    'family_id'            => $family->id,
                    'family_role'          => $row['family_role'] ?? null,
                    'parish_id'            => $request->parish_id,
                    'parish_area_id'       => $familyData['parish_area_id'] ?? null,
                    'permanent_residence'  => $familyData['address'] ?? null,
                    'permanent_province'   => $familyData['province'] ?? null,
                    'permanent_ward_id'    => $familyData['ward_id'] ?? null,
                    'note'                 => ($note = trim(($row['note'] ?? '') . "\nĐăng ký sổ GĐ (mã {$request->reference_code})")) !== ''
                        ? $note : null,
                    'status'               => true,
                    'is_active'            => true,
                    'is_included_in_stats' => true,
                ]);

                $refMap[$ref] = $parishioner->id;
            }

            foreach ($members as $row) {
                if (($row['family_role'] ?? '') !== 'husband') {
                    continue;
                }
                $realId = $refMap[$row['ref']] ?? null;
                if ($realId) {
                    $family->update(['head_id' => $realId]);
                    break;
                }
            }

            if (! $family->head_id) {
                foreach ($members as $row) {
                    if (($row['family_role'] ?? '') !== 'wife') {
                        continue;
                    }
                    $realId = $refMap[$row['ref']] ?? null;
                    if ($realId) {
                        $family->update(['head_id' => $realId]);
                        break;
                    }
                }
            }

            foreach ($request->sacraments ?? [] as $row) {
                $parishionerId = $refMap[$row['member_ref'] ?? ''] ?? null;
                if ($parishionerId) {
                    $this->createSacrament($parishionerId, $row, $request->parish_id);
                }
            }

            foreach ($request->marriages ?? [] as $row) {
                $husbandId = $refMap[$row['husband_ref'] ?? ''] ?? null;
                $wifeId    = $refMap[$row['wife_ref'] ?? ''] ?? null;

                if (! $husbandId || ! $wifeId) {
                    continue;
                }

                Marriage::create([
                    'husband_id'         => $husbandId,
                    'wife_id'            => $wifeId,
                    'married_date'       => $row['married_date'] ?? null,
                    'certificate_number' => $row['certificate_number'] ?? null,
                    'parish_id'          => $request->parish_id,
                    'parish_name'        => $row['parish_name'] ?? null,
                    'witness_1'          => $row['witness_1'] ?? null,
                    'witness_2'          => $row['witness_2'] ?? null,
                    'priest_witness'     => $row['priest_witness'] ?? null,
                    'status'             => $row['status'] ?? Marriage::STATUS_VALID,
                    'note'               => $row['note'] ?? null,
                ]);

                Parishioner::whereIn('id', [$husbandId, $wifeId])->update(['married' => 1]);
            }

            app(FamilyMembershipService::class)->recount($family);

            $submitterId = $refMap[$payload['submitter_ref'] ?? ''] ?? reset($refMap);

            $request->update([
                'status'         => ParishionerRegistrationRequest::STATUS_APPROVED,
                'parishioner_id' => $submitterId ?: null,
                'family_id'      => $family->id,
                'reviewed_by'    => $reviewer->id,
                'reviewed_at'    => now(),
                'admin_note'     => $adminNote,
            ]);

            return [
                'parishioner' => Parishioner::findOrFail($submitterId),
                'request'     => $request->fresh(),
                'family'      => $family->fresh(),
            ];
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $members
     * @return array<int, array<string, mixed>>
     */
    private function topologicalSortMembers(array $members): array
    {
        $byRef = collect($members)->keyBy('ref');
        $sorted = [];
        $visited = [];

        $visit = function (string $ref) use (&$visit, &$sorted, &$visited, $byRef) {
            if (isset($visited[$ref])) {
                return;
            }
            $visited[$ref] = true;
            $row = $byRef->get($ref);
            if (! $row) {
                return;
            }
            foreach (['father_ref', 'mother_ref'] as $key) {
                $parentRef = trim($row[$key] ?? '');
                if ($parentRef !== '' && $byRef->has($parentRef)) {
                    $visit($parentRef);
                }
            }
            $sorted[] = $row;
        };

        foreach ($members as $row) {
            $visit($row['ref']);
        }

        return $sorted;
    }

    private function resolveRef(?string $ref, array $refMap): ?int
    {
        if (! $ref || ! isset($refMap[$ref])) {
            return null;
        }

        return (int) $refMap[$ref];
    }

    private function createSacrament(int $parishionerId, array $row, int $parishId): void
    {
        Sacrament::create([
            'parishioner_id'     => $parishionerId,
            'type'               => $row['type'],
            'received_date'      => $row['received_date'] ?? null,
            'certificate_number' => $row['certificate_number'] ?? null,
            'book_number'        => $row['book_number'] ?? null,
            'giver'              => $row['giver'] ?? null,
            'sponsor'            => $row['sponsor'] ?? null,
            'parish_name'        => $row['parish_name'] ?? null,
            'note'               => $row['note'] ?? null,
            'parish_id'          => $parishId,
        ]);
    }

    private function resolveSaintId(array $payload): ?int
    {
        if (! empty($payload['saint_id'])) {
            return (int) $payload['saint_id'];
        }

        $saintName = trim($payload['saint_name'] ?? '');
        if ($saintName === '') {
            return null;
        }

        return Holymanagement::firstOrCreate(['name' => $saintName])->id;
    }

    private function buildLegacyNote(array $payload, string $referenceCode): ?string
    {
        $parts = [];

        if (! empty($payload['note'])) {
            $parts[] = trim($payload['note']);
        }

        $familyNotes = [];
        if (! empty($payload['family_head_name'])) {
            $familyNotes[] = 'Chủ hộ (sổ GĐ): ' . $payload['family_head_name'];
        }
        if (! empty($payload['spouse_name'])) {
            $familyNotes[] = 'Vợ/chồng: ' . $payload['spouse_name'];
        }
        if ($familyNotes) {
            $parts[] = implode('; ', $familyNotes);
        }

        $parts[] = 'Đăng ký tự khai (mã ' . $referenceCode . ')';

        return $parts ? implode("\n", $parts) : null;
    }

    private function moveAvatarToParishionerFolder(string $path): string
    {
        if (! Storage::disk('public')->exists($path)) {
            return $path;
        }

        $newPath = 'parishioners/avatars/' . basename($path);
        Storage::disk('public')->move($path, $newPath);

        return $newPath;
    }
}
