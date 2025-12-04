<ul {{$attributes->merge(['class' => 'primary-menu navbar d-none d-md-flex align-items-center justify-content-center list-unstyled ps-0 py-0 mb-0'])}}>
    @foreach($menus ?? [] as $menu)
        <x-menu.item :child="$menu->child" class="{{$menu->class}}" style="{{$menu->style}}" link="{{$menu->link}}">
            <a href="{{$menu->link}}" class="text-decoration-none fw-semibold py-2 text-uppercase fs-8 @if($menu->class !='') {{$menu->class}} @else @endif"><span class="me-2">{!! $menu->html !!}</span>{{$menu->name}}</a>
        </x-menu.item>
    @endforeach
</ul>
