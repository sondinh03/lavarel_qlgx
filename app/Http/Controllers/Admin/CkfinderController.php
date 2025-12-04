<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class CkfinderController extends Controller
{
    public function __invoke()
    {
        return view('backpack::ckfinder');
    }
}
