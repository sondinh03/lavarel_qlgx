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
 * Dùng như một nested component trong trang show giáo dân.
 * Không extend BaseComponent vì không cần search/pagination.
 *
 * Cách dùng trong blade:
 *   @livewire('parishioners.sacraments-manager', ['parishionerId' => $parishioner->id])
 */
class SacramentsManager extends Component
{
    // ==================== PROPS ====================

    public int $parishionerId;

    // ==================== UI STATE ====================

    public bool    $showForm   = false;
    public ?int    $editingId  = null;
    public ?string $activeType = null;  // type đang mở form

    // ==================== FORM FIELDS ====================

    public string  $type                = '';
    public ?string $received_date       = null;
    public ?string $certificate_number  = null;
    public ?int    $book_number         = null;
    public ?string $giver               = null;
    public ?string $sponsor             = null;
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
            'parish_id'          => 'nullable|integer|exists:parishes,id',
            'parish_name'        => 'nullable|string|max:100',
            'deanery_id'         => 'nullable|integer|exists:deaneries,id',
            'diocese_id'         => 'nullable|integer|exists:dioceses,id',
            'note'               => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'type.required'    => 'Vui lòng chọn loại bí tích',
        'type.in'          => 'Loại bí tích không hợp lệ',
        'received_date.date' => 'Ngày không hợp lệ',
    ];

    // ==================== LISTENERS ====================

    protected $listeners = [
        'sacramentSaved' => '$refresh',
    ];

    // ==================== LIFECYCLE ====================

    public function mount(int $parishionerId): void
    {
        // Kiểm tra giáo dân tồn tại
        Parishioner::findOrFail($parishionerId);
        $this->parishionerId = $parishionerId;
    }

    // ==================== CRUD ====================

    /**
     * Mở form thêm bí tích — truyền type sẵn để khóa dropdown
     */
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
            $this->parish_id          = $s->parish_id;
            $this->parish_name        = $s->parish_name;
            $this->deanery_id         = $s->deanery_id;
            $this->diocese_id         = $s->diocese_id;
            $this->note               = $s->note;
            $this->showForm           = true;
        } catch (ModelNotFoundException) {
            session()->flash('sacrament_error', 'Không tìm thấy bí tích');
        }
    }

    public function save(): void
    {
        $this->validate();

        // Kiểm tra duplicate (trừ anointing)
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
                'parish_id'          => $this->parish_id,
                'parish_name'        => $this->parish_name,
                'deanery_id'         => $this->deanery_id,
                'diocese_id'         => $this->diocese_id,
                'note'               => $this->note,
            ];

            Sacrament::updateOrCreate(['id' => $this->editingId], $data);

            DB::commit();

            session()->flash('sacrament_message', $this->editingId
                ? 'Cập nhật bí tích thành công'
                : 'Thêm bí tích thành công'
            );

            $this->emit('sacramentSaved');
            $this->closeForm();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('sacrament_error', 'Có lỗi khi lưu. Vui lòng thử lại.');
        }
    }

    public function delete(int $id): void
    {
        try {
            $s = Sacrament::where('parishioner_id', $this->parishionerId)
                ->findOrFail($id);
            $s->delete();
            session()->flash('sacrament_message', 'Đã xóa bí tích');
        } catch (ModelNotFoundException) {
            session()->flash('sacrament_error', 'Không tìm thấy bí tích');
        }
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
            'editingId', 'type', 'activeType', 'received_date',
            'certificate_number', 'book_number', 'giver', 'sponsor',
            'parish_id', 'parish_name', 'deanery_id', 'diocese_id', 'note',
        ]);
        $this->resetValidation();
    }

    private function getTypeName(): string
    {
        return Sacrament::typeOptions()[$this->type] ?? $this->type;
    }

    /**
     * Load tất cả bí tích của giáo dân, group theo type để dễ hiển thị
     */
    private function getSacraments(): array
    {
        $sacraments = Sacrament::where('parishioner_id', $this->parishionerId)
            ->with(['parish', 'deanery', 'diocese'])
            ->orderBy('received_date')
            ->get();

        // Group: mỗi type 1 record (trừ anointing có thể nhiều)
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