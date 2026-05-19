<?php

namespace App\Http\Livewire\Parishioners;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\Parishioner;
use App\Models\Sacrament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * SacramentsManager
 *
 * Nested component dùng trong trang show giáo dân.
 *
 * Cách dùng:
 *   @livewire('parishioners.sacraments-manager', ['parishionerId' => $parishioner->id])
 */
class SacramentsManager extends Component
{
    // ==================== PROPS ====================

    public int $parishionerId;

    // ==================== UI STATE ====================

    public bool    $showForm          = false;
    public ?int    $editingId         = null;
    public ?string $activeType        = null;
    public $deleteId                  = null;
    public bool    $showDeleteConfirm = false;

    // ==================== FORM FIELDS ====================

    public string  $type                = '';
    public ?string $received_date       = null;
    public ?string $certificate_number  = null;
    public ?int    $book_number         = null;
    public ?string $giver               = null;
    public ?string $sponsor             = null;
    public ?string $church_name         = null;  // tên nhà thờ / họ đạo cụ thể
    public ?string $anointing_condition = null;  // tình trạng xức dầu (chỉ dùng khi type=anointing)
    public ?int    $parish_id           = null;
    public ?string $parish_name         = null;
    public ?int    $deanery_id          = null;
    public ?int    $diocese_id          = null;
    public ?string $note                = null;

    // ==================== VALIDATION ====================

    protected function rules(): array
    {
        return [
            'type'               => 'required|in:baptism,communion,confirmation,anointing,holy_orders',
            'received_date'      => 'nullable|date',
            'certificate_number' => 'nullable|string|max:50',
            'book_number'        => 'nullable|integer|min:1',
            'giver'              => 'nullable|string|max:100',
            'sponsor'            => 'nullable|string|max:100',
            'church_name'        => 'nullable|string|max:100',
            'anointing_condition'=> 'nullable|string|max:100',
            'parish_id'          => 'nullable|integer|exists:parishes,id',
            'parish_name'        => 'nullable|string|max:100',
            'deanery_id'         => 'nullable|integer|exists:deaneries,id',
            'diocese_id'         => 'nullable|integer|exists:dioceses,id',
            'note'               => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'type.required'      => 'Vui lòng chọn loại bí tích',
        'type.in'            => 'Loại bí tích không hợp lệ',
        'received_date.date' => 'Ngày không hợp lệ',
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'sacramentSaved' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(int $parishionerId): void
    {
        Parishioner::findOrFail($parishionerId);
        $this->parishionerId = $parishionerId;
    }

    // ==================== CRUD ====================

    public function create(string $type = ''): void
    {
        $this->resetForm();
        $this->type       = $type;
        $this->activeType = $type ?: null;
        $this->showForm   = true;
    }

    public function edit(int $id): void
    {
        try {
            $s = Sacrament::where('parishioner_id', $this->parishionerId)
                ->findOrFail($id);

            $this->editingId          = $s->id;
            $this->type               = $s->type;
            $this->activeType         = $s->type;
            $this->received_date      = $s->received_date?->format('Y-m-d');
            $this->certificate_number = $s->certificate_number;
            $this->book_number        = $s->book_number;
            $this->giver              = $s->giver;
            $this->sponsor            = $s->sponsor;
            $this->church_name        = $s->church_name;
            $this->anointing_condition = $s->anointing_condition;
            $this->parish_id          = $s->parish_id;
            $this->parish_name        = $s->parish_name;
            $this->deanery_id         = $s->deanery_id;
            $this->diocese_id         = $s->diocese_id;
            $this->note               = $s->note;
            $this->showForm           = true;
        } catch (ModelNotFoundException) {
            $this->emit('toast','sacrament_error', 'Không tìm thấy bí tích');
        }
    }

    public function save(): void
    {
        $this->validate();

        // Kiểm tra duplicate (trừ anointing có thể lãnh nhiều lần)
        if ($this->type !== Sacrament::TYPE_ANOINTING) {
            $exists = Sacrament::where('parishioner_id', $this->parishionerId)
                ->where('type', $this->type)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->exists();

            if ($exists) {
                $this->addError('type', 'Giáo dân đã có bí tích ' . $this->getTypeName());
                return;
            }
        }

        try {
            DB::beginTransaction();

            $data = [
                'parishioner_id'     => $this->parishionerId,
                'type'               => $this->type,
                'received_date'      => $this->received_date ?: null,
                'certificate_number' => $this->certificate_number,
                'book_number'        => $this->book_number,
                'giver'              => $this->giver,
                'sponsor'            => $this->sponsor,
                'church_name'        => $this->church_name,
                // Chỉ lưu anointing_condition khi type = anointing
                'anointing_condition' => $this->type === Sacrament::TYPE_ANOINTING
                    ? $this->anointing_condition
                    : null,
                'parish_id'          => $this->parish_id,
                'parish_name'        => $this->parish_name,
                'deanery_id'         => $this->deanery_id,
                'diocese_id'         => $this->diocese_id,
                'note'               => $this->note,
            ];

            Sacrament::updateOrCreate(['id' => $this->editingId], $data);

            DB::commit();

            $this->emit('toast',
                'sacrament_message',
                $this->editingId ? 'Cập nhật bí tích thành công' : 'Thêm bí tích thành công'
            );

            $this->emit('sacramentSaved');
            $this->closeForm();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->emit('toast','sacrament_error', 'Có lỗi khi lưu. Vui lòng thử lại.');
        }
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId          = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        try {
            $s = Sacrament::where('parishioner_id', $this->parishionerId)
                ->findOrFail($this->deleteId);
            $s->delete();
            $this->emit('toast','sacrament_message', 'Đã xóa bí tích');
        } catch (ModelNotFoundException) {
            $this->emit('toast','sacrament_error', 'Không tìm thấy bí tích');
        }

        $this->showDeleteConfirm = false;
        $this->deleteId          = null;
    }

    // ==================== HELPERS ====================

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'type', 'activeType',
            'received_date', 'certificate_number', 'book_number',
            'giver', 'sponsor', 'church_name', 'anointing_condition',
            'parish_id', 'parish_name', 'deanery_id', 'diocese_id', 'note',
        ]);
        $this->resetValidation();
    }

    private function getTypeName(): string
    {
        return Sacrament::typeOptions()[$this->type] ?? $this->type;
    }

    private function getSacraments(): array
    {
        $sacraments = Sacrament::where('parishioner_id', $this->parishionerId)
            ->with(['parish', 'deanery', 'diocese'])
            ->orderBy('received_date')
            ->get();

        $grouped = [];
        foreach (Sacrament::typeOptions() as $type => $label) {
            $grouped[$type] = [
                'label'    => $label,
                'records'  => $sacraments->where('type', $type)->values(),
                'multiple' => $type === Sacrament::TYPE_ANOINTING,
            ];
        }

        return $grouped;
    }

    // ==================== RENDER ====================

    public function render()
    {
        return view('livewire.parishioners.sacraments-manager', [
            'groupedSacraments' => $this->getSacraments(),
            'typeOptions'       => Sacrament::typeOptions(),
        ]);
    }
}