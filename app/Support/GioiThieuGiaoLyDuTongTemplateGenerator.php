<?php

namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Sinh / đảm bảo mẫu Giấy giới thiệu học giáo lý dự tòng có placeholder PhpWord.
 * Backup gốc: public/word-template/03_gioithieugiaolydutong.original.docx
 */
class GioiThieuGiaoLyDuTongTemplateGenerator
{
    public const PLACEHOLDERS = [
        'diocese', 'deanery', 'parish',
        'greeting_to',
        'priest_name', 'priest_parish', 'priest_diocese',
        'honorific', 'holy_fullname',
        'birth_day', 'birth_month', 'birth_year',
        'father_name', 'mother_name',
        'address', 'belong_parish', 'belong_diocese',
        'course_place',
        'sign_place', 'day', 'month', 'year',
        'signer_name',
    ];

    public static function path(): string
    {
        return public_path('word-template/03_gioithieugiaolydutong.docx');
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

            return str_contains($xml, '${holy_fullname}') || str_contains($xml, '${course_place}');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function backupOriginal(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $backup = public_path('word-template/03_gioithieugiaolydutong.original.docx');
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
        $header = ['bold' => true, 'size' => 12];

        $section->addText('GIẤY GIỚI THIỆU', $bold, $center);
        $section->addText('HỌC GIÁO LÝ DỰ TÒNG', $bold, $center);
        $section->addTextBreak(1);

        $section->addText('${diocese}', $header, $center);
        $section->addText('${deanery}', $header, $center);
        $section->addText('${parish}', $header, $center);
        $section->addTextBreak(1);

        $section->addText('Kính gửi: ${greeting_to}', $normal);
        $section->addTextBreak(0);
        $section->addText(
            'Con, linh mục ${priest_name} chánh xứ ${priest_parish}, ${priest_diocese}. Con xin giới thiệu đến cha.',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText('${honorific} ${holy_fullname}', ['bold' => true, 'size' => 13]);
        $section->addText(
            'Sinh: ${birth_day}/${birth_month}/${birth_year}',
            $normal
        );
        $section->addText('Con ông: ${father_name}', $normal);
        $section->addText('Và bà: ${mother_name}', $normal);
        $section->addText(
            'Địa chỉ: ${address} Thuộc ${belong_parish} - ${belong_diocese}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            'Kính xin cha cho ${honorific} tham gia khóa học Giáo lý Dự Tòng tại ${course_place}. '
            . 'Sau khi đã được học Giáo lý đầy đủ xin Cha thương ban Bí Tích Khai Tâm cho ${honorific}.',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            'Con xin chân thành cảm ơn và xin Chúa ban muôn ơn lành cho Cha.',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            '${sign_place}, ngày ${day} tháng ${month} năm ${year}',
            $normal,
            $end
        );
        $section->addTextBreak(1);
        $section->addText('Linh mục chánh xứ', $normal, $end);
        $section->addTextBreak(2);
        $section->addText('${signer_name}', $small, $end);

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
