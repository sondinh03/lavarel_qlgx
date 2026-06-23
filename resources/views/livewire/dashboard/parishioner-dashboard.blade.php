@section('topbar')
<x-breadcrumb :items="[['label' => 'Trang chủ', 'url' => route('parishioners.dashboard')]]" />
@endsection

<div class="min-h-screen bg-slate-50 p-2 sm:p-4 lg:p-6" style="min-height: calc(100vh - 56px - var(--bottom-offset));">
    <div class="mx-auto max-w-7xl space-y-6">

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <x-page-header
                title="Dashboard Giáo dân"
                :description="$todayLabel"
                iconType="default"
                :statValue="number_format($stats['parishioners'])"
                statLabel="Giáo dân"
            >
                <x-slot name="actions">
                    <x-button wire:click="refresh" variant="subtle" wire:loading.attr="disabled" wire:target="refresh">
                        <x-icon name="refresh" wire:loading.class="animate-spin" wire:target="refresh" />
                        Làm mới
                    </x-button>
                </x-slot>
            </x-page-header>
        </div>

        {{-- Toast --}}
        @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3000">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
        @endif

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stats.stat-card label="Giáo dân" :value="number_format($stats['parishioners'])" subline="Đang sinh hoạt" />
            <x-stats.stat-card label="Gia đình" :value="number_format($stats['families'])" subline="Đang hoạt động" />
            <x-stats.stat-card label="Giáo họ" :value="number_format($stats['parish_groups'])" subline="Đơn vị" />
            <x-stats.stat-card label="Tên thánh" :value="number_format($stats['holy_names'])" subline="Danh mục" />

            <x-stats.stat-card label="Tân tòng" :value="number_format($stats['new_converts'])" subline="Đang sinh hoạt" />
            <x-stats.stat-card label="Qua đời" :value="number_format($stats['deceased'])" subline="Đã ghi nhận" />
            <x-stats.stat-card label="Tiện ích" :value="number_format($stats['utility_groups'])" subline="Nhóm/CLB" />
            <x-stats.stat-card
                label="Giới tính"
                :value="number_format($genderStats['male']) . ' / ' . number_format($genderStats['female'])"
                subline="Nam / Nữ"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-stats.chart-card
                title="Cơ cấu độ tuổi"
                :right="'Nam ' . number_format($genderStats['male']) . ' · Nữ ' . number_format($genderStats['female'])"
            >
                @if(count($ageGroups) > 0)
                    <div class="space-y-4">
                        @foreach($ageGroups as $g)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-semibold text-slate-700">{{ $g['label'] }}</span>
                                    <span class="text-sm font-bold text-slate-900">{{ number_format($g['count']) }}</span>
                                </div>
                                <progress
                                    value="{{ $g['count'] }}"
                                    max="{{ $g['max'] }}"
                                    class="w-full h-1.5 rounded-full overflow-hidden [&::-webkit-progress-bar]:bg-slate-100 [&::-webkit-progress-value]:bg-primary-500 [&::-moz-progress-bar]:bg-primary-500"
                                ></progress>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                    </div>
                @endif
            </x-stats.chart-card>

            <x-stats.chart-card title="Giáo dân mới" right="Gần đây">
                @if(count($recentParishioners) > 0)
                    <div class="-mx-6 -my-6 divide-y divide-slate-100">
                        @foreach($recentParishioners as $p)
                            <div class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors duration-200">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $p['name'] }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $p['gender'] }} · {{ $p['phone'] ?? '—' }}</p>
                                </div>
                                <a href="{{ $p['url'] }}"
                                   class="text-xs font-semibold text-primary-600 hover:text-primary-700 transition-colors duration-200 shrink-0">
                                    Xem →
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-sm text-slate-400">Chưa có dữ liệu</p>
                    </div>
                @endif
            </x-stats.chart-card>
        </div>

        <x-stats.chart-card title="Truy cập nhanh">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('parishioners.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                    <div class="text-sm font-semibold text-slate-800">Giáo dân</div>
                    <div class="text-xs text-slate-400 mt-0.5">Danh sách & tìm kiếm</div>
                </a>
                <a href="{{ route('families.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                    <div class="text-sm font-semibold text-slate-800">Gia đình</div>
                    <div class="text-xs text-slate-400 mt-0.5">Sổ gia đình</div>
                </a>
                <a href="{{ route('parish-group.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                    <div class="text-sm font-semibold text-slate-800">Giáo họ</div>
                    <div class="text-xs text-slate-400 mt-0.5">Quản lý giáo họ</div>
                </a>
                <a href="{{ route('holy-names.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                    <div class="text-sm font-semibold text-slate-800">Tên thánh</div>
                    <div class="text-xs text-slate-400 mt-0.5">Danh mục tên thánh</div>
                </a>
                @can('viewAny', \App\Models\ParishionerRegistrationRequest::class)
                <a href="{{ route('parishioners.registrations.index') }}" class="px-4 py-3 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 transition">
                    <div class="text-sm font-semibold text-amber-900">Duyệt đăng ký</div>
                    <div class="text-xs text-amber-700 mt-0.5">Yêu cầu từ giáo dân</div>
                </a>
                @endcan
                <a href="{{ route('parishioners.register.public') }}" target="_blank" class="px-4 py-3 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                    <div class="text-sm font-semibold text-emerald-900">Form đăng ký</div>
                    <div class="text-xs text-emerald-700 mt-0.5">Link công khai</div>
                </a>
            </div>
        </x-stats.chart-card>
    </div>
</div>

