<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerLyLichPresenter;
use App\Support\LyLichTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportLyLichCaNhanAction
{
    public function handle(Parishioner $parishioner): array
    {
        $templatePath = LyLichTemplateGenerator::ensureExists();
        $presenter    = ParishionerLyLichPresenter::for($parishioner);
        $placeholders = $presenter->toPlaceholders();

        $processor = new TemplateProcessor($templatePath);

        foreach ($placeholders as $key => $value) {
            $processor->setValue($key, $this->escape($value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('lylich_', true) . '.docx');
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

    private function escape(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
