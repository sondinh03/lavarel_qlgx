<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerDonXinRuaToiPresenter;
use App\Support\DonXinRuaToiTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportDonXinRuaToiAction
{
    public function handle(
        Parishioner $parishioner,
        string $holyFullName,
        string $godparentName,
        string $birthday,
        string $birthPlace = '',
        ?int $birthOrder = null
    ): array {
        $templatePath = DonXinRuaToiTemplateGenerator::ensureExists();
        $presenter    = ParishionerDonXinRuaToiPresenter::for(
            $parishioner,
            $holyFullName,
            $godparentName,
            $birthday,
            $birthPlace,
            $birthOrder
        );
        $placeholders = $presenter->toPlaceholders();

        $processor = new TemplateProcessor($templatePath);

        foreach ($placeholders as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('donxinruatoi_', true) . '.docx');
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
