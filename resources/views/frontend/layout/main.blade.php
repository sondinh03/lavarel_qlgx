<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('settings.web_name', 'Quản Lý Giáo Xứ'))</title>

    @includeIf('frontend.layout.meta')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles
    @stack('styles')

    {{-- Set margin-left NGAY khi parse HTML, trước khi Alpine load --}}
    <script>
        (function() {
            var mini = localStorage.getItem('sidebarMini') === 'true';
            var desktop = window.innerWidth >= 1024;

            document.documentElement.style.setProperty(
                '--sidebar-current',
                desktop ? (mini ? '64px' : '256px') : '0px'
            );

            if (!desktop) return;

            var style = document.createElement('style');

            style.id = 'sidebar-init-style';
            style.textContent = mini ?
                `
            #main-wrapper { margin-left: var(--sidebar-w-mini) !important; }
            #sidebar { width: var(--sidebar-w-mini) !important; }
        ` :
                `   
            #main-wrapper { margin-left: var(--sidebar-w) !important; }
            #sidebar { width: var(--sidebar-w) !important; }
        `;

            document.head.appendChild(style);
        })();
    </script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Sidebar width tokens */
        :root {
            --sidebar-w: 256px;
            --sidebar-w-mini: 64px;
            --sidebar-current: 0px;

            --transition-fast: 200ms ease;
        }

        /* Transition chỉ khi user toggle — class .is-animating thêm bằng JS */
        #sidebar.is-animating {
            transition: width 200ms ease;
        }

        #main-wrapper.is-animating {
            transition: margin-left 200ms ease;
        }

        /* Hide text labels when mini */
        #sidebar[data-mini="true"] .sidebar-label {
            display: none;
        }

        #sidebar[data-mini="true"] .sidebar-chevron {
            display: none;
        }

        #sidebar[data-mini="true"] .logo-text {
            display: none;
        }

        #sidebar[data-mini="true"] .user-info {
            display: none;
        }

        /* Flyout tooltip for mini mode */
        #sidebar[data-mini="true"] .has-flyout:hover .flyout-menu {
            display: block;
        }

        .flyout-menu {
            display: none;
            position: absolute;
            left: calc(var(--sidebar-w-mini) + 4px);
            top: 0;
            z-index: 999;
            min-width: 200px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .10);
            padding: 4px 0;
        }

        /* Scrollbar sidebar */
        #sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar-nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        @keyframes indeterminate {
            0% {
                transform: translateX(-100%) scaleX(0.3);
            }

            50% {
                transform: translateX(0%) scaleX(0.7);
            }

            100% {
                transform: translateX(100%) scaleX(0.3);
            }
        }
    </style>
</head>

@php
if (request()->routeIs('attendance.statistics', 'scores.statistics', 'students.statistics')) {
    $activeGroup = 'statistics';
} elseif (request()->routeIs('school-years.guide', 'help.*')) {
    $activeGroup = 'help';
} elseif (
    request()->routeIs(
        'session.*',
        'attendance.edit-logs',
        'scores.edit-logs',
        'catechists.*',
        'school-years.index',
        'school-years.copy',
        'classes.*'
    )
) {
    $activeGroup = 'system';
} elseif (
    request()->routeIs('attendance.*', 'scores.*', 'catechism.announcements')
    || (request()->routeIs('students.*') && ! request()->routeIs('students.statistics'))
) {
    $activeGroup = 'learning';
} else {
    $activeGroup = null;
}
$isDashboard = request()->routeIs('parish-admin.dashboard');
$isGroupsActive = request()->routeIs('groups.*');
@endphp

<body class="min-h-screen bg-apple-gray text-slate-800 antialiased"
    x-data="{
        sidebarMini: localStorage.getItem('sidebarMini') === 'true',
        mobileOpen: false,
        openGroups: (function() {
            const active = '{{ $activeGroup }}';

            if (active) return [active];

            return JSON.parse(localStorage.getItem('openGroups') || '[]');
        })(),

        toggleGroup(name) {
            if (this.sidebarMini) return;

            if (this.openGroups.includes(name)) {
                this.openGroups = this.openGroups.filter(g => g !== name);
            } else {
                this.openGroups.push(name);
            }

            localStorage.setItem('openGroups', JSON.stringify(this.openGroups));
        }
    }"
    :class="{ 'overflow-hidden': mobileOpen }">

    {{-- Loading Indicator --}}
    <div id="global-loading" class="hidden fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-primary-100 overflow-hidden">
            <div class="h-full bg-primary-500 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
    </div>

    <x-toast-manager />

    {{-- ═══════════════════════════════════════════
         MOBILE OVERLAY
    ═══════════════════════════════════════════ --}}
    <div x-show="mobileOpen"
        x-transition:enter="transition-opacity duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileOpen = false"
        class="fixed inset-0 bg-black/40 z-40 lg:hidden"
        x-cloak></div>

    {{-- ═══════════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════════ --}}
    <aside id="sidebar"
        :data-mini="sidebarMini ? 'true' : 'false'"
        :class="[
            sidebarMini ? 'w-16' : 'w-64',
            mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
        ]"
        style="height:100vh;height:100dvh;"
        class="fixed top-0 left-0 z-50
            bg-white border-r border-slate-200 shadow-sm
            flex flex-col
            transition-all duration-200
            -translate-x-full lg:translate-x-0">

        {{-- ── Logo ── --}}
        <div class="flex items-center gap-3 px-4 h-16 border-b border-slate-100 flex-shrink-0">
            <a href="{{ route('parish-admin.dashboard') }}" class="flex items-center gap-3 min-w-0">
                <img src="{{ url(config('settings.logo')) }}"
                    class="h-8 w-8 rounded-lg object-contain flex-shrink-0" alt="Logo">
                <span class="logo-text text-sm font-bold text-primary-700 truncate leading-tight sidebar-label">
                    {{ config('settings.web_name') }}
                </span>
            </a>

            {{-- Toggle mini button (desktop only) --}}
            <button @click="
                    const sidebar = document.getElementById('sidebar');
                    const wrapper = document.getElementById('main-wrapper');
                    sidebar.classList.add('is-animating');
                    wrapper.classList.add('is-animating');
                    setTimeout(() => {
                        sidebar.classList.remove('is-animating');
                        wrapper.classList.remove('is-animating');
                    }, 220);
                    const nextMini = !sidebarMini;
                    sidebarMini = nextMini;
                    openGroups = nextMini ? [] : (('{{ $activeGroup }}' ? ['{{ $activeGroup }}'] : []));
                    localStorage.setItem('sidebarMini', nextMini);
                    const offset = nextMini ? '64px' : '256px';
                    wrapper.style.marginLeft = offset;
                    document.documentElement.style.setProperty('--sidebar-current', offset);
                "
                class="hidden lg:flex ml-auto flex-shrink-0 w-7 h-7 items-center justify-center
                       rounded-lg text-slate-400 hover:bg-slate-100 hover:text-primary-600 transition">
                <svg class="w-4 h-4 transition-transform duration-200"
                    :class="sidebarMini ? 'rotate-180' : ''"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </div>

        {{-- ── Navigation ── --}}
        <nav id="sidebar-nav" class="flex-1 overflow-y-auto overflow-x-hidden min-h-0 py-3 px-2 space-y-0.5">

            {{-- Trang chủ --}}
            <a href="{{ route('parish-admin.dashboard') }}"
                class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                    {{ $isDashboard ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0
                    {{ $isDashboard ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="sidebar-label truncate">Trang chủ</span>

                @if($isDashboard)
                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                @endif
            </a>

            {{-- ── Nhóm: GIÁO LÝ ── --}}
            @php
                $isStatisticsActive = $activeGroup === 'statistics';
                $isLearningActive = $activeGroup === 'learning';
                $isSystemActive = $activeGroup === 'system';
                $isHelpActive = $activeGroup === 'help';
            @endphp
            <div class="relative has-flyout">
                {{-- Group header --}}
                <button @click="toggleGroup('learning')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isLearningActive ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isLearningActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Giáo lý</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroups.includes('learning') ? 'rotate-180 text-primary-600' : 'text-slate-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isLearningActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                {{-- Accordion submenu (full sidebar) --}}
                <div x-show="openGroups.includes('learning') && !sidebarMini"
                    style="{{ $isLearningActive ? '' : 'display:none' }}"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5">
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'students.index', 'label' => 'Học sinh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'attendance.show', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'scores.index', 'label' => 'Kết quả học tập'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'catechism.announcements', 'label' => 'Thông báo GLV'])
                </div>

                {{-- Flyout (mini sidebar) --}}
                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Giáo lý</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'students.index', 'label' => 'Học sinh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'attendance.show', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'scores.index', 'label' => 'Kết quả học tập'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'catechism.announcements', 'label' => 'Thông báo GLV'])
                </div>
            </div>

            {{-- ── Nhóm: THỐNG KÊ ── --}}
            <div class="relative has-flyout">
                <button @click="toggleGroup('statistics')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isStatisticsActive ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isStatisticsActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Thống kê</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroups.includes('statistics') ? 'rotate-180 text-primary-600' : 'text-slate-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isStatisticsActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                <div x-show="openGroups.includes('statistics') && !sidebarMini"
                    style="{{ $isStatisticsActive ? '' : 'display:none' }}"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5">
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'students.statistics', 'label' => 'Học sinh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'attendance.statistics', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'scores.statistics', 'label' => 'Điểm số'])
                </div>

                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Thống kê</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'students.statistics', 'label' => 'Học sinh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'attendance.statistics', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'scores.statistics', 'label' => 'Điểm số'])
                </div>
            </div>

            {{-- Quản lý nhóm (link thẳng, không nhóm 1 mục) --}}
            <a href="{{ route('groups.index') }}"
                class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                    {{ $isGroupsActive ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <svg class="w-5 h-5 flex-shrink-0
                    {{ $isGroupsActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span class="sidebar-label truncate">Quản lý nhóm</span>
                @if($isGroupsActive)
                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                @endif
            </a>

            {{-- ── Nhóm: HỆ THỐNG ── --}}
            <div class="relative has-flyout">
                <button @click="toggleGroup('system')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isSystemActive ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isSystemActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Hệ thống</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroups.includes('system') ? 'rotate-180 text-primary-600' : 'text-slate-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isSystemActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                <div x-show="openGroups.includes('system') && !sidebarMini"
                    style="{{ $isSystemActive ? '' : 'display:none' }}"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5">
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'school-years.index', 'label' => 'Năm học'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'classes.index', 'label' => 'Lớp học'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'catechists.index', 'label' => 'Giáo lý viên'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'session.index', 'label' => 'Phiên điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'attendance.edit-logs', 'label' => 'Nhật ký điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'scores.edit-logs', 'label' => 'Nhật ký sửa điểm'])
                </div>

                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Hệ thống</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'school-years.index', 'label' => 'Năm học'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'classes.index', 'label' => 'Lớp học'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'catechists.index', 'label' => 'Giáo lý viên'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'session.index', 'label' => 'Phiên điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'attendance.edit-logs', 'label' => 'Nhật ký điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'scores.edit-logs', 'label' => 'Nhật ký sửa điểm'])
                </div>
            </div>

            {{-- ── Nhóm: TRỢ GIÚP ── --}}
            <div class="relative has-flyout">
                <button @click="toggleGroup('help')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isHelpActive ? 'text-primary-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isHelpActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Trợ giúp</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroups.includes('help') ? 'rotate-180 text-primary-600' : 'text-slate-400'"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isHelpActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                <div x-show="openGroups.includes('help') && !sidebarMini"
                    style="{{ $isHelpActive ? '' : 'display:none' }}"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5">
                    @include('frontend.layout.partials.sidebar-sub-item', [
                        'route' => 'school-years.guide',
                        'label' => 'Cấu hình năm học mới',
                    ])
                    @include('frontend.layout.partials.sidebar-sub-item', [
                        'route' => 'help.install-app',
                        'label' => 'Cài đặt lên điện thoại',
                    ])
                </div>

                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Trợ giúp</div>
                    @include('frontend.layout.partials.flyout-item', [
                        'route' => 'school-years.guide',
                        'label' => 'Cấu hình năm học mới',
                    ])
                    @include('frontend.layout.partials.flyout-item', [
                        'route' => 'help.install-app',
                        'label' => 'Cài đặt lên điện thoại',
                    ])
                </div>
            </div>

            {{-- Chuyển sang module Giáo dân --}}
            @if(auth()->user()?->canManageParishioners())
            <div class="pt-2 mt-2 border-t border-slate-100">
                <a href="{{ route('parishioners.dashboard') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                           text-slate-500 hover:bg-slate-50 hover:text-slate-900">
                    <svg class="w-5 h-5 flex-shrink-0 text-slate-400 group-hover:text-slate-600"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="sidebar-label truncate">Sang module Giáo dân</span>
                </a>
            </div>
            @endif
        </nav>

        {{-- ── User info (bottom) ── --}}
        @auth
        <div class="flex-shrink-0 border-t border-slate-100 p-3">
            <div class="relative has-flyout" x-data="{ open: false }">
                <button @click="if(!sidebarMini) open = !open" @click.outside="open = false"
                    class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition group">
                    <x-user-avatar :user="Auth::user()" size="sm" />
                    <div class="user-info sidebar-label flex-1 min-w-0 text-left">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <svg class="sidebar-chevron w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" />
                    </svg>
                </button>

                {{-- Popup user menu (full mode) --}}
                <div x-show="open && !sidebarMini"
                    x-transition
                    @click.outside="open = false"
                    class="absolute bottom-full left-0 right-0 mb-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden z-50"
                    x-cloak>
                    <a href="{{ route('account.settings') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        Tài khoản
                    </a>
                    @if(Auth::user()->isParishAdmin())
                    <a href="{{ route('parish.settings') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        Thông tin giáo xứ
                    </a>
                    @endif
                    <a href="{{ route('module.select') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        Chọn phân hệ
                    </a>
                    <div class="border-t border-slate-100"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50">
                            Đăng xuất
                        </button>
                    </form>
                </div>

                {{-- Flyout user menu (mini mode) --}}
                <div class="flyout-menu bottom-0" style="top:auto;bottom:0;">
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">
                        {{ Auth::user()->name }}
                    </div>
                    <a href="{{ route('account.settings') }}"
                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Tài khoản
                    </a>
                    @if(Auth::user()->isParishAdmin())
                    <a href="{{ route('parish.settings') }}"
                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Thông tin giáo xứ
                    </a>
                    @endif
                    <a href="{{ route('parish-admin.dashboard') }}"
                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Dashboard Giáo lý
                    </a>
                    <div class="border-t border-slate-100 my-1"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                            Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endauth
    </aside>

    {{-- ═══════════════════════════════════════════
         MAIN WRAPPER
    ═══════════════════════════════════════════ --}}
    <div id="main-wrapper"
        class="min-h-screen flex flex-col"
        x-init="
            localStorage.setItem('openGroups', JSON.stringify(openGroups));

            $watch('openGroups', value => {
                localStorage.setItem('openGroups', JSON.stringify(value));
            });

            if (window.innerWidth >= 1024) {
                const offset = sidebarMini ? '64px' : '256px';
                $el.style.marginLeft = offset;
                document.documentElement.style.setProperty('--sidebar-current', offset);
            } else {
                document.documentElement.style.setProperty('--sidebar-current', '0px');
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    const offset = sidebarMini ? '64px' : '256px';
                    $el.style.marginLeft = offset;
                    document.documentElement.style.setProperty('--sidebar-current', offset);
                } else {
                    $el.style.marginLeft = '';
                    document.documentElement.style.setProperty('--sidebar-current', '0px');
                }
            });

            // Xoá style inject từ head script sau khi Alpine đã handle
            var s = document.getElementById('sidebar-init-style');
            if (s) s.remove();
        ">

        {{-- ── Topbar ── --}}
        <header class="sticky top-0 z-30 bg-white/70 backdrop-blur-xl border-b border-black/[0.06] shadow-mac-sm">
            <div class="h-14 flex items-center gap-3 px-4 sm:px-6">

                {{-- Mobile hamburger --}}
                <button @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Breadcrumb slot hoặc page title --}}
                <div class="flex-1 min-w-0">
                    @hasSection('topbar')
                    @yield('topbar')
                    @else
                    <h1 class="text-sm font-semibold text-slate-700 truncate">
                        @yield('title', config('settings.web_name'))
                    </h1>
                    @endif
                </div>

                {{-- Right actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @auth
                    @livewire('notifications.notification-bell')
                    @endauth

                    {{-- Fullscreen --}}
                    <button id="fullscreen-button"
                        class="hidden lg:flex p-2 rounded-lg text-slate-500
                               hover:bg-slate-100 hover:text-primary-600 transition"
                        aria-label="Toàn màn hình">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        {{-- ── Page content ── --}}
        <main id="main-content" class="flex-1">
            @yield('content')
        </main>

        @include('frontend.layout.footer')
    </div>

    {{-- Back to top --}}
    <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-6 right-6 z-40 w-10 h-10
               bg-primary-600 hover:bg-primary-700
               text-white rounded-full shadow-lg
               flex items-center justify-center
               transition-all hover:scale-110 active:scale-95
               focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
        aria-label="Lên đầu trang">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    <x-confirm-dialog />

    {{-- Scripts --}}
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>

    <script>
        document.getElementById('fullscreen-button')?.addEventListener('click', () => {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                document.exitFullscreen();
            }
        });
    </script>

    <script>
        // Optimistic active highlight: tô màu ngay khi click, trước khi server response
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#sidebar-nav a[href]').forEach(function(link) {
                link.addEventListener('click', function() {
                    // Xoá active cũ
                    document.querySelectorAll('#sidebar-nav a[href]').forEach(function(el) {
                        el.classList.remove('bg-primary-50', 'text-primary-700');
                        el.classList.add('text-slate-500');
                        var dot = el.querySelector('span.bg-primary-500');
                        if (dot) dot.classList.replace('bg-primary-500', 'bg-slate-300');
                    });
                    // Set active mới ngay lập tức
                    link.classList.add('bg-primary-50', 'text-primary-700');
                    link.classList.remove('text-slate-500');
                    var dot = link.querySelector('span.bg-slate-300');
                    if (dot) dot.classList.replace('bg-slate-300', 'bg-primary-500');
                });
            });
        });
    </script>

    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('toast', (type, message) => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: [type, message]
                }));
            });
        });
    </script>

    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.hook('message.sent', () => {
                document.getElementById('global-loading').classList.remove('hidden');
            });
            Livewire.hook('message.processed', () => {
                document.getElementById('global-loading').classList.add('hidden');
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
    @include('frontend.layout.partials.livewire-alpine-bridge')

    @stack('scripts')
</body>

</html>