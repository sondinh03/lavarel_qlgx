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
</head>

<body class="bg-gray-50 text-gray-800 font-sans antialiased min-h-screen flex flex-col">
    {{-- Loading Overlay  --}}
    <x-loading-indicator target="selectedNamHoc,selectedKhoi,resetFilters" />

    {{-- HEADER --}}
    <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                {{-- Logo --}}
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="flex items-center space-x-3">
                        <img src="{{ url(config('settings.logo')) }}" alt="Logo" class="h-10 w-auto">
                        <span class="text-xl font-bold text-indigo-700 hidden sm:block">
                            {{ config('settings.web_name') }}
                        </span>
                    </a>
                </div>

                {{-- Menu desktop --}}
                <nav class="hidden md:flex space-x-8">
                    <a href="https://mvqlgiaoxu.org/tim-kiem" class="text-gray-700 hover:text-indigo-600 font-medium transition">
                        Kết quả học tập
                    </a>
                </nav>

                {{-- Right: Fullscreen + Login --}}
                <div class="flex items-center space-x-4">
                    <button id="fullscreen-button" class="text-gray-600 hover:text-indigo-600 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                    </button>

                    <!-- @auth
                        <x-nav /> {{-- Menu admin --}}
                    @endauth -->
                </div>

                {{-- Mobile menu button --}}
                <button class="md:hidden burger-menu text-gray-700">
                    <i class="bi bi-list text-2xl"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            {{-- Module hệ thống (nếu có) --}}
            @yield('content_top')
            @yield('column_left')

            {{-- Nội dung chính từ Livewire hoặc Blade --}}
            @yield('content')

            @yield('column_right')
            @yield('content_bottom')
        </div>
    </main>

    {{-- FOOTER --}}
    @includeIf('frontend.footer')

    {{-- Back to top --}}
    <button class="fixed bottom-6 right-6 z-50 w-12 h-12 bg-indigo-600 text-white rounded-full shadow-lg hover:bg-indigo-700 transition hover:scale-110" onclick="window.scrollTo({top:0,behavior:'smooth'})">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
        </svg>
    </button>

    {{-- JS – Mix + Livewire --}}
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script> {{-- hoặc main.js / custom.js --}}

    {{-- Fullscreen functionality --}}
    <script>
        document.getElementById('fullscreen-button')?.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.log('Fullscreen error:', err);
                });
            } else {
                document.exitFullscreen();
            }
        });
    </script>

    @stack('scripts')
    @livewireScripts
</body>
html>