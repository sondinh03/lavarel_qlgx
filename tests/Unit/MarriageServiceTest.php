<?php

namespace Tests\Unit;

use App\Models\Family;
use App\Models\Marriage;
use App\Models\Parishioner;
use App\Services\MarriageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MarriageServiceTest extends TestCase
{
    use DatabaseTransactions;

    private MarriageService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(MarriageService::class);
    }

    public function test_same_parish_assigns_family_id_to_both_spouses(): void
    {
        [$husband, $wife, $marriage] = $this->seedCoupleSameParish();

        $result = $this->service->processValidMarriage($marriage);

        $husband->refresh();
        $wife->refresh();

        $this->assertNotNull($result->family);
        $this->assertSame($result->family->id, $husband->family_id);
        $this->assertSame($result->family->id, $wife->family_id);
        $this->assertSame('husband', $husband->family_role);
        $this->assertSame('wife', $wife->family_role);
        $this->assertSame(1, $husband->married);
        $this->assertSame(1, $wife->married);
        $this->assertSame(0, $husband->birth_order);
        $this->assertSame(0, $wife->birth_order);
        $this->assertSame($husband->id, $result->marriage->husband_id);
        $this->assertSame($wife->id, $result->marriage->wife_id);
    }

    public function test_empty_origin_family_is_deactivated(): void
    {
        [$husband, $wife, $marriage, $originFamily] = $this->seedCoupleSameParish(asOnlyChild: true);

        $result = $this->service->processValidMarriage($marriage);

        $originFamily->refresh();
        $this->assertSame(0, $originFamily->member_count);
        $this->assertFalse((bool) $originFamily->status);
        $this->assertNotNull($result->family);
    }

    public function test_groom_as_head_triggers_reassign_or_warning(): void
    {
        [$husband, $wife, $marriage, $originFamily, $sibling] = $this->seedCoupleWithSiblingHead();

        $result = $this->service->processValidMarriage($marriage);

        $originFamily->refresh();
        $this->assertNotSame((int) $husband->id, (int) $originFamily->head_id);
        $this->assertContains($originFamily->head_id, [$sibling->id, null]);
    }

    public function test_different_parish_clones_bride_record(): void
    {
        [$husband, $wife, $marriage] = $this->seedCoupleDifferentParish();

        $result = $this->service->processValidMarriage($marriage);

        $wife->refresh();
        $this->assertFalse((bool) $wife->is_active);
        $this->assertStringContainsString('Lấy chồng', (string) $wife->left_reason);

        $activeWife = Parishioner::find($result->marriage->wife_id);
        $this->assertNotNull($activeWife);
        $this->assertNotSame($wife->id, $activeWife->id);
        $this->assertTrue((bool) $activeWife->is_active);
        $this->assertSame($wife->id, (int) $activeWife->transferred_from);
        $this->assertSame($result->family->id, $activeWife->family_id);
    }

    public function test_external_parish_marks_bride_inactive_without_clone(): void
    {
        [$husband, $wife, $marriage] = $this->seedCoupleExternalParish();

        $result = $this->service->processValidMarriage($marriage);

        $wife->refresh();
        $this->assertFalse((bool) $wife->is_active);
        $this->assertStringContainsString('ngoài hệ thống', (string) $wife->left_reason);
        $this->assertNull($wife->family_id);
        $this->assertNotEmpty($result->warnings);
        $this->assertSame($wife->id, $result->marriage->wife_id);
    }

    /**
     * @return array{0: Parishioner, 1: Parishioner, 2: Marriage}
     */
    protected function seedCoupleSameParish(bool $asOnlyChild = false): array
    {
        $parishId = $this->anyParishId();

        $originFamily = Family::create([
            'parish_id'    => $parishId,
            'name'         => 'Gia đình gốc test',
            'status'       => true,
            'member_count' => 0,
        ]);

        $husband = $this->makeParishioner('Nam', 'Chồng', 'male', $parishId, $originFamily->id, 'husband');
        $originFamily->update(['head_id' => $husband->id, 'member_count' => 1]);

        $wifeParish = $parishId;
        $wifeFamily = Family::create([
            'parish_id'    => $wifeParish,
            'name'         => 'Gia đình cô dâu test',
            'status'       => true,
            'member_count' => 0,
        ]);
        $wife = $this->makeParishioner('Nữ', 'Vợ', 'female', $wifeParish, $wifeFamily->id, 'wife');
        $wifeFamily->update(['head_id' => $wife->id, 'member_count' => 1]);

        $marriage = Marriage::create([
            'husband_id'   => $husband->id,
            'wife_id'      => $wife->id,
            'parish_id'    => $parishId,
            'parish_name'  => 'Test parish',
            'status'       => Marriage::STATUS_VALID,
            'married_date' => now()->toDateString(),
        ]);

        return $asOnlyChild
            ? [$husband, $wife, $marriage, $originFamily]
            : [$husband, $wife, $marriage];
    }

    /**
     * @return array{0: Parishioner, 1: Parishioner, 2: Marriage, 3: Family, 4: Parishioner}
     */
    protected function seedCoupleWithSiblingHead(): array
    {
        $parishId = $this->anyParishId();

        $originFamily = Family::create([
            'parish_id'    => $parishId,
            'name'         => 'Gia đình anh em test',
            'status'       => true,
            'member_count' => 0,
        ]);

        $husband = $this->makeParishioner('Anh', 'Chồng', 'male', $parishId, $originFamily->id, 'husband');
        $sibling = $this->makeParishioner('Em', 'Trai', 'male', $parishId, $originFamily->id, 'child');
        $originFamily->update(['head_id' => $husband->id, 'member_count' => 2]);

        $wifeFamily = Family::create([
            'parish_id'    => $parishId,
            'name'         => 'Gia đình cô dâu 2',
            'status'       => true,
            'member_count' => 0,
        ]);
        $wife = $this->makeParishioner('Chi', 'Dau', 'female', $parishId, $wifeFamily->id, 'wife');
        $wifeFamily->update(['member_count' => 1]);

        $marriage = Marriage::create([
            'husband_id'   => $husband->id,
            'wife_id'      => $wife->id,
            'parish_id'    => $parishId,
            'status'       => Marriage::STATUS_VALID,
            'married_date' => now()->toDateString(),
        ]);

        return [$husband, $wife, $marriage, $originFamily, $sibling];
    }

    /**
     * @return array{0: Parishioner, 1: Parishioner, 2: Marriage}
     */
    protected function seedCoupleDifferentParish(): array
    {
        $parishA = $this->anyParishId();
        $parishB = $this->anotherParishId($parishA);

        $husband = $this->makeParishioner('Chong', 'A', 'male', $parishA);
        $wife    = $this->makeParishioner('Co', 'Dau', 'female', $parishB);

        $marriage = Marriage::create([
            'husband_id'   => $husband->id,
            'wife_id'      => $wife->id,
            'parish_id'    => $parishA,
            'parish_name'  => 'Parish A',
            'status'       => Marriage::STATUS_VALID,
            'married_date' => now()->toDateString(),
        ]);

        return [$husband, $wife, $marriage];
    }

    /**
     * @return array{0: Parishioner, 1: Parishioner, 2: Marriage}
     */
    protected function seedCoupleExternalParish(): array
    {
        $parishA = $this->anyParishId();

        $husband = $this->makeParishioner('Chong', 'B', 'male', $parishA);
        $wife    = $this->makeParishioner('Co', 'DauB', 'female', $parishA + 99999);

        $marriage = Marriage::create([
            'husband_id'   => $husband->id,
            'wife_id'      => $wife->id,
            'parish_id'    => $parishA + 88888,
            'parish_name'  => 'Giáo xứ ngoài hệ thống XYZ',
            'status'       => Marriage::STATUS_VALID,
            'married_date' => now()->toDateString(),
        ]);

        return [$husband, $wife, $marriage];
    }

    protected function makeParishioner(
        string $last,
        string $first,
        string $gender,
        ?int $parishId,
        ?int $familyId = null,
        ?string $role = null
    ): Parishioner {
        return Parishioner::create([
            'last_name'   => $last,
            'first_name'  => $first,
            'gender'      => $gender,
            'parish_id'   => $parishId,
            'family_id'   => $familyId,
            'family_role' => $role,
            'married'     => 0,
            'is_active'   => true,
            'status'      => true,
        ]);
    }

    protected function anyParishId(): int
    {
        $id = \App\Models\ParishNew::query()->value('id');

        return $id ? (int) $id : 1;
    }

    protected function anotherParishId(int $exclude): int
    {
        $id = \App\Models\ParishNew::query()->where('id', '!=', $exclude)->value('id');

        return $id ? (int) $id : $exclude + 1;
    }
}
