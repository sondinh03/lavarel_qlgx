<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerLyLichPresenter;
use App\Support\LyLichTemplateGenerator;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportLyLichCaNhanAction
{
    private const HEADER_PLACEHOLDERS = ['did', 'deid', 'pid'];

    private const HEADER_FONT = [
        'name' => 'Times New Roman',
        'size' => 13,
    ];

    public function handle(Parishioner $parishioner): array
    {
        $templatePath = LyLichTemplateGenerator::ensureExists();
        $presenter    = ParishionerLyLichPresenter::for($parishioner);
        $placeholders = $presenter->toPlaceholders();

        $processor = new TemplateProcessor($templatePath);

        foreach ($placeholders as $key => $value) {
            if (in_array($key, self::HEADER_PLACEHOLDERS, true)) {
                $this->setHeaderValue($processor, $key, $this->escape($value));
                continue;
            }

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

    private function setHeaderValue(TemplateProcessor $processor, string $key, string $value): void
    {
        $textRun = new TextRun();
        $textRun->addText($value, self::HEADER_FONT);
        $processor->setComplexValue($key, $textRun);
    }

    private function escape(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
