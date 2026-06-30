<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/*
use App\Http\Resources\ParishResource;
use App\Http\Resources\ParishCollection;
*/
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ParishController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        $Parish = new ParishResource(Parish::find($id));
        
        if ($Parish) {
            return response()->json($Parish, 200);
        } else {
            return response()->json([
                'status' => 'warning',
                'message' => 'The record does not exist',
            ], 200);
        }
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
