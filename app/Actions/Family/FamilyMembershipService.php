<?php

namespace App\Actions\Family;

use App\Models\Family;
use App\Models\Parishioner;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FamilyMembershipService
{
  /**
   * Gán đầy đủ thành viên hộ (chồng, vợ, con) và đồng bộ head_id, member_count.
   *
   * @param  array<int>  $childIds
   * @param  array<int>  $additionalMemberIds  Thành viên khác (role other) giữ nguyên trong hộ
   */
  public function assignMembers(
    Family $family,
    ?int $husbandId,
    ?int $wifeId,
    array $childIds,
    ?Collection $previousMemberIds = null,
    array $additionalMemberIds = []
  ): void {
    if (! $husbandId) {
      throw new InvalidArgumentException('Phải chọn chủ hộ (chồng).');
    }

    DB::transaction(function () use ($family, $husbandId, $wifeId, $childIds, $previousMemberIds, $additionalMemberIds) {
      $husband = Parishioner::findOrFail($husbandId);
      $wife    = $wifeId ? Parishioner::findOrFail($wifeId) : null;

      $newMemberIds = collect([$husbandId, $wifeId])
        ->merge($childIds)
        ->merge($additionalMemberIds)
        ->filter()
        ->unique()
        ->values();

      $oldMemberIds = $previousMemberIds
        ?? Parishioner::where('family_id', $family->id)->pluck('id');

      Parishioner::whereIn('id', $oldMemberIds)
        ->whereNotIn('id', $newMemberIds)
        ->update([
          'family_id'   => null,
          'father_id'   => null,
          'mother_id'   => null,
          'family_role' => null,
        ]);

      $husband->update([
        'family_id'   => $family->id,
        'family_role' => 'husband',
      ]);

      if ($wife) {
        $wife->update([
          'family_id'   => $family->id,
          'family_role' => 'wife',
        ]);
      }

      $children = Parishioner::whereIn('id', $childIds)->get();
      [$fatherId, $motherId] = $this->resolveParentIds($husband, $wife);

      foreach ($children as $child) {
        $child->update([
          'family_id'   => $family->id,
          'family_role' => 'child',
          'father_id'   => $fatherId,
          'mother_id'   => $motherId,
        ]);
      }

      $others = Parishioner::whereIn('id', $additionalMemberIds)->get();
      foreach ($others as $other) {
        $other->update([
          'family_id'   => $family->id,
          'family_role' => 'other',
          'father_id'   => null,
          'mother_id'   => null,
        ]);
      }

      $family->update(['head_id' => $husband->id]);
      $this->recount($family);
    });
  }

  /**
   * Thêm giáo dân chưa có hộ vào gia đình.
   *
   * @param  array<int>  $parishionerIds
   */
  public function addMembers(Family $family, array $parishionerIds, string $defaultRole = 'other'): int
  {
    if (empty($parishionerIds)) {
      return 0;
    }

    if (! in_array($defaultRole, ['husband', 'wife', 'child', 'other'], true)) {
      throw new InvalidArgumentException('Vai trò không hợp lệ.');
    }

    return DB::transaction(function () use ($family, $parishionerIds, $defaultRole) {
      $parishioners = Parishioner::whereIn('id', $parishionerIds)
        ->whereNull('family_id')
        ->get();

      if ($parishioners->isEmpty()) {
        return 0;
      }

      $husband = Parishioner::where('family_id', $family->id)
        ->where('family_role', 'husband')
        ->first();
      $wife = Parishioner::where('family_id', $family->id)
        ->where('family_role', 'wife')
        ->first();
      [$fatherId, $motherId] = $this->resolveParentIds($husband, $wife);

      foreach ($parishioners as $parishioner) {
        $role = $defaultRole;
        $data = [
          'family_id'   => $family->id,
          'family_role' => $role,
        ];

        if ($role === 'child') {
          $data['father_id'] = $fatherId;
          $data['mother_id'] = $motherId;
        }

        $parishioner->update($data);
      }

      $this->recount($family);

      return $parishioners->count();
    });
  }

  public function removeMember(Family $family, Parishioner $member): void
  {
    if ((int) $member->family_id !== (int) $family->id) {
      throw new InvalidArgumentException('Thành viên không thuộc gia đình này.');
    }

    DB::transaction(function () use ($family, $member) {
      $member->update([
        'family_id'   => null,
        'family_role' => null,
        'father_id'   => null,
        'mother_id'   => null,
      ]);

      if ((int) $family->head_id === (int) $member->id) {
        $family->update(['head_id' => null]);
      }

      $this->recount($family);
    });
  }

  public function setRole(Family $family, Parishioner $member, string $role): void
  {
    if (! in_array($role, ['husband', 'wife', 'child', 'other'], true)) {
      throw new InvalidArgumentException('Vai trò không hợp lệ.');
    }

    if ((int) $member->family_id !== (int) $family->id) {
      throw new InvalidArgumentException('Thành viên không thuộc gia đình này.');
    }

    if (in_array($role, ['husband', 'wife'], true)) {
      $exists = Parishioner::where('family_id', $family->id)
        ->where('family_role', $role)
        ->where('id', '!=', $member->id)
        ->exists();

      if ($exists) {
        $label = $role === 'husband' ? 'chồng' : 'vợ';
        throw new InvalidArgumentException("Gia đình đã có người giữ vai trò {$label}.");
      }
    }

    DB::transaction(function () use ($family, $member, $role) {
      $data = ['family_role' => $role];

      if ($role === 'child') {
        $husband = Parishioner::where('family_id', $family->id)
          ->where('family_role', 'husband')
          ->first();
        $wife = Parishioner::where('family_id', $family->id)
          ->where('family_role', 'wife')
          ->first();
        [$fatherId, $motherId] = $this->resolveParentIds($husband, $wife);
        $data['father_id'] = $fatherId;
        $data['mother_id'] = $motherId;
      } else {
        $data['father_id'] = null;
        $data['mother_id'] = null;
      }

      $member->update($data);

      if ($role === 'husband') {
        $family->update(['head_id' => $member->id]);
      } elseif ($role !== 'husband' && (int) $family->head_id === (int) $member->id) {
        $family->update(['head_id' => null]);
      }

      $this->recount($family);
    });
  }

  public function recount(Family $family): void
  {
    $count = Parishioner::where('family_id', $family->id)->count();
    $family->update(['member_count' => $count]);
  }

  public function dissolve(Family $family): void
  {
    DB::transaction(function () use ($family) {
      Parishioner::where('family_id', $family->id)->update([
        'family_id'   => null,
        'family_role' => null,
        'father_id'   => null,
        'mother_id'   => null,
      ]);

      $family->update([
        'head_id'      => null,
        'member_count' => 0,
      ]);

      $family->delete();
    });
  }

  /**
   * @return array{0: ?int, 1: ?int}
   */
  protected function resolveParentIds(?Parishioner $husband, ?Parishioner $wife): array
  {
    $fatherId = null;
    $motherId = null;

    foreach ([$husband, $wife] as $parent) {
      if (! $parent) {
        continue;
      }

      if ($parent->gender === 'male') {
        $fatherId = $parent->id;
      } elseif ($parent->gender === 'female') {
        $motherId = $parent->id;
      }
    }

    return [$fatherId, $motherId];
  }
}
