<?php

namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Sinh / đảm bảo mẫu Giấy báo tử có placeholder PhpWord.
 * Backup gốc: public/word-template/02_phieubaotu.original.docx
 */
class PhieuBaoTuTemplateGenerator
{
    public const PLACEHOLDERS = [
        'diocese', 'parish', 'parish_group',
        'honorific', 'holy_fullname', 'common_name',
        'birth_day', 'birth_month', 'birth_year', 'birth_place',
        'death_hour', 'death_day', 'death_month', 'death_year', 'death_place',
        'age',
        'embalm_hour', 'embalm_day', 'embalm_month', 'embalm_year',
        'farewell_hour', 'farewell_day', 'farewell_month', 'farewell_year',
        'burial_mass_hour', 'burial_mass_day', 'burial_mass_month', 'burial_mass_year',
        'burial_place',
        'sign_place', 'day', 'month', 'year',
    ];

    public static function path(): string
    {
        return public_path('word-template/02_phieubaotu.docx');
    }

    public static function ensureExists(bool $force = false): string
    {
        $path = self::path();
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($force || ! is_file($path) || ! self::hasPlaceholders($path)) {
            self::backupOriginal($path);
            self::generate($path);
        }

        return $path;
    }

    public static function hasPlaceholders(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                return false;
            }
            $xml = $zip->getFromName('word/document.xml') ?: '';
            $zip->close();

            return str_contains($xml, '${holy_fullname}') || str_contains($xml, '${death_day}');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function backupOriginal(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $backup = public_path('word-template/02_phieubaotu.original.docx');
        if (! is_file($backup)) {
            copy($path, $backup);
        }
    }

    public static function generate(?string $path = null): string
    {
        $path = $path ?? self::path();
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Times New Roman');
        $phpWord->setDefaultFontSize(13);

        $section = $phpWord->addSection([
            'marginTop'    => 800,
            'marginBottom' => 800,
            'marginLeft'   => 1000,
            'marginRight'  => 1000,
        ]);

        $center = ['alignment' => Jc::CENTER];
        $end    = ['alignment' => Jc::END];
        $bold   = ['bold' => true, 'size' => 14];
        $normal = ['size' => 13];
        $small  = ['size' => 12];

        $section->addText('${diocese}', $small, $center);
        $section->addText('${parish}', $small, $center);
        $section->addText('${parish_group}', $small, $center);
        $section->addTextBreak(1);
        $section->addText('GIẤY BÁO TỬ', $bold, $center);
        $section->addTextBreak(1);

        $section->addText('Kính trình Cha Chánh xứ ${parish}', $normal);
        $section->addTextBreak(0);
        $section->addText(
            'Ban Điều hành ${parish_group}, cùng gia đình tang quyến chúng con xin kính báo:',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText('Ông (Bà, Anh, Chị, Em): ${honorific}', $normal);
        $section->addText('Tên thánh, họ tên: ${holy_fullname}', $normal);
        $section->addText('Tên thường gọi là: ${common_name}', $normal);
        $section->addText(
            'Sinh ngày ${birth_day} tháng ${birth_month} năm ${birth_year}, tại ${birth_place}',
            $normal
        );
        $section->addText(
            'Từ trần lúc: ${death_hour} giờ ngày ${death_day} tháng ${death_month} năm ${death_year}, tại ${death_place}',
            $normal
        );
        $section->addText('Hưởng thọ (dương): ${age} tuổi', $normal);
        $section->addTextBreak(1);

        $section->addText('Tang quyến xin cha hướng dẫn những việc hậu sự như sau:', $normal);
        $section->addText(
            'Nghi thức tẩm liệm: ${embalm_hour} giờ ${embalm_day} tháng ${embalm_month} năm ${embalm_year}',
            $normal
        );
        $section->addText(
            'Thánh lễ đưa chân: ${farewell_hour} giờ ${farewell_day} tháng ${farewell_month} năm ${farewell_year}',
            $normal
        );
        $section->addText(
            'Thánh lễ an táng: ${burial_mass_hour} giờ ${burial_mass_day} tháng ${burial_mass_month} năm ${burial_mass_year}',
            $normal
        );
        $section->addText('An táng tại nghĩa trang: ${burial_place}', $normal);
        $section->addTextBreak(1);

        $section->addText('NHỮNG ĐÓNG GÓP HY SINH CỦA NGƯỜI QUÁ CỐ', ['bold' => true, 'size' => 13], $center);
        $section->addText('1. ………………………………………………………………', $normal);
        $section->addText('2. ………………………………………………………………', $normal);
        $section->addText('3. ………………………………………………………………', $normal);
        $section->addText('4. ………………………………………………………………', $normal);
        $section->addText('5. ………………………………………………………………', $normal);
        $section->addTextBreak(1);

        $section->addText(
            'Kính báo ${sign_place}, ngày ${day} tháng ${month} năm ${year}',
            $normal,
            $end
        );
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 40]);
        $table->addRow();
        $table->addCell(4500)->addText('TM/BĐH GIÁO HỌ', $normal, $center);
        $table->addCell(4500)->addText('TM Tang gia', $normal, $center);
        $table->addRow();
        $table->addCell(4500)->addText('Trưởng Ban', $small, $center);
        $table->addCell(4500)->addTextBreak(1);
        $table->addRow();
        $table->addCell(4500)->addTextBreak(3);
        $table->addCell(4500)->addTextBreak(3);

        $section->addTextBreak(1);
        $section->addText('Thành kính phân ưu', $normal, $center);
        $section->addText('Linh mục Chánh xứ', $normal, $center);
        $section->addTextBreak(2);

        $section->addText('Kính báo đến:', $small);
        $section->addText('- TV/ BHG', $small);
        $section->addText('- Các BĐH Giáo Họ', $small);
        $section->addText('- Các BTS Giới', $small);
        $section->addText('- Ca đoàn và Hội đoàn liên hệ', $small);

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
