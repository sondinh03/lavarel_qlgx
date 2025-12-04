<ul {{$attributes}} class="vertical-menu">
    @foreach($menus ?? [] as $menu)
        <x-menu-mobile.item :child="$menu->child" class="{{$menu->class}}" style="{{$menu->style}}">
            <a href="{{$menu->link}}" class="p-2 px-0 fs-6 fw-semibold text-uppercase @if($menu->class !='') {{$menu->class}} @else text-dark @endif">@if($menu->html !='')<span class="me-2">{!! $menu->html !!}</span>@endif{{$menu->name}}</a>
        </x-menu-mobile.item>
    @endforeach
</ul>