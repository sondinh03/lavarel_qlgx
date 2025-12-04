<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Throwable;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\ParishManagement;
use App\Models\Parish;
use PhpOffice\PhpSpreadsheet\IOFactory;


class LopImport implements WithMultipleSheets, WithHeadingRow, SkipsOnError, WithValidation, SkipsOnFailure, WithChunkReading, ShouldQueue, WithEvents
{
    use Importable, SkipsErrors, SkipsFailures, RegistersEventListeners;
    
    protected $filePath;
    
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }
    
    public function sheets(): array
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        
        $imports = [];
        foreach ($sheetNames as $name) {
            $imports[$name] = new LophocImport($name);
        }
        
        return $imports;
    }
    
    public function onUnknownSheet($sheetName)
    {
        // E.g. you can log that a sheet was not found.
        info("Sheet {$sheetName} was skipped");
    }
    
    public function rules(): array
    {
        return [
            '*.email' => ['email', 'unique:users,email']
        ];
    }
    
    
    public function chunkSize(): int
    {
        return 1000;
    }
    
    public static function afterImport(AfterImport $event)
    {
        
    }
    
    public function onFailure(Failure ...$failure)
    {
        
    }
}
