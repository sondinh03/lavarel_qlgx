<?php

namespace App\Http\Livewire\Catechism;

use App\Actions\Catechism\SendCatechistAnnouncementAction;
use App\Http\Livewire\Base\BaseComponent;
use App\Models\GradeLevel;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

class CatechistAnnouncementComposer extends BaseComponent
{
    protected $usePagination = false;

    public string $title = '';

    public string $body = '';

    public string $linkUrl = '';

    /** all|grade */
    public string $audience = SendCatechistAnnouncementAction::AUDIENCE_ALL;

    public $gradeLevelId = null;

    public int $recipientPreview = 0;

    public array $gradeOptions = [];

    protected function loadInitialData(): void
    {
        $this->requireParishId();
        abort_unless(auth()->user()?->canManageCatechism(), 403);

        $this->gradeOptions = GradeLevel::query()
            ->active()
            ->ordered()
            ->get(['id', 'name'])
            ->map(fn ($g) => [
                'id'   => (string) $g->id,
                'name' => $g->name,
            ])
            ->values()
            ->toArray();

        $this->refreshRecipientPreview();
    }

    public function updatedAudience(): void
    {
        if ($this->audience !== SendCatechistAnnouncementAction::AUDIENCE_GRADE) {
            $this->gradeLevelId = null;
        }

        $this->refreshRecipientPreview();
    }

    public function updatedGradeLevelId(): void
    {
        $this->gradeLevelId = $this->gradeLevelId !== null && $this->gradeLevelId !== ''
            ? (int) $this->gradeLevelId
            : null;

        $this->refreshRecipientPreview();
    }

    public function refreshRecipientPreview(): void
    {
        if (! $this->parishId) {
            $this->recipientPreview = 0;

            return;
        }

        $this->recipientPreview = app(SendCatechistAnnouncementAction::class)->countRecipients(
            $this->parishId,
            $this->audience,
            $this->audience === SendCatechistAnnouncementAction::AUDIENCE_GRADE
                ? ($this->gradeLevelId ? (int) $this->gradeLevelId : null)
                : null,
            auth()->id()
        );
    }

    public function send(): void
    {
        abort_unless(auth()->user()?->canManageCatechism() && $this->parishId, 403);

        $this->validate([
            'title'         => 'required|string|max:150',
            'body'          => 'required|string|max:2000',
            'linkUrl'       => 'nullable|string|max:500',
            'audience'      => ['required', Rule::in([
                SendCatechistAnnouncementAction::AUDIENCE_ALL,
                SendCatechistAnnouncementAction::AUDIENCE_GRADE,
            ])],
            'gradeLevelId'  => [
                Rule::requiredIf(fn () => $this->audience === SendCatechistAnnouncementAction::AUDIENCE_GRADE),
                'nullable',
                'integer',
                'exists:grade_levels,id',
            ],
        ], [
            'title.required'        => 'Vui lòng nhập tiêu đề.',
            'body.required'         => 'Vui lòng nhập nội dung.',
            'gradeLevelId.required' => 'Vui lòng chọn khối.',
            'gradeLevelId.exists'   => 'Khối không hợp lệ.',
        ]);

        $link = trim($this->linkUrl);
        if ($link !== '' && ! preg_match('#^https?://#i', $link) && ! str_starts_with($link, '/')) {
            $link = '/' . ltrim($link, '/');
        }

        try {
            $result = app(SendCatechistAnnouncementAction::class)->handle(
                auth()->user(),
                (int) $this->parishId,
                $this->title,
                $this->body,
                $this->audience,
                $this->audience === SendCatechistAnnouncementAction::AUDIENCE_GRADE
                    ? (int) $this->gradeLevelId
                    : null,
                $link !== '' ? $link : null,
            );
        } catch (InvalidArgumentException $e) {
            $this->emit('toast', 'error', $e->getMessage());

            return;
        } catch (\Throwable $e) {
            report($e);
            $this->emit('toast', 'error', 'Không gửi được thông báo. Vui lòng thử lại.');

            return;
        }

        $this->reset(['title', 'body', 'linkUrl']);
        $this->audience = SendCatechistAnnouncementAction::AUDIENCE_ALL;
        $this->gradeLevelId = null;
        $this->refreshRecipientPreview();

        $this->emit(
            'toast',
            'message',
            'Đã gửi tới ' . $result['count'] . ' GLV (' . $result['audience_label'] . ').'
        );
    }

    public function render()
    {
        return view('livewire.catechism.catechist-announcement-composer')
            ->extends('frontend.layout.main')
            ->section('content');
    }
}
