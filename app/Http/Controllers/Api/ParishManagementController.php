<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Resources\ParishManagementResource;
use App\Http\Resources\ParishManagementCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ParishManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$search_term = $request->input('q');
        
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
        
        if(!empty($_GET['diocese'])){
            $array_deanerys = DB::table('deanerys')
                ->where('status', '1')
                ->where('did', $_GET['diocese'])
                ->orderBy('id', 'ASC')
                ->get()
                ->toArray();
            $array_deanerys = array_values($array_deanerys);
            $array_deanerys = json_decode(json_encode($array_deanerys, true), true);
            $array_dea = array();
            foreach($array_deanerys as $item){
                $array_dea[] = $item;
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
        
        
        $parishmanagements = new ParishManagementCollection($parishmanagements);
        
        if (count($parishmanagements)) {
            return response()->json($parishmanagements, 200);
        } else {
            return response()->json([
                'status' => 'warning',
                'message' => 'No records found',
            ], 200);
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
        
        $ParishManagement = new ParishManagementResource(ParishManagement::find($id));
        
        if ($ParishManagement) {
            return response()->json($ParishManagement, 200);
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
