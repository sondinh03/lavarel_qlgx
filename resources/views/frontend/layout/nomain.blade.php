<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?: 'vi_VN' }}">
    <head>
        <title>@yield('title')</title>
        @includeIf('frontend.layout.meta')
        {!! Assets::css() !!}
        @stack('stylesheet')
        @livewireStyles
        @stack('header_scripts')
        @includeIf('feed::links')
    </head>
    <body class="giaoxu">
        <header class="shadow bg-white">
            <div class="container mx-auto">
            	<nav class="position-relative p-2 bg-white">
                    <div class="row">
                    	<div class="col-12 col-md-2 mb-3 mb-md-0">
                    		<div class="flex justify-content-center">
                                <a class="text-3xl font-bold leading-none text-decoration-none" href="/">
                                    <x-smart-image src="{{ url(config('settings.logo')) }}" alt="{{ config('settings.web_name') }}" class="img-fluid w-auto h-auto"/>
                                </a>
                            </div>
                    	</div>
                    	<div class="col-12 col-md-10 d-flex justify-content-between align-items-center">
                    		<div class="row w-100 align-items-center">
                    			<div class="col-3 col-md-11">
                    				<x-menu></x-menu>
                            		<div class="clearfix d-flex d-sm-flex d-md-none align-items-center">
                                		<button type="button" class="d-block d-sm-block d-md-none burger-menu btn btn-secondary p-0 border-0 fs-3 py-1 px-2">
                                        	<i class="bi bi-list"></i>
                                        </button>
                                    </div>
                    			</div>
                    			<div class="col-9 col-md-1">
                        			<div class="d-flex justify-content-end">
                                        <div class="dropdown header-fullscreen">
                            				<a class="nav-link icon full-screen-link" id="fullscreen-button">
                            					<svg xmlns="http://www.w3.org/2000/svg" class="header-icon" width="24" height="24" viewBox="0 0 24 24"><path d="M10 4L8 4 8 8 4 8 4 10 10 10zM8 20L10 20 10 14 4 14 4 16 8 16zM20 14L14 14 14 20 16 20 16 16 20 16zM20 8L16 8 16 4 14 4 14 10 20 10z"></path></svg>
                            				</a>
                            			</div>
                        			</div>
                    			</div>
                    		</div>
                    	</div>
                    </div>
                </nav>
                <div class="canvas-menu d-flex align-items-start flex-column">
                    <nav class="vertical">
                    	<button type="button" class="text-white bg-transparent btn-close fs-6" aria-label="Close"></button>
                		<x-menu-mobile></x-menu-mobile>
                	</nav>
                </div>
            </div>
        </header>
        <section class="bg-body-tertiary py-4">
        	<div class="main-overlay"></div>
            @yield('main')
        </section>
        @includeIf('frontend.layout.footer')
        
        <div class="progress-wrap cursor-pointer bg-white">
            <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
                <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
            </svg>
        </div>
                
        <script src="{{mix('js/manifest.js')}}"></script>
        <script src="{{mix('js/vendor.js')}}"></script>
        <script src="{{mix('js/main.js')}}"></script>
        <script src="{{mix('js/custom.js')}}"></script>
        
        {!! Assets::js() !!}
        @php
            Assets::group('defer')->js(function ($assets) {
                $output = '';
                foreach($assets as $a)
                    $output .= '<script src="' . $a . '" defer></script>';
                echo $output;
            });
        @endphp
        @stack('footer_scripts')
        @if(config('settings.extend_scripts'))
            {!! config('settings.extend_scripts') !!}
        @endif
        @livewireScripts
    </body>
</html>