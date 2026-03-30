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

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Sidebar width tokens */
        :root {
            --sidebar-w: 256px;
            --sidebar-w-mini: 64px;
        }

        /* Smooth sidebar transition */
        #sidebar {
            transition: width 200ms ease;
        }

        /* Main content offset */
        #main-wrapper {
            margin-left: var(--sidebar-current);
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
    </style>
</head>

@php
$activeGroup = null;

if (request()->routeIs('students.*','attendance.*','classes.*','scores.*','session.*')) {
$activeGroup = 'students';
} elseif (request()->routeIs('catechists.*','parishioners.*')) {
$activeGroup = 'staff';
} elseif (request()->routeIs('school-years.*','parish-group.*','holy-names.*')) {
$activeGroup = 'system';
}
@endphp

@php
$isDashboard = request()->routeIs(
'dashboard',
'catechist.dashboard',
'parish-admin.dashboard'
);
@endphp

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased"
    x-data="{
        sidebarMini: false,
        mobileOpen: false,
        openGroup: '{{ $activeGroup }}',   
        toggleGroup(name) {
            if (this.sidebarMini) return;
            this.openGroup = this.openGroup === name ? null : name;
        }
    }"
    :class="{ 'overflow-hidden': mobileOpen }">

    {{-- Loading indicator --}}
    <x-loading-indicator target="selectedNamHoc,selectedKhoi,resetFilters" />

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
        class="fixed top-0 left-0 z-50 bg-white border-r border-slate-200 shadow-sm
               flex flex-col transition-all duration-200
               -translate-x-full lg:translate-x-0">

        {{-- ── Logo ── --}}
        <div class="flex items-center gap-3 px-4 h-16 border-b border-slate-100 flex-shrink-0">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 min-w-0">
                <img src="{{ url(config('settings.logo')) }}"
                    class="h-8 w-8 rounded-lg object-contain flex-shrink-0" alt="Logo">
                <span class="logo-text text-sm font-bold text-primary-700 truncate leading-tight sidebar-label">
                    {{ config('settings.web_name') }}
                </span>
            </a>

            {{-- Toggle mini button (desktop only) --}}
            <button @click="sidebarMini = !sidebarMini; openGroup = null"
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
            <a href="{{ route('dashboard') }}"
                class="relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                    {{ $isDashboard ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
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

            {{-- ── Nhóm: HỌC SINH ── --}}
            @php $isStudentActive = request()->routeIs('students.*','attendance.*','classes.*','scores.*','session.*'); @endphp
            <div class="relative has-flyout">
                {{-- Group header --}}
                <button @click="toggleGroup('students')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isStudentActive ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isStudentActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Học Sinh</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroup === 'students' ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isStudentActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                {{-- Accordion submenu (full sidebar) --}}
                <div x-show="openGroup === 'students' && !sidebarMini"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5"
                    x-cloak>
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'students.index', 'label' => 'Quản lý học sinh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'classes.index', 'label' => 'Danh sách lớp'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'attendance.show', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'session.index', 'label' => 'Phiên điểm danh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'scores.index', 'label' => 'Kết quả học tập'])
                </div>

                {{-- Flyout (mini sidebar) --}}
                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Học Sinh</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'students.index', 'label' => 'Quản lý học sinh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'classes.index', 'label' => 'Danh sách lớp'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'attendance.show', 'label' => 'Điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'session.index', 'label' => 'Phiên điểm danh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'scores.index', 'label' => 'Kết quả học tập'])
                </div>
            </div>

            {{-- ── Nhóm: NHÂN SỰ ── --}}
            @php $isStaffActive = request()->routeIs('catechists.*','parishioners.*'); @endphp
            <div class="relative has-flyout">
                <button @click="toggleGroup('staff')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isStaffActive ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isStaffActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Nhân Sự</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroup === 'staff' ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isStaffActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                <div x-show="openGroup === 'staff' && !sidebarMini"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5"
                    x-cloak>
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'catechists.index', 'label' => 'Giáo lý viên'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'parishioners.index','label' => 'Giáo dân'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'catechists.import', 'label' => 'Import GLV'])
                </div>

                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Nhân Sự</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'catechists.index', 'label' => 'Giáo lý viên'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'parishioners.index','label' => 'Giáo dân'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'catechists.import', 'label' => 'Import GLV'])
                </div>
            </div>

            {{-- ── Nhóm: HỆ THỐNG ── --}}
            @php $isSystemActive = request()->routeIs('school-years.*','parish-group.*','holy-names.*'); @endphp
            <div class="relative has-flyout">
                <button @click="toggleGroup('system')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition group
                        {{ $isSystemActive ? 'bg-primary-50 text-primary-700' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    <svg class="w-5 h-5 flex-shrink-0
                        {{ $isSystemActive ? 'text-primary-600' : 'text-slate-400 group-hover:text-slate-600' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="sidebar-label flex-1 text-left truncate">Hệ Thống</span>
                    <svg class="sidebar-chevron w-3.5 h-3.5 flex-shrink-0 text-slate-400 transition-transform duration-200"
                        :class="openGroup === 'system' ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    @if($isSystemActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-6 bg-primary-500 rounded-r-full"></span>
                    @endif
                </button>

                <div x-show="openGroup === 'system' && !sidebarMini"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mt-0.5 ml-4 pl-3 border-l border-slate-100 space-y-0.5"
                    x-cloak>
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'school-years.index', 'label' => 'Năm học'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'classes.index', 'label' => 'Lớp học'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'parish-group.index', 'label' => 'Giáo họ'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'holy-names.index', 'label' => 'Tên thánh'])
                    @include('frontend.layout.partials.sidebar-sub-item', ['route' => 'parishioners.index', 'label' => 'Giáo dân'])
                </div>

                <div class="flyout-menu" x-cloak>
                    <div class="px-3 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wide">Hệ Thống</div>
                    @include('frontend.layout.partials.flyout-item', ['route' => 'school-years.index', 'label' => 'Năm học'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'classes.index', 'label' => 'Lớp học'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'parish-group.index', 'label' => 'Giáo họ'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'holy-names.index', 'label' => 'Tên thánh'])
                    @include('frontend.layout.partials.flyout-item', ['route' => 'parishioners.index', 'label' => 'Giáo dân'])
                </div>
            </div>

        </nav>

        {{-- ── User info (bottom) ── --}}
        @auth
        <div class="flex-shrink-0 border-t border-slate-100 p-3">
            <div class="relative has-flyout" x-data="{ open: false }">
                <button @click="if(!sidebarMini) open = !open" @click.outside="open = false"
                    class="w-full flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-slate-50 transition group">
                    <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700
                                flex items-center justify-center text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
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
                    <a href="{{ route('dashboard') }}"
                        class="block px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50">
                        Trang quản trị
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
                    <a href="{{ route('dashboard') }}"
                        class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                        Trang quản trị
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
        :style="sidebarMini 
        ? '--sidebar-current: var(--sidebar-w-mini)' 
        : '--sidebar-current: var(--sidebar-w)'"
        class="min-h-screen flex flex-col">

        {{-- ── Topbar ── --}}
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-sm border-b border-slate-200 shadow-sm">
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
            <div class="max-w-7xl mx-auto p-4 sm:p-6">
                @yield('content')
            </div>
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

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
</body>

</html>