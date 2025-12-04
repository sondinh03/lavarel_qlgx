@props([
    'child',
    'class',
    'html',
])

@if(count($child) > 0)
    <li {{$attributes->merge(['class' => 'dropdown'])}}>
        {{$slot}}
        <ul class="sub-menu">
            @foreach($child as $menu)
                <x-menu-mobile.item :child="$menu->child">
                    <a href="{{$menu->link}}" class="p-2 pl-4 fs-7 fw-medium">{{$menu->name}}</a>
                </x-menu-mobile.item>
            @endforeach
        </ul>
    </li>
@else
    <li {{$attributes->merge(['class' => 'rounded-2'])}}>{{$slot}}</li>
@endif
