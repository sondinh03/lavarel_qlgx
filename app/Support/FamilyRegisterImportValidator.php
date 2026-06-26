<?php

namespace App\Support;

use App\Models\Holymanagement;
use App\Models\ParishNew;
use App\Models\Association;

class FamilyRegisterImportValidator
{
    /**
     * @param  array<int, array<int, array<string, mixed>>>  $allSheets
     * @return array{
     *   errors: array<string>,
     *   warnings: array<string>,
     *   parishioners: array,
     *   sacraments: array,
     *   marriages: array,
     *   ready: bool
     * }
     */
    public function validate(array $allSheets, ?int $parishId = null): array
    {
        $classified = $this->classifySheets($allSheets);

        $familiesMeta = [];
        $parishioners = $classified['parishioners'];
        $sacraments   = $classified['sacraments'];
        $marriages    = $classified['marriages'];

        if (FamilyRegisterTwoSheetNormalizer::detect($allSheets)) {
            $normalized = app(FamilyRegisterTwoSheetNormalizer::class)->normalize($allSheets);

            if (! empty($normalized['errors'])) {
                return [
                    'errors'       => $normalized['errors'],
                    'warnings'     => [],
                    'families'     => [],
                    'parishioners' => [],
                    'sacraments'   => [],
                    'marriages'    => [],
                    'ready'        => false,
                ];
            }

            $familiesMeta = $normalized['families'];
            $parishioners = $this->rawRowsFromNormalized($normalized['parishioners']);
            $sacraments   = $this->rawRowsFromNormalized($normalized['sacraments']);
            $marriages    = $this->rawRowsFromNormalized($normalized['marriages']);
        }

        $errors   = [];
        $warnings = [];

        if (empty($parishioners)) {
            $errors[] = 'Không tìm thấy dữ liệu thành viên (sheet <strong>thanh_vien</strong> hoặc <strong>parishioners</strong>).';

            return compact('errors', 'warnings', 'parishioners', 'sacraments', 'marriages') + ['families' => $familiesMeta, 'ready' => false];
        }

        $tempIds        = [];
        $familyRoles    = [];
        $parsedRows     = [];
        $saintNames     = Holymanagement::pluck('name')->map(fn($n) => mb_strtolower(trim($n), 'UTF-8'))->toArray();
        $parishNames    = ParishNew::pluck('name', 'id')->mapWithKeys(fn($name, $id) => [mb_strtolower(trim($name), 'UTF-8') => $id])->toArray();

        foreach ($parishioners as $index => $row) {
            $rowNumber = $index + 6;
            $tempId    = trim($row['temp_id'] ?? '');

            if ($tempId === '' && trim($row['last_name'] ?? '') === '' && trim($row['first_name'] ?? '') === '') {
                continue;
            }

            if ($tempId === '') {
                $errors[] = "parishioners dòng {$rowNumber}: thiếu <strong>temp_id</strong>.";
                continue;
            }

            if (isset($tempIds[$tempId])) {
                $errors[] = "parishioners dòng {$rowNumber}: <strong>temp_id \"{$tempId}\"</strong> bị trùng (đã có ở dòng {$tempIds[$tempId]}).";
            } else {
                $tempIds[$tempId] = $rowNumber;
            }

            if (trim($row['last_name'] ?? '') === '') {
                $errors[] = "parishioners dòng {$rowNumber} ({$tempId}): thiếu <strong>last_name</strong>.";
            }
            if (trim($row['first_name'] ?? '') === '') {
                $errors[] = "parishioners dòng {$rowNumber} ({$tempId}): thiếu <strong>first_name</strong>.";
            }

            $familyRole = ParishionerEnumResolver::parseFamilyRole($row['family_role'] ?? '');
            if ($familyRole === null) {
                $raw = trim($row['family_role'] ?? '');
                $errors[] = $raw === ''
                    ? "thanh_vien dòng {$rowNumber} ({$tempId}): thiếu <strong>vai_tro</strong>."
                    : "thanh_vien dòng {$rowNumber} ({$tempId}): vai_tro <strong>\"{$raw}\"</strong> không hợp lệ (Chồng / Vợ / Con / Khác).";
            }

            $genderRaw = trim($row['gender'] ?? '');
            if ($genderRaw !== '' && ! in_array(mb_strtolower($genderRaw, 'UTF-8'), ['nam', 'nữ', 'nu', 'male', 'female', 'm', 'f'], true)) {
                $errors[] = "thanh_vien dòng {$rowNumber} ({$tempId}): gioi_tinh <strong>\"{$genderRaw}\"</strong> không hợp lệ (Nam / Nữ).";
            }

            if (!empty($row['birthday']) && ExcelDateParser::parse($row['birthday']) === null) {
                $errors[] = "parishioners dòng {$rowNumber} ({$tempId}): birthday <strong>\"{$row['birthday']}\"</strong> không hợp lệ (dd/mm/yyyy).";
            }

            $familyTempId = trim($row['family_temp_id'] ?? '');
            if ($familyTempId !== '' && $familyRole !== null) {
                $familyRoles[$familyTempId][$familyRole][] = $tempId;
            }

            foreach (['father_temp_id', 'mother_temp_id'] as $fk) {
                $ref = trim($row[$fk] ?? '');
                if ($ref !== '' && !isset($tempIds[$ref])) {
                    // parent may appear later — defer check
                }
            }

            $saintName = trim($row['saint_name'] ?? '');
            if ($saintName !== '' && !in_array(mb_strtolower($saintName, 'UTF-8'), $saintNames, true)) {
                $warnings[] = "parishioners dòng {$rowNumber} ({$tempId}): tên thánh <strong>\"{$saintName}\"</strong> chưa có — sẽ tự tạo khi import.";
            }

            $parishName = trim($row['parish_name'] ?? '');
            if ($parishName !== '' && !isset($parishNames[mb_strtolower($parishName, 'UTF-8')])) {
                $warnings[] = "parishioners dòng {$rowNumber} ({$tempId}): giáo xứ <strong>\"{$parishName}\"</strong> không khớp — dùng giáo xứ đang import.";
            }

            $associationName = trim($row['association_name'] ?? '');
            if ($associationName !== '') {
                $associationQuery = Association::query()
                    ->where('status', 1)
                    ->whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($associationName, 'UTF-8')]);

                if ($parishId) {
                    $associationQuery->where('pid', $parishId);
                }

                if (! $associationQuery->exists()) {
                    $warnings[] = "thanh_vien dòng {$rowNumber} ({$tempId}): hội đoàn <strong>\"{$associationName}\"</strong> chưa có trong hệ thống — sẽ bỏ qua khi import.";
                }
            }

            $parsedRows[] = $this->normalizeParishionerRow($row, $rowNumber);
        }

        // Second pass: FK refs for parents
        $allTempIds = array_keys($tempIds);
        foreach ($parishioners as $index => $row) {
            $rowNumber = $index + 6;
            $tempId    = trim($row['temp_id'] ?? '');
            if ($tempId === '') {
                continue;
            }
            foreach (['father_temp_id', 'mother_temp_id'] as $fk) {
                $ref = trim($row[$fk] ?? '');
                if ($ref !== '' && !in_array($ref, $allTempIds, true)) {
                    $errors[] = "parishioners dòng {$rowNumber} ({$tempId}): <strong>{$fk} \"{$ref}\"</strong> không tồn tại trong sheet parishioners.";
                }
            }
        }

        foreach ($familyRoles as $familyTempId => $roles) {
            if (count($roles['husband'] ?? []) > 1) {
                $errors[] = "ma_ho_gia_dinh <strong>{$familyTempId}</strong>: có nhiều hơn 1 <strong>Chồng</strong>.";
            }
            if (count($roles['wife'] ?? []) > 1) {
                $errors[] = "ma_ho_gia_dinh <strong>{$familyTempId}</strong>: có nhiều hơn 1 <strong>Vợ</strong>.";
            }
        }

        $parsedSacraments = [];
        foreach ($sacraments as $index => $row) {
            $rowNumber = $index + 6;
            $ref       = trim($row['parishioner_temp_id'] ?? '');

            if ($ref === '' && trim($row['type'] ?? '') === '') {
                continue;
            }

            if ($ref === '') {
                $errors[] = "sacraments dòng {$rowNumber}: thiếu <strong>parishioner_temp_id</strong>.";
                continue;
            }

            if (!isset($tempIds[$ref])) {
                $errors[] = "sacraments dòng {$rowNumber}: <strong>parishioner_temp_id \"{$ref}\"</strong> không tồn tại.";
            }

            $type = ParishionerEnumResolver::parseSacramentType($row['type'] ?? '');
            if ($type === null) {
                $raw = trim($row['type'] ?? '');
                $errors[] = $raw === ''
                    ? "bí tích dòng {$rowNumber}: thiếu <strong>loại bí tích</strong>."
                    : "bí tích dòng {$rowNumber}: loại <strong>\"{$raw}\"</strong> không hợp lệ.";
            }

            if (!empty($row['received_date']) && ExcelDateParser::parse($row['received_date']) === null) {
                $errors[] = "sacraments dòng {$rowNumber}: received_date <strong>\"{$row['received_date']}\"</strong> không hợp lệ.";
            }

            $parsedSacraments[] = $this->normalizeSacramentRow($row, $rowNumber);
        }

        $parsedMarriages = [];
        foreach ($marriages as $index => $row) {
            $rowNumber = $index + 6;
            $husband   = trim($row['husband_temp_id'] ?? '');
            $wife      = trim($row['wife_temp_id'] ?? '');

            if ($husband === '' && $wife === '') {
                continue;
            }

            if ($husband === '') {
                $errors[] = "marriages dòng {$rowNumber}: thiếu <strong>husband_temp_id</strong>.";
            } elseif (!isset($tempIds[$husband])) {
                $errors[] = "marriages dòng {$rowNumber}: <strong>husband_temp_id \"{$husband}\"</strong> không tồn tại.";
            }

            if ($wife === '') {
                $errors[] = "marriages dòng {$rowNumber}: thiếu <strong>wife_temp_id</strong>.";
            } elseif (!isset($tempIds[$wife])) {
                $errors[] = "marriages dòng {$rowNumber}: <strong>wife_temp_id \"{$wife}\"</strong> không tồn tại.";
            }

            $statusRaw = trim($row['status'] ?? 'Hợp lệ');
            $status = ParishionerEnumResolver::parseMarriageRecordStatus($statusRaw);
            if ($statusRaw !== '' && ! in_array(mb_strtolower($statusRaw, 'UTF-8'), [
                'hợp lệ', 'hop le', 'bất hợp lệ', 'bat hop le', 'góa', 'goa', 'ly hôn', 'ly hon',
                'valid', 'invalid', 'widowed', 'divorced',
            ], true)) {
                $errors[] = "hôn phối dòng {$rowNumber}: trạng thái <strong>\"{$statusRaw}\"</strong> không hợp lệ.";
            }

            if (!empty($row['married_date']) && ExcelDateParser::parse($row['married_date']) === null) {
                $errors[] = "marriages dòng {$rowNumber}: married_date <strong>\"{$row['married_date']}\"</strong> không hợp lệ.";
            }

            $parsedMarriages[] = $this->normalizeMarriageRow($row, $rowNumber);
        }

        $ready = empty($errors) && !empty($parsedRows);

        return [
            'errors'       => $errors,
            'warnings'     => $warnings,
            'families'     => $familiesMeta,
            'parishioners' => $parsedRows,
            'sacraments'   => $parsedSacraments,
            'marriages'    => $parsedMarriages,
            'ready'        => $ready,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function rawRowsFromNormalized(array $rows): array
    {
        return array_values($rows);
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $allSheets
     * @return array{parishioners: array, sacraments: array, marriages: array}
     */
    private function classifySheets(array $allSheets): array
    {
        $parishioners = [];
        $sacraments   = [];
        $marriages    = [];

        foreach ($allSheets as $rows) {
            if (empty($rows)) {
                continue;
            }

            $first = $rows[0] ?? [];

            if (array_key_exists('temp_id', $first) && array_key_exists('family_temp_id', $first)) {
                $parishioners = $rows;
            } elseif (array_key_exists('parishioner_temp_id', $first) && array_key_exists('type', $first)) {
                $sacraments = $rows;
            } elseif (array_key_exists('husband_temp_id', $first) && array_key_exists('wife_temp_id', $first)) {
                $marriages = $rows;
            }
        }

        return compact('parishioners', 'sacraments', 'marriages');
    }

    private function normalizeParishionerRow(array $row, int $rowNumber): array
    {
        return [
            'row_number'      => $rowNumber,
            'temp_id'         => trim($row['temp_id'] ?? ''),
            'family_temp_id'  => trim($row['family_temp_id'] ?? ''),
            'family_role'     => ParishionerEnumResolver::parseFamilyRole($row['family_role'] ?? '') ?? '',
            'last_name'       => trim($row['last_name'] ?? ''),
            'first_name'      => trim($row['first_name'] ?? ''),
            'gender'          => ParishionerEnumResolver::parseGender($row['gender'] ?? ''),
            'birthday'        => $row['birthday'] ?? '',
            'birth_place'     => trim($row['birth_place'] ?? ''),
            'birth_order'     => trim($row['birth_order'] ?? ''),
            'saint_name'      => trim($row['saint_name'] ?? ''),
            'father_temp_id'  => trim($row['father_temp_id'] ?? ''),
            'mother_temp_id'  => trim($row['mother_temp_id'] ?? ''),
            'parish_name'     => trim($row['parish_name'] ?? ''),
            'association_name' => trim($row['association_name'] ?? ''),
            'note'            => trim($row['note'] ?? ''),
        ];
    }

    private function normalizeSacramentRow(array $row, int $rowNumber): array
    {
        return [
            'row_number'          => $rowNumber,
            'parishioner_temp_id' => trim($row['parishioner_temp_id'] ?? ''),
            'type'                => ParishionerEnumResolver::parseSacramentType($row['type'] ?? '') ?? '',
            'received_date'       => $row['received_date'] ?? '',
            'certificate_number'  => trim($row['certificate_number'] ?? ''),
            'book_number'         => trim($row['book_number'] ?? ''),
            'giver'               => trim($row['giver'] ?? ''),
            'sponsor'             => trim($row['sponsor'] ?? ''),
            'parish_name'         => trim($row['parish_name'] ?? ''),
            'note'                => trim($row['note'] ?? ''),
        ];
    }

    private function normalizeMarriageRow(array $row, int $rowNumber): array
    {
        return [
            'row_number'         => $rowNumber,
            'husband_temp_id'    => trim($row['husband_temp_id'] ?? ''),
            'wife_temp_id'       => trim($row['wife_temp_id'] ?? ''),
            'married_date'       => $row['married_date'] ?? '',
            'certificate_number' => trim($row['certificate_number'] ?? ''),
            'parish_name'        => trim($row['parish_name'] ?? ''),
            'witness_1'          => trim($row['witness_1'] ?? ''),
            'witness_2'          => trim($row['witness_2'] ?? ''),
            'priest_witness'     => trim($row['priest_witness'] ?? ''),
            'status'             => ParishionerEnumResolver::parseMarriageRecordStatus($row['status'] ?? 'Hợp lệ'),
            'note'               => trim($row['note'] ?? ''),
        ];
    }
}
