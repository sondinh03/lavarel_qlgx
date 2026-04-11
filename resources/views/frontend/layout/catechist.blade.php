<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="theme-color" content="#4f46e5">
    <title>@yield('title', config('settings.web_name', 'Quản Lý Giáo Xứ'))</title>

    @includeIf('frontend.layout.meta')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    @livewireStyles
    @stack('styles')

    <style>
        :root {
            --bottom-nav-height: 64px;
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --bottom-offset: calc(var(--bottom-nav-height) + var(--safe-bottom));
        }

        @media screen and (-webkit-min-device-pixel-ratio: 0) {

            select,
            textarea,
            input[type="text"],
            input[type="password"],
            input[type="datetime"],
            input[type="datetime-local"],
            input[type="date"],
            input[type="month"],
            input[type="time"],
            input[type="week"],
            input[type="number"],
            input[type="email"],
            input[type="url"] {
                font-size: 16px;
            }
        }

        body {
            padding-bottom: var(--bottom-offset);
        }

        .bottom-nav {
            padding-bottom: var(--safe-bottom);
            height: calc(var(--bottom-nav-height) + var(--safe-bottom));
        }

        .sticky-action-bar {
            bottom: var(--bottom-offset);
        }

        .touch-feedback:active {
            transform: scale(0.97);
            opacity: 0.8;
        }

        [x-cloak] {
            display: none !important;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
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

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased"
    x-data="{ showMenu: false }">

    {{-- Toast --}}
    <x-toast-manager />

    {{-- Loading indicator --}}
    <div id="global-loading" class="hidden fixed top-0 left-0 right-0 z-[9999] pointer-events-none">
        <div class="h-0.5 bg-primary-100 overflow-hidden">
            <div class="h-full bg-primary-500 animate-[indeterminate_1.4s_ease-in-out_infinite]"></div>
        </div>
    </div>

    <header id="main-header"
        class="sticky top-0 z-40 bg-primary-50 shadow-sm rounded-b-xl transition-all duration-200">
        <div class="flex items-center justify-between px-4 h-14">

            {{-- Left: logo + page title --}}
            <div class="flex items-center gap-2.5">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ url(config('settings.logo')) }}" class="h-7 w-auto" alt="Logo">
                </a>
                <span id="header-collapsed-title"
                    class="text-primary-800 font-semibold text-sm opacity-0 transition-opacity duration-300">
                    @stack('page-title')
                </span>
            </div>

            {{-- Right: bell + avatar --}}
            <div class="flex items-center gap-1">
                <button class="relative p-2 rounded-full text-slate-500
                           hover:bg-slate-100 active:bg-slate-200 transition touch-feedback"
                    aria-label="Thông báo">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                </button>
                <button @click="showMenu = !showMenu"
                    class="w-8 h-8 rounded-full bg-primary-600 border border-primary-700
                       flex items-center justify-center text-white text-sm font-bold
                       hover:bg-primary-700 active:bg-primary-800 transition touch-feedback"
                    aria-label="Menu">
                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                </button>
            </div>
        </div>
    </header>

    {{-- DRAWER MENU --}}
    <div x-cloak
        x-show="showMenu"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="showMenu = false"
        class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm">

        <div @click.stop
            x-show="showMenu"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute right-0 top-0 bottom-0 w-72 max-w-[85vw] bg-white shadow-2xl flex flex-col">

            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-6 py-5 flex-shrink-0">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-white">Menu</h2>
                    <button @click="showMenu = false"
                        class="p-1.5 rounded-full text-white/90 hover:bg-white/20 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full bg-white/20 flex items-center justify-center
                                text-white font-bold text-lg flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-white truncate">{{ Auth::user()->name ?? '' }}</p>
                        <p class="text-xs text-primary-100">Giáo lý viên</p>
                    </div>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
                @php
                $navItems = [
                ['route' => 'dashboard', 'label' => 'Trang chủ', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'students.index', 'label' => 'Học sinh lớp tôi', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'attendance.qr', 'label' => 'Quét QR', 'icon' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z'],
                ['route' => 'attendance.show', 'label' => 'Điểm danh', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
                ['route' => 'session.index', 'label' => 'Lịch sử điểm danh', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ];
                @endphp

                @foreach($navItems as $item)
                @php
                try { $url = route($item['route']); } catch (\Exception $e) { $url = '#'; }
                $isActive = request()->routeIs($item['route'])
                || request()->routeIs(rtrim($item['route'], '.index') . '.*');
                @endphp
                <a href="{{ $url }}"
                    class="flex items-center gap-3 px-3 py-3 rounded-xl transition-all touch-feedback
                          {{ $isActive ? 'bg-primary-50 text-primary-700 font-semibold' : 'text-slate-700 hover:bg-slate-100' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
                @endforeach
            </nav>

            <div class="flex-shrink-0 p-3 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center gap-3 px-3 py-3 rounded-xl
                               text-red-600 hover:bg-red-50 transition-all touch-feedback">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-medium">Đăng xuất</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- MAIN CONTENT --}}
    <main id="main-content" class="flex-1">
        @yield('content')
    </main>

    {{-- BOTTOM NAVIGATION --}}
    <nav class="bottom-nav fixed bottom-0 left-0 right-0 z-30
                bg-white/95 backdrop-blur-sm border-t border-slate-200 shadow-lg">
        <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr));"
            class="gap-0 px-1 pt-1 pb-2 items-end">
            @php
            $tabs = [
            ['route' => 'dashboard', 'label' => 'Trang chủ', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'students.index', 'label' => 'Học sinh', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => 'attendance.qr', 'label' => 'Quét QR', 'icon' => null],
            ['route' => 'attendance.show', 'label' => 'Điểm danh', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['route' => 'session.index', 'label' => 'Lịch sử', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ];
            @endphp

            @foreach($tabs as $tab)
            @php
            try { $url = route($tab['route']); } catch (\Exception $e) { $url = '#'; }
            $isActive = request()->routeIs($tab['route'])
            || request()->routeIs(rtrim($tab['route'], '.index') . '.*');
            @endphp

            @if($tab['icon'] === null)
            <div class="flex flex-col items-center -mt-5 pb-1.5">
                <a href="{{ $url }}"
                    class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center
                           touch-feedback transition-transform active:scale-95
                           {{ $isActive ? 'bg-primary-700 ring-4 ring-white' : 'bg-primary-600 hover:bg-primary-700' }}">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                </a>
                <span class="text-[10px] font-semibold mt-1 leading-none
                             {{ $isActive ? 'text-primary-600' : 'text-slate-400' }}">
                    {{ $tab['label'] }}
                </span>
            </div>

            @else
            <a href="{{ $url }}"
                class="flex flex-col items-center gap-0.5 py-1 pb-1.5 rounded-lg transition touch-feedback
                       {{ $isActive ? 'text-primary-600' : 'text-slate-400 hover:text-slate-600' }}">
                <div class="relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $tab['icon'] }}" />
                    </svg>
                    @if($isActive)
                    <span class="absolute -top-0.5 -right-0.5 w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                    @endif
                </div>
                <span class="text-[10px] font-medium leading-none {{ $isActive ? 'font-semibold' : '' }}">
                    {{ $tab['label'] }}
                </span>
            </a>
            @endif

            @endforeach
        </div>
    </nav>

    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    @stack('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        (function() {
            const header = document.getElementById('main-header');
            if (!header) return;

            function onScroll() {
                if (window.scrollY > 10) {
                    header.classList.add('border-b', 'border-primary-200', 'shadow-md');
                    header.classList.remove('shadow-sm');
                } else {
                    header.classList.remove('border-b', 'border-primary-200', 'shadow-md');
                    header.classList.add('shadow-sm');
                }
            }

            window.addEventListener('scroll', onScroll, {
                passive: true
            });
            onScroll(); // check ngay khi load
        })();
    </script>

    <script>
        document.addEventListener('livewire:load', () => {
            Livewire.on('toast', (type, message) => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: [type, message]
                }));
            });

            Livewire.hook('message.sent', () => {
                document.getElementById('global-loading')?.classList.remove('hidden');
            });
            Livewire.hook('message.processed', () => {
                document.getElementById('global-loading')?.classList.add('hidden');
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

            let loadingTimer = null;

            Livewire.hook('message.sent', () => {
                loadingTimer = setTimeout(() => {
                    document.getElementById('global-loading')?.classList.remove('hidden');
                }, 500);
            });

            Livewire.hook('message.processed', () => {
                clearTimeout(loadingTimer);
                loadingTimer = null;
                document.getElementById('global-loading')?.classList.add('hidden');
            });
        });
    </script>
    @livewireScripts
</body>

</html>