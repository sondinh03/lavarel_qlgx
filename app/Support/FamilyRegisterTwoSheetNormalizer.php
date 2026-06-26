<?php

namespace App\Support;

class FamilyRegisterTwoSheetNormalizer
{
    /** @var array<int, array<string, string|array<int, string>>> */
    private const SACRAMENT_FIELDS = [
        [
            'type' => 'baptism',
            'date' => ['rua_toi_ngay', 'rt_ngay', 'baptism_date'],
            'certificate_number' => ['rua_toi_so', 'rt_so', 'baptism_certificate'],
            'book_number' => ['rua_toi_quyen', 'rt_quyen', 'baptism_book'],
            'parish_name' => ['rua_toi_noi', 'rt_noi', 'baptism_parish'],
            'giver' => ['rua_toi_ban', 'rt_ban', 'baptism_giver'],
            'sponsor' => ['rua_toi_do_dau', 'rt_do_dau', 'baptism_sponsor'],
            'note' => ['rua_toi_ghi_chu', 'rt_ghi_chu', 'baptism_note'],
        ],
        [
            'type' => 'communion',
            'date' => ['ruoc_le_ngay', 'tt_ngay', 'communion_date'],
            'certificate_number' => ['ruoc_le_so', 'tt_so', 'communion_certificate'],
            'book_number' => ['ruoc_le_quyen', 'tt_quyen', 'communion_book'],
            'parish_name' => ['ruoc_le_noi', 'tt_noi', 'communion_parish'],
            'giver' => ['ruoc_le_ban', 'tt_ban', 'communion_giver'],
            'sponsor' => ['ruoc_le_do_dau', 'tt_do_dau', 'communion_sponsor'],
            'note' => ['ruoc_le_ghi_chu', 'tt_ghi_chu', 'communion_note'],
        ],
        [
            'type' => 'confirmation',
            'date' => ['them_suc_ngay', 'ts_ngay', 'confirmation_date'],
            'certificate_number' => ['them_suc_so', 'ts_so', 'confirmation_certificate'],
            'book_number' => ['them_suc_quyen', 'ts_quyen', 'confirmation_book'],
            'parish_name' => ['them_suc_noi', 'ts_noi', 'confirmation_parish'],
            'giver' => ['them_suc_ban', 'ts_ban', 'confirmation_giver'],
            'sponsor' => ['them_suc_do_dau', 'ts_do_dau', 'confirmation_sponsor'],
            'note' => ['them_suc_ghi_chu', 'ts_ghi_chu', 'confirmation_note'],
        ],
        [
            'type' => 'anointing',
            'date' => ['xung_toi_ngay', 'xd_ngay', 'anointing_date'],
            'certificate_number' => ['xung_toi_so', 'xd_so', 'anointing_certificate'],
            'book_number' => ['xung_toi_quyen', 'xd_quyen', 'anointing_book'],
            'parish_name' => ['xung_toi_noi', 'xd_noi', 'anointing_parish'],
            'giver' => ['xung_toi_ban', 'xd_ban', 'anointing_giver'],
            'sponsor' => ['xung_toi_do_dau', 'xd_do_dau', 'anointing_sponsor'],
            'note' => ['xung_toi_ghi_chu', 'xd_ghi_chu', 'anointing_note'],
        ],
        [
            'type' => 'holy_orders',
            'date' => ['truyen_chuc_ngay', 'tch_ngay', 'holy_orders_date'],
            'certificate_number' => ['truyen_chuc_so', 'tch_so', 'holy_orders_certificate'],
            'book_number' => ['truyen_chuc_quyen', 'tch_quyen', 'holy_orders_book'],
            'parish_name' => ['truyen_chuc_noi', 'tch_noi', 'holy_orders_parish'],
            'giver' => ['truyen_chuc_ban', 'tch_ban', 'holy_orders_giver'],
            'sponsor' => ['truyen_chuc_do_dau', 'tch_do_dau', 'holy_orders_sponsor'],
            'note' => ['truyen_chuc_ghi_chu', 'tch_ghi_chu', 'holy_orders_note'],
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
            $familyTempId = $this->cell($row, 'ma_ho_gia_dinh', 'ma_ho', 'family_temp_id');

            if ($familyTempId === '' && $this->cell($row, 'ten_ho_gia_dinh', 'ten_ho', 'family_name', 'name') === '') {
                continue;
            }

            if ($familyTempId === '') {
                $errors[] = "ho_gia_dinh dòng {$rowNumber}: thiếu <strong>ma_ho_gia_dinh</strong>.";
                continue;
            }

            if (isset($families[$familyTempId])) {
                $errors[] = "ho_gia_dinh dòng {$rowNumber}: <strong>ma_ho_gia_dinh \"{$familyTempId}\"</strong> bị trùng.";
                continue;
            }

            $families[$familyTempId] = [
                'family_temp_id'     => $familyTempId,
                'code'               => $this->cell($row, 'ma_gia_dinh', 'ma_gd', 'family_code', 'code'),
                'name'               => $this->cell($row, 'ten_ho_gia_dinh', 'ten_ho', 'family_name', 'name'),
                'parish_group'       => $this->cell($row, 'giao_ho', 'gio_ho', 'parish_group', 'parish_group_name'),
                'address'            => $this->cell($row, 'dia_chi', 'address'),
                'province'           => $this->cell($row, 'tinh_thanh', 'tinh', 'province'),
                'ward'               => $this->cell($row, 'xa_phuong', 'xa', 'ward', 'ward_name'),
                'phone'              => $this->cell($row, 'dien_thoai', 'phone', 'contact_phone'),
                'note'               => $this->cell($row, 'ghi_chu', 'note'),
                'married_date'       => $this->cell($row, 'hon_phoi_ngay', 'hp_ngay', 'married_date'),
                'certificate_number' => $this->cell($row, 'hon_phoi_so', 'hp_so', 'certificate_number'),
                'marriage_parish'    => $this->cell($row, 'hon_phoi_giao_xu', 'hp_gx', 'parish_name'),
                'witness_1'          => $this->cell($row, 'hon_phoi_nhan_chung_1', 'hp_nc1', 'witness_1'),
                'witness_2'          => $this->cell($row, 'hon_phoi_nhan_chung_2', 'hp_nc2', 'witness_2'),
                'priest_witness'     => $this->cell($row, 'hon_phoi_linh_muc', 'hp_lm', 'priest_witness'),
                'marriage_status'    => ParishionerEnumResolver::parseMarriageRecordStatus(
                    $this->cell($row, 'hon_phoi_trang_thai', 'hp_trang_thai', 'status') ?: 'Hợp lệ'
                ),
                'marriage_note'      => $this->cell($row, 'hon_phoi_ghi_chu', 'hp_ghi_chu', 'note'),
            ];
        }

        $parishioners = [];
        $sacraments = [];
        $marriages = [];
        $membersByFamily = [];

        foreach ($classified['members'] as $index => $row) {
            $rowNumber = $index + 6;
            $tempId = $this->cell($row, 'ma_thanh_vien', 'ma_tv', 'temp_id');
            $familyTempId = $this->cell($row, 'ma_ho_gia_dinh', 'ma_ho', 'family_temp_id');

            if ($tempId === '' && $this->cell($row, 'ho', 'last_name') === '' && $this->cell($row, 'ten', 'first_name') === '') {
                continue;
            }

            if ($tempId === '') {
                $errors[] = "thanh_vien dòng {$rowNumber}: thiếu <strong>ma_thanh_vien</strong>.";
                continue;
            }

            if ($familyTempId === '') {
                $errors[] = "thanh_vien dòng {$rowNumber} ({$tempId}): thiếu <strong>ma_ho_gia_dinh</strong>.";
            } elseif (! isset($families[$familyTempId])) {
                $errors[] = "thanh_vien dòng {$rowNumber} ({$tempId}): <strong>ma_ho_gia_dinh \"{$familyTempId}\"</strong> không có trong sheet ho_gia_dinh.";
            }

            $rawRole = $this->cell($row, 'vai_tro', 'family_role');
            $familyRole = ParishionerEnumResolver::parseFamilyRole($rawRole);

            $parishionerRow = [
                'temp_id'        => $tempId,
                'family_temp_id' => $familyTempId,
                'family_role'    => $familyRole ?? $rawRole,
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
                'association_name' => $this->cell($row, 'hoi_doan', 'ten_hoi_doan', 'association_name'),
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
                $familyMeta = $families[$familyTempId] ?? [];

                if ($this->cell($familyMeta, 'married_date') === '') {
                    $legacyRow = collect($classified['members'])->first(function ($row) use ($husband) {
                        return $this->cell($row, 'ma_thanh_vien', 'ma_tv', 'temp_id') === $husband['temp_id']
                            && $this->cell($row, 'hon_phoi_ngay', 'hp_ngay', 'married_date') !== '';
                    }) ?? collect($classified['members'])->first(function ($row) use ($wife) {
                        return $this->cell($row, 'ma_thanh_vien', 'ma_tv', 'temp_id') === $wife['temp_id']
                            && $this->cell($row, 'hon_phoi_ngay', 'hp_ngay', 'married_date') !== '';
                    });

                    if ($legacyRow) {
                        $familyMeta = array_merge($familyMeta, [
                            'married_date'       => $this->cell($legacyRow, 'hon_phoi_ngay', 'hp_ngay', 'married_date'),
                            'certificate_number' => $this->cell($legacyRow, 'hon_phoi_so', 'hp_so', 'certificate_number'),
                            'marriage_parish'    => $this->cell($legacyRow, 'hon_phoi_giao_xu', 'hp_gx', 'parish_name'),
                            'witness_1'          => $this->cell($legacyRow, 'hon_phoi_nhan_chung_1', 'hp_nc1', 'witness_1'),
                            'witness_2'          => $this->cell($legacyRow, 'hon_phoi_nhan_chung_2', 'hp_nc2', 'witness_2'),
                            'priest_witness'     => $this->cell($legacyRow, 'hon_phoi_linh_muc', 'hp_lm', 'priest_witness'),
                            'marriage_status'    => ParishionerEnumResolver::parseMarriageRecordStatus(
                                $this->cell($legacyRow, 'hon_phoi_trang_thai', 'hp_trang_thai', 'status') ?: 'Hợp lệ'
                            ),
                            'marriage_note'      => $this->cell($legacyRow, 'hon_phoi_ghi_chu', 'hp_ghi_chu', 'note'),
                        ]);
                    }
                }

                $marriages[] = $this->buildMarriageRow(
                    $husband['temp_id'],
                    $wife['temp_id'],
                    $familyMeta
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

            $hasFamilyId = $this->hasAnyKey($first, 'ma_ho_gia_dinh', 'ma_ho', 'family_temp_id');
            $hasMemberId = $this->hasAnyKey($first, 'ma_thanh_vien', 'ma_tv', 'temp_id');
            $hasFamilyName = $this->hasAnyKey($first, 'ten_ho_gia_dinh', 'ten_ho', 'family_name');
            $hasPersonName = $this->hasAnyKey($first, 'ho', 'last_name') && $this->hasAnyKey($first, 'ten', 'first_name');
            $hasMarriageOnFamily = $this->hasAnyKey($first, 'hon_phoi_ngay', 'hp_ngay', 'married_date');

            if ($hasFamilyId && $hasFamilyName && ! $hasMemberId) {
                $families = $rows;
            } elseif ($hasMemberId && $hasPersonName) {
                $members = $rows;
            } elseif ($hasFamilyId && $hasMemberId && $hasPersonName) {
                $members = $rows;
            } elseif ($hasFamilyId && $hasMarriageOnFamily && ! $hasMemberId) {
                $families = $rows;
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
     * @param  array<string, mixed>  $familyMeta
     * @return array<string, mixed>
     */
    private function buildMarriageRow(string $husbandTempId, string $wifeTempId, array $familyMeta): array
    {
        return [
            'husband_temp_id'    => $husbandTempId,
            'wife_temp_id'       => $wifeTempId,
            'married_date'       => $familyMeta['married_date'] ?? '',
            'certificate_number' => $familyMeta['certificate_number'] ?? '',
            'parish_name'        => $familyMeta['marriage_parish'] ?? '',
            'witness_1'          => $familyMeta['witness_1'] ?? '',
            'witness_2'          => $familyMeta['witness_2'] ?? '',
            'priest_witness'     => $familyMeta['priest_witness'] ?? '',
            'status'             => $familyMeta['marriage_status'] ?? ParishionerEnumResolver::parseMarriageRecordStatus('Hợp lệ'),
            'note'               => $familyMeta['marriage_note'] ?? '',
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
