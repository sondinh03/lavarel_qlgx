{{-- resources/views/frontend/layout/partials/flyout-item.blade.php --}}
{{-- Usage: @include('...flyout-item', ['route' => 'route.name', 'label' => 'Label', 'active' => 'pattern.*'|bool]) --}}
@php
    if (isset($active)) {
        $isActive = is_bool($active)
            ? $active
            : request()->routeIs(...(array) $active);
    } else {
        $isActive = request()->routeIs($route);
    }
@endphp
<a href="{{ route($route) }}"
    class="block px-4 py-2 text-sm transition
       {{ $isActive ? 'text-primary-700 font-medium bg-primary-50' : 'text-slate-700 hover:bg-slate-50' }}">
    {{ $label }}
</a>
