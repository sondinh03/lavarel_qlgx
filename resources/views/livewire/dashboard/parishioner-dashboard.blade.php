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
                    <a href="{{ route('parishioners.statistics') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Thống kê
                    </a>
                    <x-button wire:click="refresh" variant="subtle" wire:loading.attr="disabled" wire:target="refresh">
                        <x-icon name="refresh" wire:loading.class="animate-spin" wire:target="refresh" />
                        Làm mới
                    </x-button>
                </x-slot>
            </x-page-header>
        </div>

        @if(session()->has('message'))
            <x-toast-notification type="success" :duration="3000">{{ session('message') }}</x-toast-notification>
        @endif
        @if(session()->has('error'))
            <x-toast-notification type="error" :duration="4000">{{ session('error') }}</x-toast-notification>
        @endif

        @can('viewAny', \App\Models\ParishionerRegistrationRequest::class)
        @if(($stats['pending_registrations'] ?? 0) > 0)
        <a href="{{ route('parishioners.registrations.index') }}"
            class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 hover:bg-amber-100 transition">
            <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-900">
                    {{ number_format($stats['pending_registrations']) }} yêu cầu đăng ký đang chờ duyệt
                </p>
                <p class="text-xs text-amber-700 mt-0.5">Nhấn để xem và xử lý</p>
            </div>
        </a>
        @endif
        @endcan

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-stats.stat-card label="Giáo dân" :value="number_format($stats['parishioners'])" subline="Đang sinh hoạt" />
            <x-stats.stat-card label="Gia đình" :value="number_format($stats['families'])" subline="Đang hoạt động" />
            <x-stats.stat-card label="Giáo họ" :value="number_format($stats['parish_groups'])" subline="Đơn vị" />
            <x-stats.stat-card
                label="Giới tính"
                :value="number_format($genderStats['male']) . ' / ' . number_format($genderStats['female'])"
                subline="Nam / Nữ"
            />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-stats.chart-card title="Tóm tắt nhanh">
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-xs text-slate-400">Tân tòng</p>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">{{ number_format($stats['new_converts']) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-xs text-slate-400">Qua đời</p>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">{{ number_format($stats['deceased']) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-xs text-slate-400">Tên thánh (danh mục)</p>
                        <p class="text-lg font-bold text-slate-900 mt-0.5">{{ number_format($stats['holy_names']) }}</p>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-100 p-3">
                        <p class="text-xs text-slate-400">Tỷ lệ nam</p>
                        <p class="text-lg font-bold text-primary-600 mt-0.5">{{ $genderStats['male_rate'] ?? 0 }}%</p>
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-4">
                    Xem biểu đồ chi tiết theo độ tuổi, giáo họ và hội đoàn tại
                    <a href="{{ route('parishioners.statistics') }}" class="text-primary-600 font-semibold hover:text-primary-700">trang Thống kê giáo dân</a>.
                </p>
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
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Giáo dân</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <a href="{{ route('parishioners.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Danh sách giáo dân</div>
                            <div class="text-xs text-slate-400 mt-0.5">Tìm kiếm & quản lý</div>
                        </a>
                        <a href="{{ route('parishioners.statistics') }}" class="px-4 py-3 rounded-2xl bg-primary-50 hover:bg-primary-100 border border-primary-200 transition">
                            <div class="text-sm font-semibold text-primary-900">Thống kê</div>
                            <div class="text-xs text-primary-700 mt-0.5">Biểu đồ & phân tích</div>
                        </a>
                        @can('viewAny', \App\Models\ParishionerRegistrationRequest::class)
                        <a href="{{ route('parishioners.registrations.index') }}" class="px-4 py-3 rounded-2xl bg-amber-50 hover:bg-amber-100 border border-amber-200 transition">
                            <div class="text-sm font-semibold text-amber-900">Duyệt đăng ký</div>
                            <div class="text-xs text-amber-700 mt-0.5">Yêu cầu từ giáo dân</div>
                        </a>
                        @endcan
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Gia đình</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <a href="{{ route('families.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Gia đình</div>
                            <div class="text-xs text-slate-400 mt-0.5">Sổ gia đình</div>
                        </a>
                        <a href="{{ route('marriage-announcements.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Rao hôn phối</div>
                            <div class="text-xs text-slate-400 mt-0.5">Thông báo hôn phối</div>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Danh mục</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <a href="{{ route('parish-group.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Giáo họ</div>
                            <div class="text-xs text-slate-400 mt-0.5">Quản lý giáo họ</div>
                        </a>
                        <a href="{{ route('associations.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Hội đoàn</div>
                            <div class="text-xs text-slate-400 mt-0.5">Quản lý hội đoàn</div>
                        </a>
                        <a href="{{ route('holy-names.index') }}" class="px-4 py-3 rounded-2xl bg-slate-50 hover:bg-slate-100 border border-slate-200 transition">
                            <div class="text-sm font-semibold text-slate-800">Tên thánh</div>
                            <div class="text-xs text-slate-400 mt-0.5">Danh mục tên thánh</div>
                        </a>
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Công khai</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <a href="{{ route('parishioners.register.public') }}" target="_blank" class="px-4 py-3 rounded-2xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition">
                            <div class="text-sm font-semibold text-emerald-900">Form đăng ký</div>
                            <div class="text-xs text-emerald-700 mt-0.5">Link công khai</div>
                        </a>
                    </div>
                </div>
            </div>
        </x-stats.chart-card>
    </div>
</div>
