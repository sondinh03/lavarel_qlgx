<nav class="position-relative p-2 bg-white">
    <div class="row">
    	<div class="col-8 col-md-2 mb-3 mb-md-0">
    		<div class="flex justify-content-center">
                <a class="text-3xl font-bold leading-none text-decoration-none" href="/">
                    <x-smart-image src="{{ url(config('settings.logo')) }}" alt="{{ config('settings.web_name') }}" class="img-fluid w-auto h-auto"/>
                </a>
            </div>
    	</div>
    	<div class="col-4 col-md-10 d-flex justify-content-end align-items-center">
    		<x-menu></x-menu>
    		<div class="clearfix d-flex d-sm-flex d-md-none align-items-center">
        		<button type="button" class="d-block d-sm-block d-md-none burger-menu btn btn-secondary p-0 border-0 fs-3 py-1 px-2">
                	<i class="bi bi-list"></i>
                </button>
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


