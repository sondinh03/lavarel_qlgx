<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerGioiThieuGiaoLyDuTongPresenter;
use App\Support\GioiThieuGiaoLyDuTongTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportGioiThieuGiaoLyDuTongAction
{
    public function handle(
        Parishioner $parishioner,
        string $fullName,
        string $birthday,
        string $address = '',
        string $fatherName = '',
        string $motherName = '',
        string $coursePlace = '',
        string $greetingTo = '',
    ): array {
        $templatePath = GioiThieuGiaoLyDuTongTemplateGenerator::ensureExists();
        $presenter    = ParishionerGioiThieuGiaoLyDuTongPresenter::for(
            $parishioner,
            $fullName,
            $birthday,
            $address,
            $fatherName,
            $motherName,
            $coursePlace,
            $greetingTo,
        );
        $processor = new TemplateProcessor($templatePath);

        foreach ($presenter->toPlaceholders() as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('gioithieugiaolydutong_', true) . '.docx');
        $dir = dirname($tempPath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $processor->saveAs($tempPath);

        return [
            'path'     => $tempPath,
            'filename' => $presenter->downloadFilename(),
        ];
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
