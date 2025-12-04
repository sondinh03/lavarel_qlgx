@props([
    'child',
    'class',
    'html',
])
@if(count($child) > 0)
    <li {{$attributes->merge(['class' => 'px-1 dropdown'])}}>
        <div class="d-flex align-items-center">
            {{$slot}}
            <i class="bi bi-chevron-down ps-1 fs-9"></i>
        </div>
        <ul class="dropdown-menu">
            @foreach($child as $menu)
                <x-menu.item :child="$menu->child">
                    <a href="{{$menu->link}}" class="text-decoration-none text-nowrap">{{$menu->name}}</a>
                </x-menu.item>
            @endforeach
        </ul>
    </li>
@else
    @if($attributes['link'] == url()->current())
    	<li {{$attributes->merge(['class' => isset($attributes['class']) ? '' : 'py-2 px-1 active'])}}>{{$slot}}</li>
    @else
    	<li {{$attributes->merge(['class' => isset($attributes['class']) ? '' : 'py-2 px-2'])}}>{{$slot}}</li>
    @endif
@endif
