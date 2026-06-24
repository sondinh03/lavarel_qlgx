<?php

namespace App\Support;

class FamilyRegisterTwoSheetNormalizer
{
    /** @var array<int, array<string, string>> */
    private const SACRAMENT_FIELDS = [
        [
            'type' => 'baptism',
            'date' => ['rt_ngay', 'baptism_date'],
            'certificate_number' => ['rt_so', 'baptism_certificate'],
            'book_number' => ['rt_quyen', 'baptism_book'],
            'parish_name' => ['rt_noi', 'baptism_parish'],
            'giver' => ['rt_ban', 'baptism_giver'],
            'sponsor' => ['rt_do_dau', 'baptism_sponsor'],
            'note' => ['rt_ghi_chu', 'baptism_note'],
        ],
        [
            'type' => 'communion',
            'date' => ['tt_ngay', 'communion_date'],
            'certificate_number' => ['tt_so', 'communion_certificate'],
            'book_number' => ['tt_quyen', 'communion_book'],
            'parish_name' => ['tt_noi', 'communion_parish'],
            'giver' => ['tt_ban', 'communion_giver'],
            'sponsor' => ['tt_do_dau', 'communion_sponsor'],
            'note' => ['tt_ghi_chu', 'communion_note'],
        ],
        [
            'type' => 'confirmation',
            'date' => ['ts_ngay', 'confirmation_date'],
            'certificate_number' => ['ts_so', 'confirmation_certificate'],
            'book_number' => ['ts_quyen', 'confirmation_book'],
            'parish_name' => ['ts_noi', 'confirmation_parish'],
            'giver' => ['ts_ban', 'confirmation_giver'],
            'sponsor' => ['ts_do_dau', 'confirmation_sponsor'],
            'note' => ['ts_ghi_chu', 'confirmation_note'],
        ],
        [
            'type' => 'anointing',
            'date' => ['xd_ngay', 'anointing_date'],
            'certificate_number' => ['xd_so', 'anointing_certificate'],
            'book_number' => ['xd_quyen', 'anointing_book'],
            'parish_name' => ['xd_noi', 'anointing_parish'],
            'giver' => ['xd_ban', 'anointing_giver'],
            'sponsor' => ['xd_do_dau', 'anointing_sponsor'],
            'note' => ['xd_ghi_chu', 'anointing_note'],
        ],
        [
            'type' => 'holy_orders',
            'date' => ['tch_ngay', 'holy_orders_date'],
            'certificate_number' => ['tch_so', 'holy_orders_certificate'],
            'book_number' => ['tch_quyen', 'holy_orders_book'],
            'parish_name' => ['tch_noi', 'holy_orders_parish'],
            'giver' => ['tch_ban', 'holy_orders_giver'],
            'sponsor' => ['tch_do_dau', 'holy_orders_sponsor'],
            'note' => ['tch_ghi_chu', 'holy_orders_note'],
        ],
    ];

    /**
     * @param  array<int|string, array<int, array<string, mixed>>>  $allSheets
     * @return array{
     *   families: array<string, array<string, mixed>>,
     *   parishioners: array<int, array<string, mixed>>,
     *   sacraments: array<int, array<string, mixed>>,
     *   marriages: array<int, array<string, mixed>>,
     *   errors: array<string>
     * }
     */
    public function normalize(array $allSheets): array
    {
        $classified = $this->classifySheets($allSheets);
        $errors = [];

        if (empty($classified['families']) || empty($classified['members'])) {
            return [
                'families' => [],
                'parishioners' => [],
                'sacraments' => [],
                'marriages' => [],
                'errors' => ['Không nhận dạng được 2 sheet <strong>ho_gia_dinh</strong> và <strong>thanh_vien</strong>.'],
            ];
        }

        $families = [];
        foreach ($classified['families'] as $index => $row) {
            $rowNumber = $index + 6;
            $familyTempId = $this->cell($row, 'ma_ho', 'family_temp_id');

            if ($familyTempId === '' && $this->cell($row, 'ten_ho', 'family_name', 'name') === '') {
                continue;
            }

            if ($familyTempId === '') {
                $errors[] = "ho_gia_dinh dòng {$rowNumber}: thiếu <strong>ma_ho</strong>.";
                continue;
            }

            if (isset($families[$familyTempId])) {
                $errors[] = "ho_gia_dinh dòng {$rowNumber}: <strong>ma_ho \"{$familyTempId}\"</strong> bị trùng.";
                continue;
            }

            $families[$familyTempId] = [
                'family_temp_id' => $familyTempId,
                'code'           => $this->cell($row, 'ma_gd', 'family_code', 'code'),
                'name'           => $this->cell($row, 'ten_ho', 'family_name', 'name'),
                'parish_group'   => $this->cell($row, 'gio_ho', 'parish_group', 'parish_group_name'),
                'address'        => $this->cell($row, 'dia_chi', 'address'),
                'province'       => $this->cell($row, 'tinh', 'province'),
                'ward'           => $this->cell($row, 'xa', 'ward', 'ward_name'),
                'phone'          => $this->cell($row, 'dien_thoai', 'phone', 'contact_phone'),
                'note'           => $this->cell($row, 'ghi_chu', 'note'),
            ];
        }

        $parishioners = [];
        $sacraments = [];
        $marriages = [];
        $membersByFamily = [];

        foreach ($classified['members'] as $index => $row) {
            $rowNumber = $index + 6;
            $tempId = $this->cell($row, 'ma_tv', 'temp_id');
            $familyTempId = $this->cell($row, 'ma_ho', 'family_temp_id');

            if ($tempId === '' && $this->cell($row, 'ho', 'last_name') === '' && $this->cell($row, 'ten', 'first_name') === '') {
                continue;
            }

            if ($tempId === '') {
                $errors[] = "thanh_vien dòng {$rowNumber}: thiếu <strong>ma_tv</strong>.";
                continue;
            }

            if ($familyTempId === '') {
                $errors[] = "thanh_vien dòng {$rowNumber} ({$tempId}): thiếu <strong>ma_ho</strong>.";
            } elseif (! isset($families[$familyTempId])) {
                $errors[] = "thanh_vien dòng {$rowNumber} ({$tempId}): <strong>ma_ho \"{$familyTempId}\"</strong> không có trong sheet ho_gia_dinh.";
            }

            $parishionerRow = [
                'temp_id'        => $tempId,
                'family_temp_id' => $familyTempId,
                'family_role'    => $this->cell($row, 'vai_tro', 'family_role'),
                'last_name'      => $this->cell($row, 'ho', 'last_name'),
                'first_name'     => $this->cell($row, 'ten', 'first_name'),
                'gender'         => $this->cell($row, 'gioi_tinh', 'gender'),
                'birthday'       => $row['ngay_sinh'] ?? $row['birthday'] ?? '',
                'birth_place'    => $this->cell($row, 'noi_sinh', 'birth_place'),
                'birth_order'    => $this->cell($row, 'con_thu', 'birth_order'),
                'saint_name'     => $this->cell($row, 'ten_thanh', 'saint_name'),
                'father_temp_id' => $this->cell($row, 'ma_cha', 'father_temp_id'),
                'mother_temp_id' => $this->cell($row, 'ma_me', 'mother_temp_id'),
                'parish_name'    => $this->cell($row, 'gio_xu', 'parish_name'),
                'note'           => $this->cell($row, 'ghi_chu', 'note'),
            ];

            $parishioners[] = $parishionerRow;

            foreach (self::SACRAMENT_FIELDS as $fields) {
                $date = $this->cellFromMap($row, $fields['date']);
                if ($date === '') {
                    continue;
                }

                $sacraments[] = [
                    'parishioner_temp_id' => $tempId,
                    'type'                => $fields['type'],
                    'received_date'       => $date,
                    'certificate_number'  => $this->cellFromMap($row, $fields['certificate_number']),
                    'book_number'         => $this->cellFromMap($row, $fields['book_number']),
                    'parish_name'         => $this->cellFromMap($row, $fields['parish_name']),
                    'giver'               => $this->cellFromMap($row, $fields['giver']),
                    'sponsor'             => $this->cellFromMap($row, $fields['sponsor']),
                    'note'                => $this->cellFromMap($row, $fields['note']),
                ];
            }

            $membersByFamily[$familyTempId][] = $parishionerRow;
        }

        foreach ($membersByFamily as $familyTempId => $members) {
            $husband = collect($members)->first(fn ($m) => mb_strtolower($m['family_role'], 'UTF-8') === 'husband');
            $wife = collect($members)->first(fn ($m) => mb_strtolower($m['family_role'], 'UTF-8') === 'wife');

            if (! $husband || ! $wife) {
                continue;
            }

            $pairKey = $this->marriagePairKey($husband['temp_id'], $wife['temp_id']);
            $already = collect($marriages)->contains(fn ($m) => $this->marriagePairKey($m['husband_temp_id'], $m['wife_temp_id']) === $pairKey);

            if (! $already) {
                $hpRow = collect($classified['members'])->first(function ($row) use ($husband) {
                    return $this->cell($row, 'ma_tv', 'temp_id') === $husband['temp_id']
                        && $this->cell($row, 'hp_ngay', 'married_date') !== '';
                }) ?? collect($classified['members'])->first(function ($row) use ($wife) {
                    return $this->cell($row, 'ma_tv', 'temp_id') === $wife['temp_id']
                        && $this->cell($row, 'hp_ngay', 'married_date') !== '';
                });

                $marriages[] = $this->buildMarriageRow(
                    $husband['temp_id'],
                    $wife['temp_id'],
                    $hpRow ?? []
                );
            }
        }

        return [
            'families'     => $families,
            'parishioners' => $parishioners,
            'sacraments'   => $sacraments,
            'marriages'    => $this->uniqueMarriages($marriages),
            'errors'       => $errors,
        ];
    }

    /**
     * @param  array<int|string, array<int, array<string, mixed>>>  $allSheets
     * @return array{families: array, members: array}
     */
    private function classifySheets(array $allSheets): array
    {
        $families = [];
        $members = [];

        foreach ($allSheets as $rows) {
            if (empty($rows)) {
                continue;
            }

            $first = $rows[0] ?? [];
            $keys = array_map('strval', array_keys($first));

            $hasFamilyId = $this->hasAnyKey($first, 'ma_ho', 'family_temp_id');
            $hasMemberId = $this->hasAnyKey($first, 'ma_tv', 'temp_id');
            $hasFamilyName = $this->hasAnyKey($first, 'ten_ho', 'family_name');
            $hasPersonName = $this->hasAnyKey($first, 'ho', 'last_name') && $this->hasAnyKey($first, 'ten', 'first_name');

            if ($hasFamilyId && $hasFamilyName && ! $hasMemberId) {
                $families = $rows;
            } elseif ($hasMemberId && $hasPersonName) {
                $members = $rows;
            } elseif ($hasFamilyId && $hasMemberId && $hasPersonName) {
                $members = $rows;
            }
        }

        return compact('families', 'members');
    }

    public static function detect(array $allSheets): bool
    {
        $instance = new self;
        $classified = $instance->classifySheets($allSheets);

        return ! empty($classified['families']) && ! empty($classified['members']);
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<int, string>  $keys
     */
    private function cellFromMap(array $row, array $keys): string
    {
        return $this->cell($row, ...$keys);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function cell(array $row, string ...$keys): string
    {
        foreach ($keys as $key) {
            $value = trim((string) ($row[$key] ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function hasAnyKey(array $row, string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $row)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function buildMarriageRow(string $husbandTempId, string $wifeTempId, array $row): array
    {
        return [
            'husband_temp_id'    => $husbandTempId,
            'wife_temp_id'       => $wifeTempId,
            'married_date'       => $this->cell($row, 'hp_ngay', 'married_date'),
            'certificate_number' => $this->cell($row, 'hp_so', 'certificate_number'),
            'parish_name'        => $this->cell($row, 'hp_gx', 'parish_name'),
            'witness_1'          => $this->cell($row, 'hp_nc1', 'witness_1'),
            'witness_2'          => $this->cell($row, 'hp_nc2', 'witness_2'),
            'priest_witness'     => $this->cell($row, 'hp_lm', 'priest_witness'),
            'status'             => $this->cell($row, 'hp_trang_thai', 'status') ?: 'valid',
            'note'               => $this->cell($row, 'hp_ghi_chu', 'note'),
        ];
    }

    private function marriagePairKey(string $husbandTempId, string $wifeTempId): string
    {
        return $husbandTempId . '|' . $wifeTempId;
    }

    /**
     * @param  array<int, array<string, mixed>>  $marriages
     * @return array<int, array<string, mixed>>
     */
    private function uniqueMarriages(array $marriages): array
    {
        $seen = [];
        $unique = [];

        foreach ($marriages as $marriage) {
            $key = $this->marriagePairKey($marriage['husband_temp_id'], $marriage['wife_temp_id']);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $marriage;
        }

        return $unique;
    }
}
