<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\SystemOverviewService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, SystemOverviewService $overview): View
    {
        $query = trim((string) $request->query('q', ''));

        return view(backpack_view('dashboard'), [
            'stats'   => $overview->get(),
            'support' => $query !== '' ? $overview->searchSupport($query) : null,
            'title'   => trans('backpack::base.dashboard'),
        ]);
    }
}
