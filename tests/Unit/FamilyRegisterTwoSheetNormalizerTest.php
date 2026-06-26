<?php

namespace Tests\Unit;

use App\Support\FamilyRegisterTwoSheetNormalizer;
use Tests\TestCase;

class FamilyRegisterTwoSheetNormalizerTest extends TestCase
{
    public function test_normalizes_household_meta_association_and_widowed_marriage(): void
    {
        $allSheets = [
            [
                [
                    'ma_ho_gia_dinh'      => 'H001',
                    'ten_ho_gia_dinh'     => 'GĐ Test',
                    'ghi_chu'             => 'Ghi chú hộ',
                    'dien_thoai'          => '0901234567',
                    'xa_phuong'           => 'Phường 1',
                    'hon_phoi_ngay'       => '01/01/1990',
                    'hon_phoi_trang_thai' => 'Góa',
                ],
            ],
            [
                [
                    'ma_thanh_vien'  => 'P001',
                    'ma_ho_gia_dinh' => 'H001',
                    'vai_tro'        => 'Chồng',
                    'ho'             => 'Nguyễn',
                    'ten'            => 'An',
                    'gioi_tinh'      => 'Nam',
                    'hoi_doan'       => 'Hội đoàn Thánh Giuse',
                    'gio_xu'         => 'GX Test',
                ],
                [
                    'ma_thanh_vien'  => 'P002',
                    'ma_ho_gia_dinh' => 'H001',
                    'vai_tro'        => 'Vợ',
                    'ho'             => 'Trần',
                    'ten'            => 'Bình',
                    'gioi_tinh'      => 'Nữ',
                ],
            ],
        ];

        $result = app(FamilyRegisterTwoSheetNormalizer::class)->normalize($allSheets);

        $this->assertEmpty($result['errors']);
        $this->assertSame('Ghi chú hộ', $result['families']['H001']['note']);
        $this->assertSame('0901234567', $result['families']['H001']['phone']);
        $this->assertSame('Phường 1', $result['families']['H001']['ward']);
        $this->assertSame('Hội đoàn Thánh Giuse', $result['parishioners'][0]['association_name']);
        $this->assertSame('GX Test', $result['parishioners'][0]['parish_name']);
        $this->assertCount(1, $result['marriages']);
        $this->assertSame('widowed', $result['marriages'][0]['status']);
        $this->assertSame('P001', $result['marriages'][0]['husband_temp_id']);
        $this->assertSame('P002', $result['marriages'][0]['wife_temp_id']);
    }

    public function test_normalizes_sacrament_notes_from_member_row(): void
    {
        $allSheets = [
            [
                [
                    'ma_ho_gia_dinh'  => 'H001',
                    'ten_ho_gia_dinh' => 'GĐ Test',
                ],
            ],
            [
                [
                    'ma_thanh_vien'   => 'P001',
                    'ma_ho_gia_dinh'  => 'H001',
                    'vai_tro'         => 'Con',
                    'ho'              => 'Lê',
                    'ten'             => 'Chi',
                    'gioi_tinh'       => 'Nữ',
                    'rua_toi_ngay'    => '01/01/2010',
                    'rua_toi_ghi_chu' => 'RT note',
                    'them_suc_ngay'   => '01/05/2018',
                    'them_suc_noi'    => 'GX ABC',
                    'them_suc_ghi_chu'=> 'TS note',
                ],
            ],
        ];

        $result = app(FamilyRegisterTwoSheetNormalizer::class)->normalize($allSheets);

        $this->assertEmpty($result['errors']);
        $baptism = collect($result['sacraments'])->firstWhere('type', 'baptism');
        $confirmation = collect($result['sacraments'])->firstWhere('type', 'confirmation');

        $this->assertNotNull($baptism);
        $this->assertSame('RT note', $baptism['note']);
        $this->assertNotNull($confirmation);
        $this->assertSame('GX ABC', $confirmation['parish_name']);
        $this->assertSame('TS note', $confirmation['note']);
    }
}
