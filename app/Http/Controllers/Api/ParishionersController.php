<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ParishionersController extends Controller
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
            $array_parishes = array();
            if(!empty($deid)){
                $rows = DB::table('parishes')
                ->select('id', 'name')
                ->where('deanery_id', '=', $deid)
                ->where('status', 1)
                ->orderBy('name')
                ->get()->toArray();

                $array_parishes = json_decode(json_encode($rows, true), true);
            }
            
            if (count($array_parishes) > 0) {
                return response()->json($array_parishes, 200);
            } else {
                return response()->json([
                    'status' => 'warning',
                    'message' => 'No records found',
                ], 404);
            }
        }
        
        if(!empty($_GET['pid'])){
            $pid = $_GET['pid'];
            $array_groups = array();
            if(!empty($pid)){
                $rows = DB::table('parish_groups')
                ->select('id', 'name')
                ->where('parish_id', '=', $pid)
                ->where('status', 1)
                ->orderBy('name')
                ->get()->toArray();

                $array_groups = json_decode(json_encode($rows, true), true);
            }
            
            if (count($array_groups) > 0) {
                return response()->json($array_groups, 200);
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
        
        if(!empty($_GET['communion_dioceses'])){
            $communion_dioceses = $_GET['communion_dioceses'];
            $array_dioceses = array();
            if(!empty($communion_dioceses)){
                $array_diocese = DB::table('deanerys')
                ->where('did', '=', $communion_dioceses)
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
        
        if(!empty($_GET['communion_deanerys'])){
            $communion_deanerys = $_GET['communion_deanerys'];
            $array_deanerys = array();
            if(!empty($communion_deanerys)){
                $array_deanery = DB::table('parish_managements')
                ->where('deanerys', '=', $communion_deanerys)
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
