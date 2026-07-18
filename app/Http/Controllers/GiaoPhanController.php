<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Http\Controllers\Api\GiaoPhanController;
use Illuminate\Support\Facades\DB;
use App\Models\CatechismClass;
use App\Models\Block;
use App\Models\Decen;

class GiaoPhanController extends Controller
{
    //
    protected array $data = [];
    
    protected mixed $cache_time = 0;
    
    protected mixed $per_page = 10;
    
    private $assets;
    
    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    public function getSearch(Request $request)
    {
        if(!empty($_POST['schoolyear'])){
            $search = $_POST['schoolyear'];
            $user = backpack_user();
            $userId = $user->id;
            $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
            $array_khoi = array(array(
                'id' => '',
                'name'  => 'Chọn khối ngành',
            ));
            if ($search) {
                $array_block = Block::where('namhoc', $search)
                ->where('pid', $decen->pid)
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get()->toArray();
                
                $array_block = json_decode(json_encode($array_block, true), true);
                
                foreach($array_block as $item){
                    $array_khoi[] = $item;
                }
            }
            
            if (count($array_khoi) > 0) {
                return response()->json($array_khoi, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_POST['block'])){
            $search = $_POST['block'];
            $array_class = array();
            if ($search) {
                $array_lop = CatechismClass::where('block', $search)
                ->where('status', 1)
                ->orderBy('name', 'asc')
                ->get()->toArray();
                
                $array_lop = json_decode(json_encode($array_lop, true), true);
                
                foreach($array_lop as $item){
                    $array_class[$item['id']] = $item;
                }
            }
            
            if (count($array_class) > 0) {
                return response()->json($array_class, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_POST['dioceses'])){
            $search = $_POST['dioceses'];
            $array_dea = array();
            if ($search) {
                $array_deanerys = DB::table('deanerys')
                    ->select('id', 'did', 'name')
                    ->where('did', '=', $search)
                    ->where('status', 1)
                    ->get()->toArray();
                
                $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
                
                foreach($array_deanerys as $item){
                    $array_dea[$item['id']] = $item;
                }
            }
            
            if (count($array_dea) > 0) {
                return response()->json($array_dea, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_POST['deanerys'])){
            $search = $_POST['deanerys'];
            $array_pm = array();
            if ($search) {
                $array_deanerys = DB::table('parish_managements')
                    ->select('id', 'name')
                    ->where('deanerys', '=', $search)
                    ->where('status', 1)
                    ->get()->toArray();
                
                $array_parish_managements = json_decode(json_encode($array_deanerys, true), true);
                
                foreach($array_parish_managements as $item){
                    $array_pm[$item['id']] = $item;
                }
            }
            
            if (count($array_pm) > 0) {
                return response()->json($array_pm, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_POST['parish_managements'])){
            $search = $_POST['parish_managements'];
            $array_parish_managements = array();
            if ($search) {
                $array_parish_management = DB::table('parish_groups')
                    ->select('id', 'name')
                    ->where('parish_id', '=', $search)
                    ->where('status', 1)
                    ->get()->toArray();
                
                $array_parish_management = json_decode(json_encode($array_parish_management, true), true);
                
                foreach($array_parish_management as $item){
                    $array_parish_managements[$item['id']] = $item;
                }
            }
            
            if (count($array_parish_managements) > 0) {
                return response()->json($array_parish_managements, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_POST['giaoxu'])){
            $search = $_POST['giaoxu'];
            $array_lop = array();
            if ($search) {
                $lop = DB::table('lop')
                ->select('id', 'name')
                ->where('parish_id', '=', $search)
                ->where('status', 1)
                ->get()->toArray();
                
                $lop = json_decode(json_encode($lop, true), true);
                
                foreach($lop as $item){
                    $array_lop[$item['id']] = $item;
                }
            }
            
            if (count($array_lop) > 0) {
                return response()->json($array_lop, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
    }
    
}
