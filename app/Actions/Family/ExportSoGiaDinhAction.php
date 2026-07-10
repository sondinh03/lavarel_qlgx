<?php

namespace App\Actions\Family;

use App\Models\Family;
use App\Presenters\FamilySoGiaDinhPresenter;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportSoGiaDinhAction
{
    public function handle(Family $family): array
    {
        $templatePath = public_path('word-template/sogiadinhconggiao_1-1-001.docx');

        if (!is_file($templatePath)) {
            throw new \RuntimeException('Không tìm thấy mẫu sổ gia đình công giáo.');
        }

        $presenter    = FamilySoGiaDinhPresenter::for($family);
        $placeholders = $presenter->toPlaceholders();
        $processor    = new TemplateProcessor($templatePath);

        foreach ($placeholders as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('sogiadinh_', true) . '.docx');
        $dir      = dirname($tempPath);

        if (!is_dir($dir)) {
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
        return str_replace(
            ['&', '<', '>', '"'],
            ['&amp;', '&lt;', '&gt;', '&quot;'],
            $value
        );
    }
}
