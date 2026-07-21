<?php

namespace App\Http\Livewire\Dashboard;

use App\Http\Livewire\Base\BaseComponent;
use App\Models\AttendanceSession;
use App\Models\CatechismClass;
use App\Notifications\CatechismBoardAnnouncement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CatechistDashboard extends BaseComponent
{
    protected $usePagination = false;

    public $activeSchoolYear;

    public string $schoolYearPhaseLabel = '';

    public string $todayLabel = '';

    public int $pendingTodayCount = 0;

    /** GLV chưa có phân công trong năm học đang vận hành */
    public bool $assignmentBlocked = false;

    /** @var Collection<int, \Illuminate\Notifications\DatabaseNotification> */
    public $boardAnnouncements;

    /** @var Collection<int, \Illuminate\Notifications\DatabaseNotification> */
    public $latestNotifications;

    protected function loadInitialData(): void
    {
        $this->requireParishId();

        $this->todayLabel = Carbon::now()
            ->locale('vi')
            ->isoFormat('dddd, D/M/YYYY');

        $operating = app(\App\Services\SchoolYearResolver::class)
            ->resolve($this->parishId ? (int) $this->parishId : null);

        $this->activeSchoolYear = $operating?->namHoc;
        $this->schoolYearPhaseLabel = $operating?->semesterLabel() ?? '';

        $user = auth()->user();
        $this->assignmentBlocked = $user
            && ! app(\App\Services\CatechistAccess::class)
                ->hasActiveAssignmentThisYear($user, $this->parishId);

        if (! $this->assignmentBlocked) {
            $this->loadPendingTodayCount();
        }

        $this->loadHighlightNotifications();
    }

    public function mount(): void
    {
        if (auth()->user()?->canManageCatechism()) {
            redirect()->route('parish-admin.dashboard');

            return;
        }

        $this->boardAnnouncements = collect();
        $this->latestNotifications = collect();

        parent::mount();
    }

    protected function loadPendingTodayCount(): void
    {
        if (! $this->activeSchoolYear || ! $this->parishId) {
            $this->pendingTodayCount = 0;

            return;
        }

        $classIds = CatechismClass::query()
            ->where('school_year_id', $this->activeSchoolYear->id)
            ->where('parish_id', $this->parishId)
            ->active()
            ->pluck('id');

        if ($classIds->isEmpty()) {
            $this->pendingTodayCount = 0;

            return;
        }

        $this->pendingTodayCount = AttendanceSession::query()
            ->whereIn('class_id', $classIds)
            ->whereDate('date', Carbon::today())
            ->where('status', '!=', AttendanceSession::STATUS_CANCELLED)
            ->where(function ($q) {
                $q->where('status', AttendanceSession::STATUS_OPENING)
                    ->orWhereHas('records', fn ($r) => $r->whereNull('status'));
            })
            ->count();
    }

    protected function loadHighlightNotifications(): void
    {
        $user = auth()->user();
        if (! $user) {
            $this->boardAnnouncements = collect();
            $this->latestNotifications = collect();

            return;
        }

        $boardType = CatechismBoardAnnouncement::class;

        // Ưu tiên thông báo ban giáo lý chưa đọc, rồi mới nhất gần đây.
        $unreadBoard = $user->unreadNotifications()
            ->where('type', $boardType)
            ->latest()
            ->limit(3)
            ->get();

        if ($unreadBoard->count() < 3) {
            $extra = $user->notifications()
                ->where('type', $boardType)
                ->whereNotIn('id', $unreadBoard->pluck('id'))
                ->latest()
                ->limit(3 - $unreadBoard->count())
                ->get();
            $this->boardAnnouncements = $unreadBoard->concat($extra)->values();
        } else {
            $this->boardAnnouncements = $unreadBoard;
        }

        $excludeIds = $this->boardAnnouncements->pluck('id');

        $this->latestNotifications = $user->notifications()
            ->when($excludeIds->isNotEmpty(), fn ($q) => $q->whereNotIn('id', $excludeIds))
            ->latest()
            ->limit(5)
            ->get();
    }

    public function openHighlightedNotification(string $id)
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $notification = $user->notifications()->where('id', $id)->first();
        if (! $notification) {
            return;
        }

        if ($notification->unread()) {
            $notification->markAsRead();
        }

        $url = $notification->data['url'] ?? null;

        return $url
            ? redirect()->to($url)
            : redirect()->route('notifications.index');
    }

    public function render()
    {
        return view('livewire.dashboard.catechist-dashboard', [
            'activeSchoolYear'       => $this->activeSchoolYear,
            'schoolYearPhaseLabel'   => $this->schoolYearPhaseLabel,
            'todayLabel'             => $this->todayLabel,
            'pendingTodayCount'      => $this->pendingTodayCount,
            'assignmentBlocked'      => $this->assignmentBlocked,
            'boardAnnouncements'     => $this->boardAnnouncements,
            'latestNotifications'    => $this->latestNotifications,
        ])
            ->extends('frontend.layout.catechist')
            ->section('content');
    }
}
