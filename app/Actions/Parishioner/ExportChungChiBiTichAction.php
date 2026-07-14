<?php

namespace App\Actions\Parishioner;

use App\Models\Parishioner;
use App\Presenters\ParishionerChungChiBiTichPresenter;
use App\Support\ChungChiBiTichTemplateGenerator;
use PhpOffice\PhpWord\TemplateProcessor;

class ExportChungChiBiTichAction
{
    public function handle(
        Parishioner $parishioner,
        string $recipientPriest = '',
        string $recipientDiocese = '',
        string $purpose = '',
    ): array {
        $templatePath = ChungChiBiTichTemplateGenerator::ensureExists();
        $presenter = ParishionerChungChiBiTichPresenter::for(
            $parishioner,
            $recipientPriest,
            $recipientDiocese,
            $purpose,
        );
        $processor = new TemplateProcessor($templatePath);

        foreach ($presenter->toPlaceholders() as $key => $value) {
            $processor->setValue($key, $this->escape((string) $value));
        }

        $tempPath = storage_path('app/temp/' . uniqid('chungchibitich_', true) . '.docx');
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
