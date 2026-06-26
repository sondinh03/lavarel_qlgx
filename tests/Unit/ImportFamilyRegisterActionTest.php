<?php

namespace Tests\Unit;

use App\Actions\Parishioner\ImportFamilyRegisterAction;
use App\Models\Association;
use App\Models\Family;
use App\Models\Marriage;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Support\ParishionerEnumResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ImportFamilyRegisterActionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_import_persists_family_fields_and_scopes_association_by_parish(): void
    {
        $parishId = $this->defaultParishId();
        $otherParishId = $this->anotherParishId($parishId);
        $unique = uniqid('import_', false);

        $associationName = "HD Import {$unique}";
        $association = Association::create([
            'pid'    => $parishId,
            'name'   => $associationName,
            'status' => 1,
        ]);

        Association::create([
            'pid'    => $otherParishId,
            'name'   => $associationName,
            'status' => 1,
        ]);

        $parishGroup = ParishGroup::create([
            'parish_id' => $parishId,
            'name'      => "GH Import {$unique}",
        ]);

        $familiesMeta = [
            'H001' => [
                'family_temp_id' => 'H001',
                'code'           => null,
                'name'           => 'GĐ Import Test',
                'parish_group'   => $parishGroup->name,
                'address'        => '123 Đường Test',
                'province'       => null,
                'ward'           => null,
                'phone'          => '0912345678',
                'note'           => 'Ghi chú hộ import',
            ],
        ];

        $parishioners = [
            [
                'temp_id'          => 'P001',
                'family_temp_id'   => 'H001',
                'family_role'      => 'husband',
                'last_name'        => 'Nguyễn',
                'first_name'       => 'Chồng',
                'gender'           => 'Nam',
                'birthday'         => '',
                'birth_place'      => '',
                'birth_order'      => '',
                'saint_name'       => '',
                'father_temp_id'   => '',
                'mother_temp_id'   => '',
                'parish_name'      => '',
                'association_name' => $associationName,
                'note'             => '',
            ],
            [
                'temp_id'          => 'P002',
                'family_temp_id'   => 'H001',
                'family_role'      => 'wife',
                'last_name'        => 'Trần',
                'first_name'       => 'Vợ',
                'gender'           => 'Nữ',
                'birthday'         => '',
                'birth_place'      => '',
                'birth_order'      => '',
                'saint_name'       => '',
                'father_temp_id'   => '',
                'mother_temp_id'   => '',
                'parish_name'      => '',
                'association_name' => '',
                'note'             => '',
            ],
        ];

        $marriages = [
            [
                'husband_temp_id'    => 'P001',
                'wife_temp_id'       => 'P002',
                'married_date'       => '01/01/1990',
                'certificate_number' => 'HP-001',
                'parish_name'        => '',
                'witness_1'          => '',
                'witness_2'          => '',
                'priest_witness'     => '',
                'status'             => Marriage::STATUS_DIVORCED,
                'note'               => '',
            ],
        ];

        $result = app(ImportFamilyRegisterAction::class)->handle(
            $parishioners,
            [],
            $marriages,
            $parishId,
            $familiesMeta
        );

        $husband = Parishioner::find($result['temp_id_map']['P001']);
        $wife = Parishioner::find($result['temp_id_map']['P002']);
        $family = Family::find($husband->family_id);

        $this->assertNotNull($family);
        $this->assertSame('Ghi chú hộ import', $family->note);
        $this->assertSame('0912345678', $family->phone);
        $this->assertSame($parishGroup->id, $family->parish_group_id);

        $this->assertNotNull($husband);
        $this->assertNotNull($wife);

        $this->assertSame($association->id, $husband->association_id);
        $this->assertNull($wife->association_id);
        $this->assertSame($parishGroup->id, $husband->parish_area_id);
        $this->assertSame('123 Đường Test', $husband->permanent_residence);
        $this->assertSame(ParishionerEnumResolver::marriedFromMarriageStatus(Marriage::STATUS_DIVORCED), $husband->married);
        $this->assertSame(3, $wife->married);
    }

    protected function defaultParishId(): int
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
