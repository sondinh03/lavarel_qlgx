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
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased flex flex-col"
      x-data="{ open: false }">

    {{-- Loading indicator --}}
    <x-loading-indicator target="selectedNamHoc,selectedKhoi,resetFilters"/>

    {{-- ═══════════════════════════════════════════
         ADMIN HEADER
    ═══════════════════════════════════════════ --}}
    <header class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm border-b border-slate-200 shadow-sm">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="h-16 flex items-center justify-between gap-4">

                {{-- Logo --}}
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 flex-shrink-0">
                    <img src="{{ url(config('settings.logo')) }}" class="h-9 w-auto" alt="Logo">
                    <span class="hidden sm:block text-base font-semibold text-primary-700 leading-tight">
                        {{ config('settings.web_name') }}
                    </span>
                </a>

                {{-- Desktop Nav --}}
                <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">

                    <a href="{{ route('dashboard') }}"
                       class="nav-item {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Trang chủ
                    </a>

                    {{-- Dropdown: Học Sinh --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="nav-item {{ request()->routeIs('students.*', 'attendance.*', 'classes.*', 'scores.*', 'session.*') ? 'nav-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            Học Sinh
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': open}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="dropdown-menu">
                            <x-nav-dropdown-item route="students.index"   label="Quản lý học sinh"        icon="users"/>
                            <x-nav-dropdown-item route="classes.index"    label="Danh sách lớp"           icon="folder"/>
                            <x-nav-dropdown-item route="attendance.show"  label="Điểm danh"               icon="clipboard"/>
                            <x-nav-dropdown-item route="session.index"    label="Phiên điểm danh"         icon="clock"/>
                            <x-nav-dropdown-item route="scores.index"     label="Kết quả học tập"         icon="chart"/>
                        </div>
                    </div>

                    {{-- Dropdown: Nhân Sự --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="nav-item {{ request()->routeIs('catechists.*', 'parishioners.*') ? 'nav-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Nhân Sự
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': open}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="dropdown-menu">
                            <x-nav-dropdown-item route="catechists.index"  label="Giáo lý viên"       icon="user"/>
                            <x-nav-dropdown-item route="parishioners.index" label="Giáo dân"          icon="user"/>
                            <x-nav-dropdown-item route="catechists.import"  label="Import GLV"        icon="upload"/>
                        </div>
                    </div>

                    {{-- Dropdown: Hệ Thống --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="nav-item {{ request()->routeIs('school-years.*', 'parish-group.*', 'holy-names.*') ? 'nav-active' : '' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Hệ Thống
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="{'rotate-180': open}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             class="dropdown-menu">
                            <x-nav-dropdown-item route="school-years.index"  label="Năm học"       icon="calendar"/>
                            <x-nav-dropdown-item route="classes.index"        label="Lớp học"       icon="folder"/>
                            <div class="my-1 border-t border-slate-100"></div>
                            <x-nav-dropdown-item route="parish-group.index"   label="Giáo họ"       icon="building"/>
                            <x-nav-dropdown-item route="holy-names.index"     label="Tên thánh"     icon="star"/>
                            <x-nav-dropdown-item route="parishioners.index"   label="Giáo dân"      icon="users"/>
                        </div>
                    </div>
                </nav>

                {{-- Right: User + actions --}}
                <div class="flex items-center gap-2">
                    {{-- Fullscreen --}}
                    <button id="fullscreen-button"
                            class="hidden lg:flex p-2 rounded-lg text-slate-500
                                   hover:bg-slate-100 hover:text-primary-600 transition"
                            aria-label="Toàn màn hình">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>

                    {{-- User dropdown --}}
                    @auth
                    <div class="relative hidden lg:block" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-xl
                                       text-sm font-medium text-slate-700
                                       hover:bg-slate-100 transition">
                            <div class="w-7 h-7 rounded-full bg-primary-100 text-primary-700
                                        flex items-center justify-center text-xs font-bold">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <span class="max-w-[120px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-400" :class="{'rotate-180': open}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition
                             class="absolute right-0 mt-2 w-48 bg-white border border-slate-200
                                    rounded-xl shadow-lg overflow-hidden z-50">
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
                    </div>
                    @endauth

                    {{-- Mobile hamburger --}}
                    <button @click="open = !open"
                            class="lg:hidden p-2 rounded-lg hover:bg-slate-100 text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile drawer --}}
        <div x-show="open" x-transition @click.outside="open = false"
             class="lg:hidden border-t border-slate-100 bg-white">
            <nav class="px-4 py-3 space-y-1">
                <a href="{{ route('dashboard') }}"
                   class="block px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Trang chủ
                </a>
                <a href="{{ route('students.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Học sinh
                </a>
                <a href="{{ route('attendance.show') }}"
                   class="block px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Điểm danh
                </a>
                <a href="{{ route('school-years.index') }}"
                   class="block px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-primary-50 hover:text-primary-700">
                    Hệ thống
                </a>
                @auth
                <div class="pt-2 border-t border-slate-100">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-left px-3 py-2 rounded-lg text-sm text-red-600 hover:bg-red-50">
                            Đăng xuất
                        </button>
                    </form>
                </div>
                @endauth
            </nav>
        </div>
    </header>

    {{-- ═══════════════════════════════════════════
         MAIN CONTENT
         Không bọc thêm bg-white — component tự lo
    ═══════════════════════════════════════════ --}}
    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    {{-- Back to top --}}
    <button onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="fixed bottom-6 right-6 z-40 w-11 h-11
                   bg-primary-600 hover:bg-primary-700
                   text-white rounded-full shadow-lg
                   flex items-center justify-center
                   transition-all hover:scale-110 active:scale-95
                   focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            aria-label="Lên đầu trang">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
        </svg>
    </button>

    @include('frontend.layout.footer')

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
    {{-- Alpine PHẢI load sau @stack('scripts') --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
</body>

</html>

{{-- ═══════════════════════════════════════════
     CSS UTILITIES — đặt trong app.css hoặc @push('styles')
     Copy vào resources/css/components/nav.css
═══════════════════════════════════════════
.nav-item {
    @apply flex items-center gap-1.5 px-3 py-2 rounded-xl
           text-sm font-medium text-slate-600
           hover:bg-primary-50 hover:text-primary-700
           transition-all whitespace-nowrap;
}
.nav-active {
    @apply bg-primary-50 text-primary-700 font-semibold;
}
.dropdown-menu {
    @apply absolute left-0 mt-2 w-56 bg-white border border-slate-200
           rounded-2xl shadow-xl overflow-hidden z-50 py-1;
}
═══════════════════════════════════════════ --}}