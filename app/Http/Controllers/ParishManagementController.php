<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\ParishManagement;
use Spatie\SchemaOrg\Schema;

class ParishManagementController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }
    
    protected array $data = [];
    
    protected mixed $url_prefix = null;
    
    protected mixed $cache_time = 0;
    
    private $assets;
    
    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    public function show($id): View
    {
        \Assets::add('fontawesome');
        
        $parish_management = Cache::remember("parish_management_id_$id", $this->cache_time, function () use ($id) {
            return ParishManagement::findOrFail($id);
        });
            
        $this->data['parish_management'] = $parish_management;
        
        return view()->first([
            'frontend.parish_management',
            'frontend.layout.main',
        ], $this->data);
    }
}
