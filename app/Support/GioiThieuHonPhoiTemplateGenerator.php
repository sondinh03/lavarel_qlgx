<?php

namespace App\Support;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Sinh / đảm bảo mẫu Giấy giới thiệu hôn phối có placeholder PhpWord.
 * Backup gốc: public/word-template/04_gioithieuhonphoi.original.docx
 */
class GioiThieuHonPhoiTemplateGenerator
{
    public static function path(): string
    {
        return public_path('word-template/04_gioithieuhonphoi.docx');
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

            return str_contains($xml, '${a_holy_name}') || str_contains($xml, '${b_holy_name}');
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected static function backupOriginal(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        $backup = public_path('word-template/04_gioithieuhonphoi.original.docx');
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

        $section->addText('${diocese}', $header, $center);
        $section->addText('${parish}', $header, $center);
        $section->addText('${parish_group}', $header, $center);
        $section->addTextBreak(1);

        $section->addText('GIỚI THIỆU HÔN PHỐI', $bold, $center);
        $section->addTextBreak(1);

        $section->addText('Kính gửi Cha Chánh xứ ${greeting_parish}', $normal);
        $section->addTextBreak(0);
        $section->addText('Có ${a_honorific} ${a_holy_name}', $normal);
        $section->addText(
            'Sinh ngày ${a_birth_day} tháng ${a_birth_month} năm ${a_birth_year}, tại ${a_birth_place}',
            $normal
        );
        $section->addText('Con Ông ${a_father_name}', $normal);
        $section->addText('Và Bà ${a_mother_name}', $normal);
        $section->addText('Địa chỉ: ${a_address}', $normal);
        $section->addText(
            'Hiện ở tại ${a_parish_group}, thuộc ${a_parish}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText('Nay ${a_honorific} xin kết bạn với: ${b_honorific} ${b_holy_name}', $normal);
        $section->addText(
            'Sinh ngày ${b_birth_day} tháng ${b_birth_month} năm ${b_birth_year}, tại ${b_birth_place}',
            $normal
        );
        $section->addText('Con Ông ${b_father_name}', $normal);
        $section->addText('Và Bà ${b_mother_name}', $normal);
        $section->addText('Địa chỉ: ${b_address}', $normal);
        $section->addText(
            'Hiện ở tại ${b_parish_group}, thuộc ${b_parish}',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            'Thay mặt BĐH họ đạo chúng con xin giới thiệu lên Cha xứ.',
            $normal
        );
        $section->addTextBreak(1);

        $section->addText(
            '${sign_place}, ngày ${day} tháng ${month} năm ${year}',
            $normal,
            $end
        );
        $section->addTextBreak(1);
        $section->addText('TM/BĐH GIÁO HỌ', $normal, $end);
        $section->addText('(Ký và ghi rõ họ tên)', $small, $end);
        $section->addTextBreak(2);

        $section->addText('Ghi chú:', ['bold' => true, 'size' => 12]);
        $section->addText(
            '1. Khi đi làm khẩu cung cần mặt có: đương sự (người xin rao cưới), bố mẹ, người làm chứng của đương sự.',
            $small
        );
        $section->addText(
            '2. Mang theo: sổ gia đình công giáo, phiếu sinh hoạt giới trẻ, giấy chứng nhận học giáo lý hôn nhân, giấy giới thiệu và chữ ký của Giáo họ mình đang ở.',
            $small
        );
        $section->addText(
            '3. Nếu là nữ lấy chồng ở xứ khác, sau khi có thư giới thiệu ở bên nhà trai thì được liên hệ xin làm khẩu cung hôn phối.',
            $small
        );
        $section->addText(
            '4. Chỉ in thiệp cưới khi đã đăng ký ngày lễ cưới nơi cha xứ.',
            $small
        );

        IOFactory::createWriter($phpWord, 'Word2007')->save($path);

        return $path;
    }
}
