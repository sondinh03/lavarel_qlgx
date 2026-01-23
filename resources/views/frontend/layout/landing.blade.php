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

<body class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 antialiased">

    {{-- MAIN - Không có header/nav --}}
    <main class="flex-1">
        <div class="w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content_top')
            <div class="w-full">
                <section>
                    @yield('content')
                </section>
            </div>
            @yield('content_bottom')
        </div>
    </main>

    {{-- FOOTER (optional) --}}
    @include('frontend.layout.footer')

    {{-- Scripts --}}
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>

    @stack('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireScripts
</body>

</html>