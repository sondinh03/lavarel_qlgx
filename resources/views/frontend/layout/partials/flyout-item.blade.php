{{-- resources/views/frontend/layout/partials/flyout-item.blade.php --}}
{{-- Usage: @include('...flyout-item', ['route' => 'route.name', 'label' => 'Label']) --}}
<a href="{{ route($route) }}"
    class="block px-4 py-2 text-sm transition
       {{ request()->routeIs($route) ? 'text-primary-700 font-medium bg-primary-50' : 'text-slate-700 hover:bg-slate-50' }}">
    {{ $label }}
</a>