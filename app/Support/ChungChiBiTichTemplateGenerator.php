<?php

namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Mẫu Chứng chỉ bí tích Rửa tội – Thêm sức (theo PDF mau_chung_chi_rua_toi-them_suc.pdf).
 * File: public/word-template/05_chungchibitich.docx
 */
class ChungChiBiTichTemplateGenerator
{
    public static function path(): string
    {
        return public_path('word-template/05_chungchibitich.docx');
    }

    public static function ensureExists(bool $force = false): string
    {
        $path = self::path();
        $dir  = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if ($force || ! is_file($path) || ! self::hasPlaceholders($path)) {
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

            return str_contains($xml, '${holy_name}') && str_contains($xml, '${baptism_date}');
        } catch (\Throwable $e) {
            return false;
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
            'marginTop'    => 900,
            'marginBottom' => 900,
            'marginLeft'   => 1100,
            'marginRight'  => 1100,
        ]);

        $center = ['alignment' => Jc::CENTER];
        $end    = ['alignment' => Jc::END];
        $bold   = ['bold' => true, 'size' => 14];
        $header = ['bold' => true, 'size' => 12];
        $normal = ['size' => 13];
        $small  = ['size' => 12];

        $section->addText('${archdiocese}', $header, $center);
        $section->addText('${deanery}', $small, $center);
        $section->addText('${parish}', $small, $center);
        $section->addTextBreak(1);
        $section->addText('CHỨNG CHỈ BÍ TÍCH', $bold, $center);
        $section->addTextBreak(1);

        $section->addText(
            'Kính gửi Cha: ${recipient_priest}, Giáo phận ${recipient_diocese}',
            $normal
        );
        $section->addTextBreak(0);
        $section->addText(
            'Tôi, linh mục: ${priest_name} phụ trách xứ: ${priest_parish}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            'Chứng nhận: (tên thánh, họ và tên): ${holy_name}',
            $normal
        );
        $section->addText('Sinh ngày: ${birthday}', $normal);
        $section->addText('Quê quán: ${origin}', $normal);
        $section->addText('Con ông: ${father_name}', $normal);
        $section->addText('Và bà: ${mother_name}', $normal);
        $section->addTextBreak(1);

        $section->addText(
            'Đã chịu phép Rửa Tội ngày: ${baptism_date} tại: ${baptism_place}',
            $normal
        );
        $section->addText('Do linh mục: ${baptism_giver}', $normal);
        $section->addText(
            'Người đỡ đầu: ${baptism_sponsor}, sổ Rửa Tội số: ${baptism_number}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            'Đã chịu phép Thêm Sức ngày: ${confirmation_date} tại: ${confirmation_place}',
            $normal
        );
        $section->addText('Do Đức Giám Mục: ${confirmation_giver}', $normal);
        $section->addText(
            'Người đỡ đầu: ${confirmation_sponsor}, sổ Thêm Sức số: ${confirmation_number}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText('Nay cấp chứng chỉ Bí Tích để: ${purpose}', $normal);
        $section->addTextBreak(1);

        $section->addText(
            '${sign_place}, ngày ${day} tháng ${month} năm ${year}',
            $normal,
            $end
        );
        $section->addTextBreak(1);
        $section->addText('Linh mục quản xứ', $normal, $end);
        $section->addText('(Ký tên, đóng dấu)', $small, $end);
        $section->addTextBreak(2);
        $section->addText('${signer_name}', $small, $end);

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
