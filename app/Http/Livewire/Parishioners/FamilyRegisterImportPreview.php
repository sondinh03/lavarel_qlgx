<?php

namespace App\Http\Livewire\Parishioners;

use App\Actions\Parishioner\ImportFamilyRegisterAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Imports\FamilyRegisterPreviewImport;
use App\Models\Parishioner;
use App\Support\FamilyRegisterImportValidator;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;

class FamilyRegisterImportPreview extends BaseComponent
{
    use WithFileUploads;

    public $file = null;

    public array $families         = [];
    public array $parishioners     = [];
    public array $sacraments      = [];
    public array $marriages       = [];
    public array $errors          = [];
    public array $warnings        = [];
    public bool  $readyToImport   = false;

    protected $rules = [
        'file' => 'required|mimes:xlsx,csv|max:5120',
    ];

    protected $messages = [
        'file.required' => 'Vui lòng chọn file Excel',
        'file.mimes'    => 'File phải có định dạng .xlsx hoặc .csv',
        'file.max'      => 'File không được vượt quá 5MB',
    ];

    public function mount(): void
    {
        parent::mount();
        $this->requireManager();
        $this->requireParishId();
        $this->authorize('create', Parishioner::class);
    }

    public function loadInitialData(): void {}

    public function updatedFile(): void
    {
        $this->resetPreview();
        $this->preview();
    }

    public function preview(): void
    {
        $this->validate();
        $this->resetPreview();

        try {
            $allSheets = Excel::toArray(new FamilyRegisterPreviewImport, $this->file);
            $result    = app(FamilyRegisterImportValidator::class)->validate($allSheets, $this->parishId);

            $this->errors        = $result['errors'];
            $this->warnings      = $result['warnings'];
            $this->families      = $result['families'] ?? [];
            $this->parishioners  = $result['parishioners'];
            $this->sacraments    = $result['sacraments'];
            $this->marriages     = $result['marriages'];
            $this->readyToImport = $result['ready'];

            if ($this->readyToImport) {
                $familyCount = is_array($this->families) ? count($this->families) : 0;
                $msg = sprintf(
                    'Sẵn sàng import: %d hộ, %d giáo dân, %d bí tích, %d hôn phối.',
                    $familyCount,
                    count($this->parishioners),
                    count($this->sacraments),
                    count($this->marriages)
                );
                $this->emit('toast', 'info', $msg);
            } elseif (!empty($this->errors)) {
                $this->emit('toast', 'error', 'File có ' . count($this->errors) . ' lỗi — vui lòng sửa trước khi import.');
            }
        } catch (\Exception $e) {
            $this->logError($e, 'Error previewing family register import');
            $this->errors[] = 'Lỗi khi đọc file: ' . $e->getMessage();
        }
    }

    public function confirmImport()
    {
        if (!$this->readyToImport) {
            $this->emit('toast', 'error', 'Dữ liệu chưa hợp lệ, không thể import');
            return;
        }

        try {
            $result = app(ImportFamilyRegisterAction::class)->handle(
                $this->parishioners,
                $this->sacraments,
                $this->marriages,
                $this->parishId,
                $this->families
            );

            $parts = ['Import Sổ Gia Đình hoàn tất.'];
            if ($result['families_created'] > 0) {
                $parts[] = "Tạo {$result['families_created']} gia đình.";
            }
            if ($result['parishioners_created'] > 0) {
                $parts[] = "Thêm {$result['parishioners_created']} giáo dân.";
            }
            if ($result['sacraments_created'] > 0) {
                $parts[] = "Tạo {$result['sacraments_created']} bí tích.";
            }
            if ($result['marriages_created'] > 0) {
                $parts[] = "Tạo {$result['marriages_created']} hôn phối.";
            }

            session()->flash('message', implode(' ', $parts));
            return redirect()->route('parishioners.index');
        } catch (\Exception $e) {
            $this->logError($e, 'Error confirming family register import');
            $this->emit('toast', 'error', 'Có lỗi khi import: ' . $e->getMessage());
        }
    }

    public function resetUpload(): void
    {
        $this->file = null;
        $this->resetPreview();
        $this->resetValidation();
    }

    protected function resetPreview(): void
    {
        $this->families       = [];
        $this->parishioners   = [];
        $this->sacraments     = [];
        $this->marriages      = [];
        $this->errors         = [];
        $this->warnings       = [];
        $this->readyToImport  = false;
    }

    public function render()
    {
        return view('livewire.parishioners.family-register-import-preview')
            ->extends('frontend.layout.parishioner')
            ->section('content');
    }
}
