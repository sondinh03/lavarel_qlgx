<?php

namespace App\Services;

use App\Actions\Family\FamilyMembershipService;
use App\DTOs\MarriageProcessResult;
use App\Models\Family;
use App\Models\Marriage;
use App\Models\Parishioner;
use App\Models\ParishNew;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MarriageService
{
    public function __construct(private FamilyMembershipService $membership) {}

    public function processValidMarriage(Marriage $marriage): MarriageProcessResult
    {
        if ($marriage->status !== Marriage::STATUS_VALID) {
            throw new InvalidArgumentException('Chỉ xử lý hôn phối có trạng thái hợp lệ.');
        }

        return DB::transaction(function () use ($marriage) {
            $marriage->load(['husband', 'wife']);

            $husband = $marriage->husband;
            $wife    = $marriage->wife;

            if (! $husband || ! $wife) {
                throw new InvalidArgumentException('Thiếu thông tin bên nam hoặc bên nữ.');
            }

            $warnings  = [];
            $auditLog  = [];
            $parishId  = (int) ($marriage->parish_id ?? $husband->parish_id);
            if ($parishId <= 0 || ! ParishNew::where('id', $parishId)->exists()) {
                $parishId = (int) $husband->parish_id;
            }
            $marriedOn = $marriage->married_date;

            $this->audit($auditLog, 'marriage.start', [
                'marriage_id' => $marriage->id,
                'husband_id'  => $husband->id,
                'wife_id'     => $wife->id,
                'parish_id'   => $parishId,
            ]);

            $warnings = array_merge(
                $warnings,
                $this->detachFromOriginFamily($husband, $auditLog)
            );

            $activeWife = $this->resolveActiveWife($wife, $marriage, $parishId, $marriedOn, $warnings, $auditLog);

            if ((int) $husband->parish_id !== $parishId && $parishId > 0) {
                $husband->update(['parish_id' => $parishId]);
                $this->audit($auditLog, 'husband.parish_updated', [
                    'parishioner_id' => $husband->id,
                    'parish_id'      => $parishId,
                ]);
            }

            $family = $this->createMarriageFamily($marriage, $husband, $parishId);
            $this->audit($auditLog, 'family.created', ['family_id' => $family->id, 'name' => $family->name]);

            $childIds = [];
            $this->membership->assignMembers($family, $husband->id, $activeWife?->id, $childIds);

            $this->finalizeParishionerMembership($husband, $family->id, 'husband');
            $this->audit($auditLog, 'husband.assigned', ['parishioner_id' => $husband->id, 'family_id' => $family->id]);

            if ($activeWife) {
                $this->finalizeParishionerMembership($activeWife, $family->id, 'wife');
                $this->audit($auditLog, 'wife.assigned', ['parishioner_id' => $activeWife->id, 'family_id' => $family->id]);
            } else {
                $warnings[] = 'Không thể gán bên nữ vào gia đình mới (chuyển xứ ngoài hệ thống).';
            }

            $marriage->update([
                'husband_id' => $husband->id,
                'wife_id'    => $activeWife?->id ?? $wife->id,
            ]);

            $this->audit($auditLog, 'marriage.updated_ids', [
                'husband_id' => $husband->id,
                'wife_id'    => $activeWife?->id ?? $wife->id,
            ]);

            Log::info('Marriage processed successfully', [
                'marriage_id' => $marriage->id,
                'family_id'   => $family->id,
                'audit'       => $auditLog,
                'warnings'    => $warnings,
            ]);

            return new MarriageProcessResult(
                $marriage->fresh(['husband', 'wife']),
                $family->fresh(['head', 'members']),
                $warnings,
                $auditLog,
            );
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $auditLog
     * @return array<int, string>
     */
    protected function detachFromOriginFamily(Parishioner $member, array &$auditLog): array
    {
        if (! $member->family_id) {
            return [];
        }

        $oldFamily = Family::find($member->family_id);
        if (! $oldFamily) {
            $member->update([
                'family_id'   => null,
                'family_role' => null,
                'father_id'   => null,
                'mother_id'   => null,
            ]);

            return [];
        }

        $wasHead = (int) $oldFamily->head_id === (int) $member->id;

        $member->update([
            'family_id'   => null,
            'family_role' => null,
            'father_id'   => null,
            'mother_id'   => null,
        ]);

        $this->membership->recount($oldFamily);
        $oldFamily->refresh();

        $this->audit($auditLog, 'member.detached', [
            'parishioner_id' => $member->id,
            'old_family_id'  => $oldFamily->id,
            'member_count'   => $oldFamily->member_count,
        ]);

        $warnings = [];

        if ((int) $oldFamily->member_count === 0) {
            $oldFamily->update(['status' => false]);
            $this->audit($auditLog, 'family.deactivated_empty', ['family_id' => $oldFamily->id]);
        } elseif ($wasHead) {
            $headWarning = $this->reassignFamilyHead($oldFamily, $auditLog);
            if ($headWarning) {
                $warnings[] = $headWarning;
            }
        }

        return $warnings;
    }

    /**
     * @param  array<int, array<string, mixed>>  $auditLog
     */
    protected function reassignFamilyHead(Family $family, array &$auditLog): ?string
    {
        $replacement = Parishioner::where('family_id', $family->id)
            ->where('family_role', 'husband')
            ->first();

        if (! $replacement) {
            $replacement = Parishioner::where('family_id', $family->id)
                ->orderByRaw('birthday IS NULL, birthday ASC')
                ->first();
        }

        if ($replacement) {
            if ($replacement->family_role !== 'husband') {
                $replacement->update(['family_role' => 'husband']);
            }
            $family->update(['head_id' => $replacement->id]);
            $this->audit($auditLog, 'family.head_reassigned', [
                'family_id'      => $family->id,
                'new_head_id'    => $replacement->id,
            ]);

            return null;
        }

        $family->update(['head_id' => null]);
        $this->audit($auditLog, 'family.head_cleared', ['family_id' => $family->id]);

        return "⚠️ Gia đình {$family->name} hiện chưa có chủ hộ. Vui lòng cập nhật.";
    }

    /**
     * @param  array<int, string>  $warnings
     * @param  array<int, array<string, mixed>>  $auditLog
     */
    protected function resolveActiveWife(
        Parishioner $wife,
        Marriage $marriage,
        int $parishId,
        $marriedOn,
        array &$warnings,
        array &$auditLog
    ): ?Parishioner {
        $sameParish = (int) $wife->parish_id === $parishId;

        if ($sameParish) {
            $warnings = array_merge($warnings, $this->detachFromOriginFamily($wife, $auditLog));

            return $wife->fresh();
        }

        $parishExists = $parishId > 0 && ParishNew::where('id', $parishId)->exists();

        if (! $parishExists) {
            return $this->handleExternalParishTransfer($wife, $marriage, $auditLog, $warnings);
        }

        $warnings = array_merge($warnings, $this->detachFromOriginFamily($wife, $auditLog));

        $newWife = $this->cloneParishionerToParish($wife, $parishId, $marriedOn, $marriage);
        $this->audit($auditLog, 'wife.cloned', [
            'old_id' => $wife->id,
            'new_id' => $newWife->id,
            'parish_id' => $parishId,
        ]);

        $wife->update([
            'is_active'   => false,
            'left_reason' => 'Lấy chồng - chuyển đến ' . ($marriage->parish_name ?? ParishNew::find($parishId)?->name ?? 'giáo xứ chồng'),
        ]);
        $this->audit($auditLog, 'wife.deactivated_old', ['parishioner_id' => $wife->id]);

        return $newWife;
    }

    /**
     * TH5: Giáo xứ chồng không có trong hệ thống.
     *
     * @param  array<int, string>  $warnings
     * @param  array<int, array<string, mixed>>  $auditLog
     */
    protected function handleExternalParishTransfer(
        Parishioner $wife,
        Marriage $marriage,
        array &$auditLog,
        array &$warnings
    ): ?Parishioner {
        $warnings = array_merge($warnings, $this->detachFromOriginFamily($wife, $auditLog));

        $destination = $marriage->parish_name ?? 'giáo xứ ngoài hệ thống';
        $noteSuffix  = trim('Chuyển đến giáo xứ chồng: ' . $destination);

        $wife->update([
            'is_active'   => false,
            'left_reason' => 'Lấy chồng — chuyển đến giáo xứ ngoài hệ thống',
            'note'        => trim(($wife->note ? $wife->note . "\n" : '') . $noteSuffix),
        ]);

        $warnings[] = "Bên nữ đã được đánh dấu rời xứ (chuyển đến: {$destination}). Không tạo hồ sơ mới vì giáo xứ không có trong hệ thống.";

        $this->audit($auditLog, 'wife.external_transfer', [
            'parishioner_id' => $wife->id,
            'destination'    => $destination,
        ]);

        return null;
    }

    protected function cloneParishionerToParish(
        Parishioner $source,
        int $parishId,
        $marriedOn,
        Marriage $marriage
    ): Parishioner {
        $targetParish = ParishNew::find($parishId);

        $attributes = $source->only($source->getFillable());
        unset($attributes['id']);

        $attributes['parish_id']        = $parishId;
        $attributes['deanery_id']       = $targetParish?->deanery_id ?? $attributes['deanery_id'] ?? null;
        $attributes['diocese_id']       = $targetParish?->diocese_id ?? $attributes['diocese_id'] ?? null;
        $attributes['family_id']        = null;
        $attributes['family_role']      = null;
        $attributes['father_id']        = null;
        $attributes['mother_id']        = null;
        $attributes['birth_order']      = 0;
        $attributes['married']          = 1;
        $attributes['is_active']        = true;
        $attributes['transferred_from'] = $source->id;
        $attributes['transferred_date'] = $marriedOn;
        $attributes['left_reason']      = null;
        $attributes['joined_date']      = $marriedOn ?? now()->toDateString();

        return Parishioner::create($attributes);
    }

    protected function createMarriageFamily(Marriage $marriage, Parishioner $husband, int $parishId): Family
    {
        return Family::create([
            'parish_id'            => $parishId ?: $husband->parish_id,
            'name'                 => 'Gia đình ' . $husband->full_name_with_saint,
            'note'                 => $marriage->note,
            'status'               => true,
            'address'              => null,
            'province'             => $marriage->place_province,
            'ward_id'              => $marriage->place_ward_id,
            'is_included_in_stats' => true,
            'member_count'         => 0,
        ]);
    }

    protected function finalizeParishionerMembership(Parishioner $member, int $familyId, string $role): void
    {
        $member->update([
            'family_id'   => $familyId,
            'family_role' => $role,
            'married'     => 1,
            'birth_order' => 0,
            'father_id'   => null,
            'mother_id'   => null,
            'is_active'   => true,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $auditLog
     */
    protected function audit(array &$auditLog, string $action, array $context = []): void
    {
        $auditLog[] = array_merge(['action' => $action, 'at' => now()->toIso8601String()], $context);
    }
}
