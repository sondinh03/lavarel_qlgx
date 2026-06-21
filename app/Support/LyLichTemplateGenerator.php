<?php

namespace App\Support;

use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class LyLichTemplateGenerator
{
    public const PLACEHOLDERS = [
        'did', 'deid', 'pid', 'giaoxu', 'paid', 'holy', 'id', 'name', 'birthday', 'sex',
        'email', 'origin', 'ward', 'province', 'residence', 'resi_ward', 'resi_province',
        'father', 'mother', 'phone', 'career', 'level', 'married',
        'baptism_date', 'baptism_number', 'baptism_giver', 'baptism_sponsor',
        'baptism_dioceses', 'baptism_deanerys', 'baptism_parish',
        'more_power_date', 'more_power_number', 'more_power_giver', 'more_power_sponsor',
        'more_power_dioceses', 'more_power_deanerys', 'more_power_parish',
        'communion_date', 'communion_number', 'communion_giver',
        'communion_dioceses', 'communion_deanerys', 'communion_parish',
        'anoint_date', 'anoint_status', 'anoint_giver',
        'die_time', 'die_burial', 'die_lottery',
        'date', 'sohonphoi', 'marriage_address', 'marriage_ward', 'marriage_province',
        'priest', 'peopleone', 'peopletwo', 'tinhtrang',
        'day', 'month', 'year',
    ];

    public static function path(): string
    {
        return base_path('public/word-template/LyLichCaNhan.docx');
    }

    public static function ensureExists(): string
    {
        $path = self::path();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($path)) {
            self::generate($path);
        }

        return $path;
    }

    public static function generate(?string $path = null): string
    {
        $path = $path ?? self::path();
        $dir  = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle('LÝ LỊCH CÁ NHÂN', 1);
        $section->addTextBreak(1);

        $groups = [
            'Thông tin giáo xứ' => ['did', 'deid', 'pid', 'giaoxu', 'paid'],
            'Thông tin cá nhân' => ['holy', 'name', 'birthday', 'sex', 'email', 'phone', 'id'],
            'Địa chỉ'           => ['origin', 'ward', 'province', 'residence', 'resi_ward', 'resi_province'],
            'Gia đình'          => ['father', 'mother', 'career', 'level', 'married'],
            'Rửa tội'           => ['baptism_date', 'baptism_number', 'baptism_giver', 'baptism_sponsor', 'baptism_dioceses', 'baptism_deanerys', 'baptism_parish'],
            'Thêm sức'          => ['more_power_date', 'more_power_number', 'more_power_giver', 'more_power_sponsor', 'more_power_dioceses', 'more_power_deanerys', 'more_power_parish'],
            'Rước lễ'           => ['communion_date', 'communion_number', 'communion_giver', 'communion_dioceses', 'communion_deanerys', 'communion_parish'],
            'Xức dầu'           => ['anoint_date', 'anoint_status', 'anoint_giver'],
            'Tử vong'           => ['die_time', 'die_burial', 'die_lottery'],
            'Hôn phối'          => ['date', 'sohonphoi', 'marriage_address', 'marriage_ward', 'marriage_province', 'priest', 'peopleone', 'peopletwo', 'tinhtrang'],
            'Ngày xuất'         => ['day', 'month', 'year'],
        ];

        foreach ($groups as $title => $keys) {
            $section->addText($title, ['bold' => true, 'size' => 12]);
            foreach ($keys as $key) {
                $section->addText('${' . $key . '}');
            }
            $section->addTextBreak(1);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($path);

        return $path;
    }
}
