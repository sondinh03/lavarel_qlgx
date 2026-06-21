<?php

namespace App\Actions\Parishioner;

use App\Models\Holymanagement;
use App\Models\Marriage;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\Sacrament;
use App\Support\ExcelDateParser;
use App\Support\ParishionerEnumResolver;
use App\Support\VietnamAddressResolver;
use Illuminate\Support\Facades\DB;

class ImportParishionerAction
{
    /**
     * @param  array  $rows  Mảng rows từ ParishionerImportPreview::$rows
     * @return array{imported: int, skipped: int, skipped_duplicate: int, sacraments_created: int, marriages_created: int, errors: array}
     */
    public function handle(array $rows, int $parishId): array
    {
        $saintMap = Holymanagement::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $parishGroupMap = ParishGroup::active()
            ->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [trim($name) => $id])
            ->toArray();

        $imported           = 0;
        $skipped            = 0;
        $skipped_duplicate  = 0;
        $sacramentsCreated  = 0;
        $marriagesCreated   = 0;
        $errors             = [];

        foreach ($rows as $row) {
            $rowNumber = $row['row_number'];

            if (empty(trim($row['ho_ten_dem'] ?? '')) && empty(trim($row['ten'] ?? ''))) {
                $skipped++;
                continue;
            }

            if (!empty($row['is_duplicate'])) {
                $skipped_duplicate++;
                continue;
            }

            try {
                DB::transaction(function () use (
                    $row,
                    $parishId,
                    $saintMap,
                    $parishGroupMap,
                    &$imported,
                    &$sacramentsCreated,
                    &$marriagesCreated
                ) {
                    $saintId = null;
                    if (!empty(trim($row['ten_thanh'] ?? ''))) {
                        $saintId = $saintMap[trim($row['ten_thanh'])] ?? null;
                    }

                    $parishGroupId = null;
                    if (!empty(trim($row['giao_ho'] ?? ''))) {
                        $parishGroupId = $parishGroupMap[trim($row['giao_ho'])] ?? null;
                    }

                    $birthday = !empty($row['ngay_sinh'])
                        ? ExcelDateParser::parse($row['ngay_sinh'])
                        : null;

                    $parishioner = Parishioner::create([
                        'last_name'            => trim($row['ho_ten_dem'] ?? ''),
                        'first_name'           => trim($row['ten'] ?? ''),
                        'gender'               => ParishionerEnumResolver::parseGender($row['gioi_tinh'] ?? null),
                        'birthday'             => $birthday,
                        'birth_order'          => $this->parseInt($row['con_thu'] ?? null),
                        'saint_id'             => $saintId,
                        'parish_id'            => $parishId,
                        'parish_area_id'       => $parishGroupId,
                        'phone'                => $row['so_dien_thoai'] ?? null,
                        'email'                => trim($row['email'] ?? '') ?: null,
                        'cccd'                 => $row['cccd'] ?? null,
                        'father_name'          => trim($row['ho_ten_bo'] ?? '') ?: null,
                        'mother_name'          => trim($row['ho_ten_me'] ?? '') ?: null,
                        'married'              => ParishionerEnumResolver::parseMarriedStatus($row['tinh_trang_hon_nhan'] ?? null),
                        'is_new_convert'       => ParishionerEnumResolver::parseBoolean($row['tan_tong'] ?? null),
                        'note'                 => trim($row['ghi_chu'] ?? '') ?: null,
                        'origin'               => trim($row['que_quan'] ?? '') ?: null,
                        'permanent_residence'  => trim($row['dia_chi_thuong_tru'] ?? '') ?: null,
                        'permanent_province'   => VietnamAddressResolver::resolveProvinceKey($row['tinh_thuong_tru'] ?? null),
                        'permanent_ward_id'    => VietnamAddressResolver::resolveWardId($row['xa_thuong_tru'] ?? null),
                        'temporary_residence'  => trim($row['dia_chi_tam_tru'] ?? '') ?: null,
                        'temporary_province'   => VietnamAddressResolver::resolveProvinceKey($row['tinh_tam_tru'] ?? null),
                        'ethnic'               => ParishionerEnumResolver::resolve('ethnic', $row['dan_toc'] ?? null),
                        'career'               => ParishionerEnumResolver::resolve('career', $row['nghe_nghiep'] ?? null),
                        'education_level'      => ParishionerEnumResolver::resolve('education_level', $row['trinh_do_hoc_van'] ?? null),
                        'specialist_level'     => ParishionerEnumResolver::resolve('specialist_level', $row['trinh_do_chuyen_mon'] ?? null),
                        'catechism_level'      => ParishionerEnumResolver::resolve('catechism_level', $row['trinh_do_giao_ly'] ?? null),
                        'position'             => ParishionerEnumResolver::resolve('position', $row['chuc_vu'] ?? null),
                        'level'                => ParishionerEnumResolver::resolve('level', $row['cap_bac'] ?? null),
                        'joined_date'          => !empty($row['ngay_gia_nhap']) ? ExcelDateParser::parse($row['ngay_gia_nhap']) : null,
                        'death_date'           => !empty($row['ngay_mat']) ? ExcelDateParser::parse($row['ngay_mat']) : null,
                        'death_book_number'    => trim($row['so_so_mat'] ?? '') ?: null,
                        'burial_place'         => trim($row['noi_an_tang'] ?? '') ?: null,
                        'status'               => true,
                        'is_active'            => true,
                        'is_included_in_stats' => true,
                    ]);

                    $sacramentsCreated += $this->createSacraments($parishioner, $row);
                    if ($this->createMarriage($parishioner, $row, $parishId)) {
                        $marriagesCreated++;
                    }

                    $imported++;
                });
            } catch (\Exception $e) {
                $errors[] = "Dòng {$rowNumber}: " . $e->getMessage();
            }
        }

        return compact(
            'imported',
            'skipped',
            'skipped_duplicate',
            'sacramentsCreated',
            'marriagesCreated',
            'errors'
        );
    }

    private function createSacraments(Parishioner $parishioner, array $row): int
    {
        $created = 0;

        $created += $this->createSacrament($parishioner, Sacrament::TYPE_BAPTISM, $row, [
            'date'        => 'rua_toi_ngay',
            'number'      => 'rua_toi_so',
            'giver'       => 'rua_toi_nguoi_ban',
            'sponsor'     => 'rua_toi_dau_dau',
            'parish_name' => 'rua_toi_giao_xu',
        ]);

        $created += $this->createSacrament($parishioner, Sacrament::TYPE_CONFIRMATION, $row, [
            'date'        => 'them_suc_ngay',
            'number'      => 'them_suc_so',
            'giver'       => 'them_suc_nguoi_ban',
            'sponsor'     => 'them_suc_dau_dau',
            'parish_name' => 'them_suc_giao_xu',
        ]);

        $created += $this->createSacrament($parishioner, Sacrament::TYPE_COMMUNION, $row, [
            'date'        => 'ruoc_le_ngay',
            'number'      => 'ruoc_le_so',
            'giver'       => 'ruoc_le_nguoi_ban',
            'parish_name' => 'ruoc_le_giao_xu',
        ]);

        $created += $this->createSacrament($parishioner, Sacrament::TYPE_ANOINTING, $row, [
            'date'                => 'xuc_dau_ngay',
            'giver'               => 'xuc_dau_nguoi_ban',
            'anointing_condition' => 'xuc_dau_tinh_trang',
        ]);

        return $created;
    }

    private function createSacrament(Parishioner $parishioner, string $type, array $row, array $fields): int
    {
        if (empty($row[$fields['date']] ?? '')) {
            return 0;
        }

        Sacrament::create([
            'parishioner_id'      => $parishioner->id,
            'type'                => $type,
            'received_date'       => ExcelDateParser::parse($row[$fields['date']]),
            'certificate_number'  => isset($fields['number']) ? ($row[$fields['number']] ?? null) : null,
            'giver'               => isset($fields['giver']) ? ($row[$fields['giver']] ?? null) : null,
            'sponsor'             => isset($fields['sponsor']) ? ($row[$fields['sponsor']] ?? null) : null,
            'parish_name'         => isset($fields['parish_name']) ? ($row[$fields['parish_name']] ?? null) : null,
            'anointing_condition' => $type === Sacrament::TYPE_ANOINTING && isset($fields['anointing_condition'])
                ? ($row[$fields['anointing_condition']] ?? null)
                : null,
            'parish_id'           => $parishioner->parish_id,
        ]);

        return 1;
    }

    private function createMarriage(Parishioner $parishioner, array $row, int $parishId): bool
    {
        if (empty($row['hon_phoi_ngay'] ?? '')) {
            return false;
        }

        $data = [
            'married_date'       => ExcelDateParser::parse($row['hon_phoi_ngay']),
            'certificate_number' => $row['hon_phoi_so'] ?? null,
            'parish_name'        => $row['hon_phoi_noi'] ?? null,
            'place_province'     => VietnamAddressResolver::resolveProvinceKey($row['hon_phoi_tinh'] ?? null),
            'priest_witness'     => $row['hon_phoi_lm_chung'] ?? null,
            'witness_1'          => $row['hon_phoi_nhan_chung_1'] ?? null,
            'witness_2'          => $row['hon_phoi_nhan_chung_2'] ?? null,
            'status'             => ParishionerEnumResolver::parseMarriageRecordStatus($row['hon_phoi_tinh_trang'] ?? null),
            'parish_id'          => $parishId,
        ];

        if ($parishioner->gender === 'female') {
            $data['wife_id'] = $parishioner->id;
        } else {
            $data['husband_id'] = $parishioner->id;
        }

        Marriage::create($data);

        return true;
    }

    private function parseInt(mixed $value): ?int
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' && is_numeric($value) ? (int) $value : null;
    }
}
