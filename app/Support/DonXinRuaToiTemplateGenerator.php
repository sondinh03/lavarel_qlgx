<?php

namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Sinh / đảm bảo mẫu Đơn xin rửa tội có placeholder PhpWord.
 * Backup gốc (nếu có): public/word-template/01_donxinruatoi.original.docx
 */
class DonXinRuaToiTemplateGenerator
{
    public const PLACEHOLDERS = [
        'diocese', 'parish', 'parish_group',
        'birth_order', 'holy_fullname',
        'birth_day', 'birth_month', 'birth_year', 'birth_place',
        'father_name', 'mother_name', 'address',
        'current_parish_group', 'current_parish',
        'godparent_name',
        'sign_place', 'day', 'month', 'year',
    ];

    public static function path(): string
    {
        return public_path('word-template/01_donxinruatoi.docx');
    }

    public static function ensureExists(bool $force = false): string
    {
        $path = self::path();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($force || !is_file($path) || !self::hasPlaceholders($path)) {
            self::backupOriginal($path);
            self::generate($path);
        }

        return $path;
    }

    public static function hasPlaceholders(string $path): bool
    {
        if (!is_file($path)) {
            return false;
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($path) !== true) {
                return false;
            }
            $xml = $zip->getFromName('word/document.xml') ?: '';
            $zip->close();

            return str_contains($xml, '${holy_fullname}') || str_contains($xml, '${parish}');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function backupOriginal(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $backup = public_path('word-template/01_donxinruatoi.original.docx');
        if (!is_file($backup)) {
            copy($path, $backup);
        }
    }

    public static function generate(?string $path = null): string
    {
        $path = $path ?? self::path();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
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

        self::addFormCopy($section);
        $section->addTextBreak(1);
        $section->addText(
            str_repeat('—', 48),
            ['size' => 10, 'color' => '999999'],
            ['alignment' => Jc::CENTER]
        );
        $section->addTextBreak(1);
        self::addFormCopy($section);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }

    protected static function addFormCopy($section): void
    {
        $centerBold = ['alignment' => Jc::CENTER];
        $bold       = ['bold' => true, 'size' => 14];
        $normal     = ['size' => 13];
        $small      = ['size' => 12];

        $section->addText('ĐƠN XIN RỬA TỘI', $bold, $centerBold);
        $section->addTextBreak(1);

        $section->addText(
            '${diocese} — ${parish} — ${parish_group}',
            $small,
            $centerBold
        );
        $section->addTextBreak(1);

        $section->addText('Kính gửi Cha Chánh xứ: ${parish}', $normal);
        $section->addTextBreak(1);

        $section->addText(
            'Xin Cha rửa tội (bà các phép) cho con thứ ${birth_order} của chúng con là:',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText('Tên thánh, họ gọi: ${holy_fullname}', $normal);
        $section->addText(
            'Sinh ngày ${birth_day} tháng ${birth_month} năm ${birth_year}, tại ${birth_place}',
            $normal
        );
        $section->addText('Con Ông ${father_name} Và Bà ${mother_name}', $normal);
        $section->addText('Địa chỉ: ${address}', $normal);
        $section->addText(
            'Hiện thuộc ${current_parish_group}, ${current_parish}',
            $normal
        );
        $section->addText('Tên thánh, họ tên người đỡ đầu: ${godparent_name}', $normal);
        $section->addTextBreak(1);

        $section->addText(
            'Gia đình chúng con chân thành cảm ơn Cha.',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            '${sign_place}, ngày ${day} tháng ${month} năm ${year}',
            $normal,
            ['alignment' => Jc::END]
        );
        $section->addTextBreak(1);

        $table = $section->addTable(['borderSize' => 0, 'cellMargin' => 40]);
        $table->addRow();
        $table->addCell(4500)->addText('Kính đơn,', $normal, $centerBold);
        $table->addCell(4500)->addText('Xác nhận BĐH Giáo họ', $normal, $centerBold);
        $table->addRow();
        $table->addCell(4500)->addTextBreak(3);
        $table->addCell(4500)->addTextBreak(3);
    }
}
