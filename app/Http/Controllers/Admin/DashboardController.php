<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SystemOverviewService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(SystemOverviewService $overview): View
    {
        return view(backpack_view('dashboard'), [
            'stats' => $overview->get(),
            'title' => trans('backpack::base.dashboard'),
        ]);
    }
}
