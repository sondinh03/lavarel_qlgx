<?php

namespace App\Services\Admin;

use App\Models\CatechismClass;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\NamHoc;
use App\Models\ParishAdminRegistrationRequest;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\StudentNew;
use App\Models\SystemMetricDaily;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserLoginEvent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class SystemOverviewService
{
    public const CACHE_TTL = 300;

    public function get(): array
    {
        return Cache::remember('backpack.system_overview.v4', self::CACHE_TTL, function () {
            $overview = $this->buildOverview();
            $usage = $this->buildUsage();
            $performance = $this->buildPerformance();
            $registrations = $this->buildRegistrationFunnel();
            $attention = $this->buildAttentionParishes();
            $health = $this->buildHealth();

            return [
                'overview'      => $overview,
                'scale'         => $this->buildScale(),
                'usage'         => $usage,
                'performance'   => $performance,
                'roles'         => $this->buildRoleCounts(),
                'registrations' => $registrations,
                'pending'       => $this->buildPendingRegistrations(),
                'alerts'        => $this->buildAlerts($overview, $usage, $performance, $registrations, $attention, $health),
                'attention'     => $attention['items'],
                'attention_counts' => $attention['counts'],
                'health'        => $health,
                'generated_at'  => now(),
            ];
        });
    }

    public function forget(): void
    {
        Cache::forget('backpack.system_overview.v1');
        Cache::forget('backpack.system_overview.v2');
        Cache::forget('backpack.system_overview.v3');
        Cache::forget('backpack.system_overview.v4');
    }

    /**
     * Tìm nhanh user / giáo xứ để hỗ trợ (không cache).
     *
     * @return array{query: string, users: Collection, parishes: Collection}
     */
    public function searchSupport(string $query): array
    {
        $q = trim($query);
        if ($q === '' || mb_strlen($q) < 2) {
            return [
                'query'    => $q,
                'users'    => collect(),
                'parishes' => collect(),
            ];
        }

        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

        $users = User::query()
            ->with(['parish:id,name', 'roles:id,name'])
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like);
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'email', 'parish_id', 'is_active', 'last_login_at'])
            ->map(fn (User $u) => [
                'id'            => $u->id,
                'name'          => $u->name,
                'email'         => $u->email,
                'parish'        => $u->parish?->name,
                'is_active'     => (bool) $u->is_active,
                'last_login_at' => $u->last_login_at,
                'roles'         => $u->getRoleNames()->implode(', ') ?: '—',
                'url'           => backpack_url('user/' . $u->id . '/edit'),
            ]);

        $parishes = ParishNew::query()
            ->with(['diocese:id,name', 'deanery:id,name'])
            ->withCount(['students', 'teachers', 'users'])
            ->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('code', 'like', $like)
                    ->orWhere('phone', 'like', $like)
                    ->orWhere('parish_priest_name', 'like', $like);
            })
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(fn (ParishNew $p) => [
                'id'       => $p->id,
                'name'     => $p->name,
                'code'     => $p->code,
                'phone'    => $p->phone,
                'status'   => (bool) $p->status,
                'diocese'  => $p->diocese?->name,
                'deanery'  => $p->deanery?->name,
                'students' => (int) $p->students_count,
                'teachers' => (int) $p->teachers_count,
                'users'    => (int) $p->users_count,
                'url'      => backpack_url('parish-management/' . $p->id . '/show'),
                'edit_url' => backpack_url('parish-management/' . $p->id . '/edit'),
            ]);

        return [
            'query'    => $q,
            'users'    => $users,
            'parishes' => $parishes,
        ];
    }

    protected function buildOverview(): array
    {
        $parishesTotal = ParishNew::query()->count();
        $parishesActive = ParishNew::query()->where('status', true)->count();

        $parishesWithAdmin = User::query()
            ->whereNotNull('parish_id')
            ->role(['parish_admin', 'parishioner_admin', 'catechism_admin'])
            ->pluck('parish_id')
            ->unique()
            ->count();

        return [
            'parishes_total'         => $parishesTotal,
            'parishes_active'        => $parishesActive,
            'parishes_inactive'      => max(0, $parishesTotal - $parishesActive),
            'parishes_with_admin'    => $parishesWithAdmin,
            'parishes_without_admin' => max(0, $parishesActive - $parishesWithAdmin),
            'dioceses'               => Diocese::query()->count(),
            'deaneries'              => Deanery::query()->count(),
            'users_total'            => User::query()->count(),
            'users_with_parish'      => User::query()->whereNotNull('parish_id')->count(),
        ];
    }

    /**
     * Chỉ số quy mô dữ liệu nền tảng (học sinh, lớp năm active, GLV, giáo dân).
     *
     * @return array<string, int>
     */
    protected function buildScale(): array
    {
        return [
            'students_total'      => StudentNew::query()->count(),
            'students_active'     => StudentNew::query()->where('is_active', true)->count(),
            'teachers_total'      => Teacher::query()->count(),
            'teachers_active'     => Teacher::query()->where('is_active', true)->count(),
            'parishioners_total'  => Parishioner::query()->count(),
            'parishioners_active' => Parishioner::query()->where('is_active', true)->count(),
            'classes_total'       => CatechismClass::query()->count(),
            'classes_active_year' => CatechismClass::query()
                ->whereIn('school_year_id', NamHoc::query()->active()->select('id'))
                ->count(),
            'school_years_active' => NamHoc::query()->active()->count(),
        ];
    }

    /**
     * Sử dụng / engagement: đăng nhập, người dùng hoạt động.
     *
     * @return array<string, mixed>
     */
    protected function buildUsage(): array
    {
        if (! Schema::hasTable('system_metric_daily')) {
            return $this->emptyUsage();
        }

        $today = Carbon::today()->toDateString();
        $from7 = Carbon::today()->subDays(6)->toDateString();

        $todayRow = SystemMetricDaily::query()->where('metric_date', $today)->first();
        $weekAgg = SystemMetricDaily::query()
            ->whereBetween('metric_date', [$from7, $today])
            ->selectRaw('COALESCE(SUM(logins),0) as logins, COALESCE(SUM(failed_logins),0) as failed_logins')
            ->first();

        $uniqueToday = 0;
        $unique7d = 0;
        if (Schema::hasTable('user_login_events')) {
            $uniqueToday = (int) UserLoginEvent::query()
                ->whereDate('created_at', $today)
                ->distinct()
                ->count('user_id');
            $unique7d = (int) UserLoginEvent::query()
                ->where('created_at', '>=', Carbon::today()->subDays(6)->startOfDay())
                ->distinct()
                ->count('user_id');
        }

        $active7d = 0;
        $active30d = 0;
        if (Schema::hasColumn('users', 'last_login_at')) {
            $active7d = (int) User::query()
                ->where('last_login_at', '>=', Carbon::now()->subDays(7))
                ->count();
            $active30d = (int) User::query()
                ->where('last_login_at', '>=', Carbon::now()->subDays(30))
                ->count();
        }

        $days = SystemMetricDaily::query()
            ->whereBetween('metric_date', [$from7, $today])
            ->orderBy('metric_date')
            ->get(['metric_date', 'logins', 'failed_logins']);

        $dayMap = $days->keyBy(fn ($r) => $r->metric_date->toDateString());
        $trend = collect();
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::today()->subDays($i);
            $key = $d->toDateString();
            $row = $dayMap->get($key);
            $trend->push([
                'label'  => $d->format('d/m'),
                'date'   => $key,
                'logins' => (int) ($row->logins ?? 0),
                'failed' => (int) ($row->failed_logins ?? 0),
            ]);
        }

        return [
            'logins_today'        => (int) ($todayRow->logins ?? 0),
            'failed_logins_today' => (int) ($todayRow->failed_logins ?? 0),
            'logins_7d'           => (int) ($weekAgg->logins ?? 0),
            'failed_logins_7d'    => (int) ($weekAgg->failed_logins ?? 0),
            'unique_users_today'  => $uniqueToday,
            'unique_users_7d'     => $unique7d,
            'active_users_7d'     => $active7d,
            'active_users_30d'    => $active30d,
            'trend_7d'            => $trend,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyUsage(): array
    {
        return [
            'logins_today'        => 0,
            'failed_logins_today' => 0,
            'logins_7d'           => 0,
            'failed_logins_7d'    => 0,
            'unique_users_today'  => 0,
            'unique_users_7d'     => 0,
            'active_users_7d'     => 0,
            'active_users_30d'    => 0,
            'trend_7d'            => collect(),
        ];
    }

    /**
     * Hiệu năng HTTP (web): request, chậm, lỗi 5xx, thời gian TB.
     *
     * @return array<string, mixed>
     */
    protected function buildPerformance(): array
    {
        if (! Schema::hasTable('system_metric_daily')) {
            return $this->emptyPerformance();
        }

        $today = Carbon::today()->toDateString();
        $from7 = Carbon::today()->subDays(6)->toDateString();

        $todayRow = SystemMetricDaily::query()->where('metric_date', $today)->first();
        $weekAgg = SystemMetricDaily::query()
            ->whereBetween('metric_date', [$from7, $today])
            ->selectRaw('COALESCE(SUM(requests),0) as requests, COALESCE(SUM(slow_requests),0) as slow_requests, COALESCE(SUM(server_errors),0) as server_errors, COALESCE(SUM(avg_duration_ms_sum),0) as duration_sum')
            ->first();

        $reqToday = (int) ($todayRow->requests ?? 0);
        $sumToday = (int) ($todayRow->avg_duration_ms_sum ?? 0);
        $slowToday = (int) ($todayRow->slow_requests ?? 0);
        $errToday = (int) ($todayRow->server_errors ?? 0);

        $req7 = (int) ($weekAgg->requests ?? 0);
        $sum7 = (int) ($weekAgg->duration_sum ?? 0);
        $slow7 = (int) ($weekAgg->slow_requests ?? 0);
        $err7 = (int) ($weekAgg->server_errors ?? 0);

        return [
            'requests_today'      => $reqToday,
            'avg_ms_today'        => $reqToday > 0 ? (int) round($sumToday / $reqToday) : null,
            'slow_requests_today' => $slowToday,
            'slow_rate_today'     => $reqToday > 0 ? round($slowToday / $reqToday * 100, 1) : null,
            'server_errors_today' => $errToday,
            'requests_7d'         => $req7,
            'avg_ms_7d'           => $req7 > 0 ? (int) round($sum7 / $req7) : null,
            'slow_requests_7d'    => $slow7,
            'slow_rate_7d'        => $req7 > 0 ? round($slow7 / $req7 * 100, 1) : null,
            'server_errors_7d'    => $err7,
            'slow_threshold_ms'   => 1000,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function emptyPerformance(): array
    {
        return [
            'requests_today'      => 0,
            'avg_ms_today'        => null,
            'slow_requests_today' => 0,
            'slow_rate_today'     => null,
            'server_errors_today' => 0,
            'requests_7d'         => 0,
            'avg_ms_7d'           => null,
            'slow_requests_7d'    => 0,
            'slow_rate_7d'        => null,
            'server_errors_7d'    => 0,
            'slow_threshold_ms'   => 1000,
        ];
    }

    /**
     * @return array<string, int>
     */
    protected function buildRoleCounts(): array
    {
        $roles = [
            'super_admin',
            'parish_admin',
            'parishioner_admin',
            'catechism_admin',
            'catechist',
        ];

        $counts = [];
        foreach ($roles as $role) {
            $counts[$role] = User::role($role)->count();
        }

        return $counts;
    }

    protected function buildRegistrationFunnel(): array
    {
        $byStatus = ParishAdminRegistrationRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $pending = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_PENDING] ?? 0);
        $approved = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_APPROVED] ?? 0);
        $rejected = (int) ($byStatus[ParishAdminRegistrationRequest::STATUS_REJECTED] ?? 0);
        $total = $pending + $approved + $rejected;
        $decided = $approved + $rejected;

        $weeks = $this->buildWeeklyTrend(8);

        return [
            'pending'       => $pending,
            'approved'      => $approved,
            'rejected'      => $rejected,
            'total'         => $total,
            'approval_rate' => $decided > 0 ? round($approved / $decided * 100) : null,
            'weeks'         => $weeks,
            'this_week'     => $weeks->last(),
        ];
    }

    /**
     * @return Collection<int, array{label: string, start: string, submitted: int, approved: int, rejected: int}>
     */
    protected function buildWeeklyTrend(int $weeks): Collection
    {
        $start = Carbon::now()->startOfWeek()->subWeeks($weeks - 1);
        $end = Carbon::now()->endOfWeek();

        $rows = ParishAdminRegistrationRequest::query()
            ->where('created_at', '>=', $start)
            ->where('created_at', '<=', $end)
            ->get(['status', 'created_at', 'reviewed_at']);

        $buckets = [];
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = (clone $start)->addWeeks($i);
            $weekEnd = (clone $weekStart)->endOfWeek();
            $key = $weekStart->format('Y-W');

            $buckets[$key] = [
                'label'     => $weekStart->format('d/m') . '–' . $weekEnd->format('d/m'),
                'start'     => $weekStart->toDateString(),
                'submitted' => 0,
                'approved'  => 0,
                'rejected'  => 0,
            ];
        }

        foreach ($rows as $row) {
            $createdKey = $row->created_at?->copy()->startOfWeek()->format('Y-W');
            if ($createdKey && isset($buckets[$createdKey])) {
                $buckets[$createdKey]['submitted']++;
            }

            if ($row->reviewed_at && in_array($row->status, [
                ParishAdminRegistrationRequest::STATUS_APPROVED,
                ParishAdminRegistrationRequest::STATUS_REJECTED,
            ], true)) {
                $reviewedKey = $row->reviewed_at->copy()->startOfWeek()->format('Y-W');
                if ($reviewedKey && isset($buckets[$reviewedKey])) {
                    if ($row->status === ParishAdminRegistrationRequest::STATUS_APPROVED) {
                        $buckets[$reviewedKey]['approved']++;
                    } else {
                        $buckets[$reviewedKey]['rejected']++;
                    }
                }
            }
        }

        return collect(array_values($buckets));
    }

    protected function buildPendingRegistrations(): Collection
    {
        return ParishAdminRegistrationRequest::query()
            ->with(['parish:id,name', 'diocese:id,name', 'deanery:id,name'])
            ->where('status', ParishAdminRegistrationRequest::STATUS_PENDING)
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (ParishAdminRegistrationRequest $r) => [
                'id'             => $r->id,
                'reference_code' => $r->reference_code,
                'name'           => $r->name ?: $r->email,
                'email'          => $r->email,
                'parish'         => $r->parishDisplayName(),
                'roles'          => implode(', ', $r->requestedRoleLabels()) ?: '—',
                'created_at'     => $r->created_at,
                'url'            => backpack_url('parish-admin-registration/' . $r->id . '/show'),
            ]);
    }

    /**
     * @return array{backup_count: int, backup_latest_at: ?Carbon, backup_age_hours: ?int, failed_jobs: int}
     */
    protected function buildHealth(): array
    {
        $backupCount = 0;
        $backupLatest = null;

        try {
            foreach (config('backup.backup.destination.disks', ['backups']) as $diskName) {
                if (! config("filesystems.disks.{$diskName}")) {
                    continue;
                }
                $disk = Storage::disk($diskName);
                foreach ($disk->allFiles() as $file) {
                    if (substr($file, -4) !== '.zip') {
                        continue;
                    }
                    $backupCount++;
                    $mtime = Carbon::createFromTimestamp($disk->lastModified($file));
                    if (! $backupLatest || $mtime->gt($backupLatest)) {
                        $backupLatest = $mtime;
                    }
                }
            }
        } catch (\Throwable $e) {
            report($e);
        }

        $failedJobs = 0;
        if (Schema::hasTable('failed_jobs')) {
            $failedJobs = (int) DB::table('failed_jobs')->count();
        }

        return [
            'backup_count'     => $backupCount,
            'backup_latest_at' => $backupLatest,
            'backup_age_hours' => $backupLatest ? $backupLatest->diffInHours(now()) : null,
            'failed_jobs'      => $failedJobs,
        ];
    }

    /**
     * Giáo xứ active bất thường: thiếu admin, im lặng 30 ngày, không có học sinh.
     *
     * @return array{items: Collection<int, array<string, mixed>>, counts: array<string, int>}
     */
    protected function buildAttentionParishes(): array
    {
        $adminParishIds = User::query()
            ->whereNotNull('parish_id')
            ->role(['parish_admin', 'parishioner_admin', 'catechism_admin'])
            ->pluck('parish_id')
            ->unique()
            ->all();

        $lastLoginByParish = [];
        if (Schema::hasColumn('users', 'last_login_at')) {
            $lastLoginByParish = User::query()
                ->whereNotNull('parish_id')
                ->selectRaw('parish_id, MAX(last_login_at) as last_login_at')
                ->groupBy('parish_id')
                ->pluck('last_login_at', 'parish_id')
                ->all();
        }

        $silentBefore = Carbon::now()->subDays(30);
        $parishes = ParishNew::query()
            ->where('status', true)
            ->with(['diocese:id,name'])
            ->withCount(['students', 'teachers', 'users'])
            ->orderBy('name')
            ->get();

        $counts = [
            'no_admin'     => 0,
            'silent_30d'   => 0,
            'no_students'  => 0,
            'total_flagged'=> 0,
        ];

        $items = collect();
        foreach ($parishes as $parish) {
            $reasons = [];
            $hasAdmin = in_array($parish->id, $adminParishIds, true);
            if (! $hasAdmin) {
                $reasons[] = [
                    'code'  => 'no_admin',
                    'label' => 'Chưa có quản trị',
                ];
                $counts['no_admin']++;
            }

            $rawLast = $lastLoginByParish[$parish->id] ?? null;
            $lastLogin = $rawLast ? Carbon::parse($rawLast) : null;
            if (! $lastLogin || $lastLogin->lt($silentBefore)) {
                $reasons[] = [
                    'code'  => 'silent_30d',
                    'label' => $lastLogin ? 'Không login 30 ngày' : 'Chưa từng login',
                ];
                $counts['silent_30d']++;
            }

            if ((int) $parish->students_count === 0) {
                $reasons[] = [
                    'code'  => 'no_students',
                    'label' => 'Chưa có học sinh',
                ];
                $counts['no_students']++;
            }

            if ($reasons === []) {
                continue;
            }

            $counts['total_flagged']++;

            $priority = 0;
            foreach ($reasons as $reason) {
                if ($reason['code'] === 'no_admin') {
                    $priority += 100;
                } elseif ($reason['code'] === 'silent_30d') {
                    $priority += 40;
                } else {
                    $priority += 10;
                }
            }

            $items->push([
                'id'            => $parish->id,
                'name'          => $parish->name,
                'diocese'       => $parish->diocese?->name,
                'students'      => (int) $parish->students_count,
                'teachers'      => (int) $parish->teachers_count,
                'users'         => (int) $parish->users_count,
                'has_admin'     => $hasAdmin,
                'last_login_at' => $lastLogin,
                'reasons'       => $reasons,
                'priority'      => $priority,
                'url'           => backpack_url('parish-management/' . $parish->id . '/show'),
                'edit_url'      => backpack_url('parish-management/' . $parish->id . '/edit'),
                'set_admin_url' => backpack_url('set-admin'),
            ]);
        }

        return [
            'items'  => $items->sortByDesc('priority')->take(12)->values(),
            'counts' => $counts,
        ];
    }

    /**
     * @param  array<string, mixed>  $overview
     * @param  array<string, mixed>  $usage
     * @param  array<string, mixed>  $performance
     * @param  array<string, mixed>  $registrations
     * @param  array{items: Collection, counts: array<string, int>}  $attention
     * @param  array<string, mixed>  $health
     * @return Collection<int, array<string, mixed>>
     */
    protected function buildAlerts(
        array $overview,
        array $usage,
        array $performance,
        array $registrations,
        array $attention,
        array $health
    ): Collection {
        $alerts = collect();
        $counts = $attention['counts'] ?? [];

        $pending = (int) ($registrations['pending'] ?? 0);
        if ($pending > 0) {
            $alerts->push([
                'level'  => 'warning',
                'title'  => 'Đăng ký quản trị chờ duyệt',
                'detail' => "{$pending} yêu cầu cần xử lý",
                'count'  => $pending,
                'url'    => backpack_url('parish-admin-registration?status=pending'),
            ]);
        }

        $withoutAdmin = (int) ($overview['parishes_without_admin'] ?? 0);
        if ($withoutAdmin > 0) {
            $alerts->push([
                'level'  => 'warning',
                'title'  => 'Giáo xứ active thiếu quản trị',
                'detail' => "{$withoutAdmin} xứ đang hoạt động chưa có admin",
                'count'  => $withoutAdmin,
                'url'    => '#attention-parishes',
            ]);
        }

        $errorsToday = (int) ($performance['server_errors_today'] ?? 0);
        if ($errorsToday > 0) {
            $alerts->push([
                'level'  => 'danger',
                'title'  => 'Lỗi server hôm nay',
                'detail' => "{$errorsToday} response 5xx",
                'count'  => $errorsToday,
                'url'    => backpack_url('log'),
            ]);
        }

        $failedLogins = (int) ($usage['failed_logins_today'] ?? 0);
        if ($failedLogins >= 10) {
            $alerts->push([
                'level'  => 'warning',
                'title'  => 'Đăng nhập thất bại cao',
                'detail' => "{$failedLogins} lần thất bại hôm nay",
                'count'  => $failedLogins,
                'url'    => backpack_url('user'),
            ]);
        }

        $backupAge = $health['backup_age_hours'];
        if (($health['backup_count'] ?? 0) === 0) {
            $alerts->push([
                'level'  => 'danger',
                'title'  => 'Chưa có bản sao lưu',
                'detail' => 'Không tìm thấy file .zip trên disk backups',
                'count'  => 0,
                'url'    => backpack_url('backup'),
            ]);
        } elseif ($backupAge !== null && $backupAge > 48) {
            $alerts->push([
                'level'  => 'warning',
                'title'  => 'Backup đã cũ',
                'detail' => 'Bản gần nhất cách đây ' . $health['backup_latest_at']->diffForHumans(),
                'count'  => (int) $health['backup_count'],
                'url'    => backpack_url('backup'),
            ]);
        }

        $failedJobs = (int) ($health['failed_jobs'] ?? 0);
        if ($failedJobs > 0) {
            $alerts->push([
                'level'  => 'danger',
                'title'  => 'Job hàng đợi thất bại',
                'detail' => "{$failedJobs} job trong failed_jobs",
                'count'  => $failedJobs,
                'url'    => backpack_url('log'),
            ]);
        }

        $silent = (int) ($counts['silent_30d'] ?? 0);
        if ($silent > 0) {
            $alerts->push([
                'level'  => 'info',
                'title'  => 'Giáo xứ im lặng',
                'detail' => "{$silent} xứ active không có login trong 30 ngày",
                'count'  => $silent,
                'url'    => '#attention-parishes',
            ]);
        }

        return $alerts;
    }
}
