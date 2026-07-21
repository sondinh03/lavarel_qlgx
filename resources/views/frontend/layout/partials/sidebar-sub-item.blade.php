{{-- resources/views/frontend/layout/partials/sidebar-sub-item.blade.php --}}
{{-- Usage: @include('...sidebar-sub-item', ['route' => 'route.name', 'label' => 'Label', 'active' => 'pattern.*'|bool]) --}}
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
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
       {{ $isActive ? 'text-primary-700 font-medium bg-primary-50' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-50' }}">
    <span class="w-1.5 h-1.5 rounded-full flex-shrink-0
        {{ $isActive ? 'bg-primary-500' : 'bg-slate-300' }}"></span>
    {{ $label }}
</a>
