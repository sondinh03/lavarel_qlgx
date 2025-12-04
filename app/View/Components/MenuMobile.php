<?php

namespace App\View\Components;

use Illuminate\View\Component;

class MenuMobile extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $menus = \App\Models\Menu::where('parent_id')->where('status', 1)->orderBy('lft')->get();
        
        return view('components.menu-mobile.index', compact('menus'));
        //return view('components.menu-mobile');
    }
}
