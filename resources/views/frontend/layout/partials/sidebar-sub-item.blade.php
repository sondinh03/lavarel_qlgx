{{-- resources/views/frontend/layout/partials/sidebar-sub-item.blade.php --}}
{{-- Usage: @include('...sidebar-sub-item', ['route' => 'route.name', 'label' => 'Label']) --}}
<a href="{{ route($route) }}"
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
       {{ request()->routeIs($route) ? 'text-primary-700 font-medium bg-primary-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
        {{ request()->routeIs($route) ? 'bg-primary-500' : 'bg-slate-300' }}"></span>
    {{ $label }}
</a>