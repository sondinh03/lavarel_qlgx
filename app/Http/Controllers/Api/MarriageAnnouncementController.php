<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class MarriageAnnouncementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(!empty($_GET['female_diocese'])){
            $diocese = $_GET['female_diocese'];
            $array_dea = array();
            if(!empty($diocese)){
                $array_deanerys = DB::table('deanerys')
                ->select('id', 'did', 'name')
                ->where('did', '=', $diocese)
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
        
        if(!empty($_GET['female_deanery'])){
            $deanery = $_GET['female_deanery'];
            $array_parish_managements = array();
            if(!empty($deanery)){
                $array_parish_managements = DB::table('parish_managements')
                ->select('id', 'name')
                ->where('deanerys', '=', $deanery)
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
        
        if(!empty($_GET['female_parish_management'])){
            $parish_management = $_GET['female_parish_management'];
            $array_parishs = array();
            if(!empty($parish_management)){
                $array_parishs = DB::table('parishs')
                ->where('pid', '=', $parish_management)
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
        
        // nam
        
        if(!empty($_GET['male_diocese'])){
            $diocese = $_GET['male_diocese'];
            $array_dea = array();
            if(!empty($diocese)){
                $array_deanerys = DB::table('deanerys')
                ->select('id', 'did', 'name')
                ->where('did', '=', $diocese)
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
        
        if(!empty($_GET['male_deanery'])){
            $deanery = $_GET['male_deanery'];
            $array_parish_managements = array();
            if(!empty($deanery)){
                $array_parish_managements = DB::table('parish_managements')
                ->select('id', 'name')
                ->where('deanerys', '=', $deanery)
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
        
        if(!empty($_GET['male_parish_management'])){
            $parish_management = $_GET['male_parish_management'];
            $array_parishs = array();
            if(!empty($parish_management)){
                $array_parishs = DB::table('parishs')
                ->where('pid', '=', $parish_management)
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
