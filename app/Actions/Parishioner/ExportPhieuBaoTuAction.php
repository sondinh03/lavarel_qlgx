<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerPhieuBaoTuPresenter;
use App\Support\PhieuBaoTuTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;

class ExportPhieuBaoTuAction
{
    public function handle(Parishioner $parishioner): array
    {
        if (! $parishioner->death_date) {
            throw new RuntimeException('Giáo dân chưa có ngày mất — không thể xuất giấy báo tử.');
        }

        $templatePath = PhieuBaoTuTemplateGenerator::ensureExists();
        $presenter = ParishionerPhieuBaoTuPresenter::for($parishioner);
        $processor = new TemplateProcessor($templatePath);

        foreach ($presenter->toPlaceholders() as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('phieubaotu_', true) . '.docx');
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
