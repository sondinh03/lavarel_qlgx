<?php

namespace App\Actions\MarriageAnnouncement;

use App\Models\MarriageAnnouncement;
use App\Presenters\MarriageAnnouncementGioiThieuHonPhoiPresenter;
use App\Support\GioiThieuHonPhoiTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportGioiThieuHonPhoiAction
{
    public function handle(
        MarriageAnnouncement $announcement,
        string $subjectSide = 'groom',
        string $greetingParish = '',
        array $overrides = [],
    ): array {
        $templatePath = GioiThieuHonPhoiTemplateGenerator::ensureExists();
        $presenter = MarriageAnnouncementGioiThieuHonPhoiPresenter::for(
            $announcement,
            $subjectSide,
            $greetingParish,
            $overrides,
        );
        $processor = new TemplateProcessor($templatePath);

        foreach ($presenter->toPlaceholders() as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('gioithieuhonphoi_', true) . '.docx');
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
