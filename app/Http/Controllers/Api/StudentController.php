<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use App\Models\Lop;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!empty($_GET['province'])){
            @include(resource_path().'/cities/xa_phuong_thitran.php');
            $ward = array();
            foreach($xa_phuong_thitran as $item){
                if($item['matp'] == $_GET['province']){
                    $ward[$item['xaid']] = $item;
                }
            }
            
            if (count($ward) > 0) {
                return response()->json($ward, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['did'])){
            $did = $_GET['did'];
            $array_dea = array();
            if(!empty($did)){
                $array_deanerys = DB::table('deanerys')
                ->select('id', 'did', 'name')
                ->where('did', '=', $did)
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
        
        if(!empty($_GET['deid'])){
            $deid = $_GET['deid'];
            $array_parish_managements = array();
            if(!empty($deid)){
                $array_parish_managements = DB::table('parish_managements')
                ->select('id', 'name')
                ->where('deanerys', '=', $deid)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parish_managements = json_decode(json_encode($array_parish_managements, true), true);
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
        
        if(!empty($_GET['pid'])){
            $pid = $_GET['pid'];
            $array_parishs = array();
            if(!empty($pid)){
                $array_parishs = DB::table('parishs')
                ->where('pid', '=', $pid)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_parishs = json_decode(json_encode($array_parishs, true), true);
            }
            
            if (count($array_parishs) > 0) {
                return response()->json($array_parishs, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        if(!empty($_GET['pid_assid'])){
            $pid = $_GET['pid_assid'];
            if(!empty($pid)){
                $array_associations = DB::table('associations')
                ->where('pid', '=', $pid)
                ->where('status', 1)
                ->get()->toArray();
                
                $array_associations = json_decode(json_encode($array_associations, true), true);
            }
            
            if (count($array_associations) > 0) {
                return response()->json($array_associations, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        if(!empty($_GET['baptism_dioceses'])){
            $baptism_dioceses = $_GET['baptism_dioceses'];
            $array_dioceses = array();
            if(!empty($baptism_dioceses)){
                $array_diocese = DB::table('deanerys')
                ->where('did', '=', $baptism_dioceses)
                ->where('status', 1)
                ->get()->toArray();
                $array_dioceses = json_decode(json_encode($array_diocese, true), true);
            }
            if (count($array_dioceses) > 0) {
                return response()->json($array_dioceses, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['baptism_deanerys'])){
            $baptism_deanerys = $_GET['baptism_deanerys'];
            $array_deanerys = array();
            if(!empty($baptism_deanerys)){
                $array_deanery = DB::table('parish_managements')
                ->where('deanerys', '=', $baptism_deanerys)
                ->where('status', 1)
                ->get()->toArray();
                $array_deanerys = json_decode(json_encode($array_deanery, true), true);
                
            }
            if (count($array_deanerys) > 0) {
                return response()->json($array_deanerys, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['more_power_dioceses'])){
            $baptism_dioceses = $_GET['more_power_dioceses'];
            $array_dioceses = array();
            if(!empty($baptism_dioceses)){
                $array_diocese = DB::table('deanerys')
                ->where('did', '=', $baptism_dioceses)
                ->where('status', 1)
                ->get()->toArray();
                $array_dioceses = json_decode(json_encode($array_diocese, true), true);
            }
            if (count($array_dioceses) > 0) {
                return response()->json($array_dioceses, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['more_power_deanerys'])){
            $baptism_deanerys = $_GET['more_power_deanerys'];
            $array_deanerys = array();
            if(!empty($baptism_deanerys)){
                $array_deanery = DB::table('parish_managements')
                ->where('deanerys', '=', $baptism_deanerys)
                ->where('status', 1)
                ->get()->toArray();
                $array_deanerys = json_decode(json_encode($array_deanery, true), true);
                
            }
            if (count($array_deanerys) > 0) {
                return response()->json($array_deanerys, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['q']) AND !empty($_GET['giaoxu'])){
            $giaoxu = $_GET['giaoxu'];
            $search = $_GET['q'];
            if ($search) {
                $results = Lop::where('name', 'LIKE', '%'.$search.'%')->where('pid', $giaoxu)->where('status', '=', 1)->paginate(10);
            } else {
                $results = Lop::paginate(10);
            }
            return $results;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
