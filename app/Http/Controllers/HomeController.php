<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Parish;
use App\Models\Parishioners;
use App\Models\Family;
use App\Models\MarriageAnnouncement;
use App\Models\Association;
use Illuminate\Support\Facades\Auth;
use App\Models\SetAdmin;
use App\Models\Decen;

class HomeController extends Controller
{
    protected array $data = [];
    
    protected mixed $url_prefix = null;
    
    protected mixed $cache_time = 0;
    
    private $assets;
    
    public function __construct()
    {
        $this->middleware('auth');
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    public function index(): View
    {
        \Assets::add('fontawesome');
        
        if (Auth::check()) {
            $user = backpack_user();
            if(!empty($user->id)){
                $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
                if(!empty($setadmin)){
                    $giaodan = Parishioners::count();
                    
                    $family = Family::count();
                    
                    $association =  Association::count();
                    
                    $marriage_announcements = MarriageAnnouncement::count();
                    
                    return view()->first([
                        'frontend.home',
                        'frontend.layout.main',
                    ], compact(
                        'giaodan',
                        'family',
                        'association',
                        'marriage_announcements'
                        ));
                }else{
                    $user = backpack_user();
                    $gx = $hs = '';
                    $decen = Decen::where('use', $user->id)->where('status', '1')->get()->first();
                    if(!empty($decen->parish)){
                        $gx = 1;
                    }else{
                        $gx = 0;
                    }
                    if(!empty($decen->student)){
                        $hs = 1;
                    }else{
                        $hs = 0;
                    }
                    $loginroi = 1;
                    
                    return view()->first([
                        'frontend.helo',
                        'frontend.layout.main',
                    ], compact(
                        'user',
                        'gx',
                        'hs',
                        'loginroi',
                    ));
                }
            }
        }else{
            return view('frontend.home');
        }
    }    
}