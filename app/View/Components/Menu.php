<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Menu extends Component
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
    /*
    public function render()
    {
        return view('components.menu');
    }
    */
    public function render(): View|string|Closure
    {
        $menus = \App\Models\Menu::where('parent_id')->where('status', 1)->orderBy('lft')->get();
        
        return view('components.menu.index')->with(compact('menus'));
    }
}
