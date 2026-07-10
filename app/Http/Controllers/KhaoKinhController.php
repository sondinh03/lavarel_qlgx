<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Slug;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\CatechismClass;
use App\Models\Block;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\KhaoKinh;
use Validator;
use App\Models\NamHoc;

class KhaoKinhController extends Controller
{
    protected array $data = [];
    
    protected mixed $cache_time = 0;
    
    protected mixed $per_page = 10;
    
    private $assets;
    
    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    public function index($slug, $id): View
    {
        \Assets::add('fontawesome');
        $giatri = Slug::where('keyword', '=', $slug)->where('sluggable_id', $id)->first();
        if(!empty($giatri)){
            $lop = Cache::remember("lop_$id", $this->cache_time, function () use ($id) {
                return CatechismClass::findOrFail($id);
            });
            if($lop->block != ''){
                $block = Block::where('id', $lop->block)->where('status', 1)->first();
                $lop['block'] = $block->name;
            }
            
            $lop->start_date_one = date("d-m-Y", strtotime($lop->start_date_one));
            $lop->end_date_one = date("d-m-Y", strtotime($lop->end_date_one));
            $lop->start_date_two = date("d-m-Y", strtotime($lop->start_date_two));
            $lop->end_date_two = date("d-m-Y", strtotime($lop->end_date_two));
            if(!empty($lop->schoolyear)){
                $schoolyear = NamHoc::where('id', $lop->schoolyear)->where('status', 1)->get()->first();
                $lop->schoolyear = $schoolyear->name;
            }
            
            $array_tech = array();
            if(!empty($lop->teacher)){
                foreach($lop->teacher as $row){
                    $tech = Teacher::where('id', $row)->where('status', 1)->first();
                    $array_tech[] = $tech->name;
                }
            }
            
            $lop['tech'] = $array_tech;
            
            $student = Student::where('lop', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
            $stt_start = $student->firstItem();
            $hk1 = $hk2 = array();
            foreach($student as $item){
                $item['stt'] = $stt_start++;
                $item['slug'] = url(slug($item).$this->url_prefix);
                
                $holy = Holymanagement::where('id', $item['holy'])->first();
                if(!empty($holy->name)){
                    $item['holy'] = $holy->name;
                }else{
                    $item['holy'] = '';
                }
                
                $item['birthday'] = date("d-m-Y", strtotime($item['birthday']));
                
                if(!empty($item['schoolyear'])){
                    $schoolyear = NamHoc::where('id', $item['schoolyear'])->where('status', 1)->get()->first();
                    $item['schoolyear'] = $schoolyear->name;
                }
                
                $hocsinh1 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 1)->where('status', 1)->orderBy('created_at', 'asc')->get();
                
                if(!empty($hocsinh1)){
                    $hk1[] = count($hocsinh1);
                }
                $item['hs1'] = $hocsinh1;
                
                $hocsinh2 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 2)->where('status', 1)->orderBy('created_at', 'asc')->get();
                
                if(!empty($hocsinh2)){
                    $hk2[] = count($hocsinh2);
                }
                
                $item['hs2'] = $hocsinh2;
            }
            
            if(!empty($hk1)){
                $items_k1 = max($hk1);
            }else{
                $items_k2 = 0;
            }
            
            $this->data['hk1'] = max($hk1);
            
            $this->data['items_k1'] = $items_k1;
            
            if(!empty($hk2)){
                $items_k2 = max($hk2);
            }else{
                $items_k2 = 0;
            }
            
            $this->data['hk2'] = max($hk2);
            
            $this->data['items_k2'] = $items_k2;
            
            $this->data['student'] = $student;
            
            $this->data['pagination'] = $student->links();
            
            $this->data['lop'] = $lop;
            
            return view()->first([
                'frontend.khaokinh',
                'frontend.layout.main',
            ], $this->data);
        }else{
            return view()->first([
                'errors.403',
                'errors.layout.main',
            ], $this->data);
        }
    }
    
    /**
     * Display a listing of the submitInfohk1.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfohk1(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ky1' => 'required',
            'id' => 'required',
            'idhv' => 'required',
            'datamoi' => 'required',
        ]);
        
        if ($validator->passes()) {
            $ky1 = $request->ky1;
            $id = $request->id;
            $idhv = $request->idhv;
            $datamoi = $request->datamoi;
            $params = array();
            parse_str($datamoi, $params);
            
            foreach ($params['khaokinh'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    $data = DB::table('khaokinh')
                    ->where('idh', $key)
                    ->where('lophoc', $id)
                    ->where('hocky', $ky1)
                    ->where('ngay', $_key)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->first();
                    
                    $weight = DB::table('khaokinh')
                    ->where('lophoc', $id)
                    ->where('hocky', $ky1)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->max('weight');
                    
                    if(empty($weight)){
                        $weight = 1;
                    }else{
                        $weight = intval($weight) + 1;
                    }
                    if(!empty($data->id)){
                        if($data->khaokinh != $_row){
                            //update
                            KhaoKinh::where('id', $data->id)->where('idh', $data->idh)->where('lophoc', $data->lophoc)->where('hocky', $data->hocky)->where('ngay', $data->ngay)->where('status', $data->status)->update(['khaokinh' => $_row]);
                        }
                    }else{
                        //insert
                        KhaoKinh::create([
                            'idh'       => $key,
                            'lophoc'    => $id,
                            'hocky'     => $ky1,
                            'ngay'      => $_key,
                            'khaokinh'  => $_row,
                            'weight'    => $weight,
                            'status'    => 1,
                        ]);
                    }
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh cho cả lớp.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    
    /**
     * Display a listing of the submitInfoHk2.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfohk2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ky2' => 'required',
            'id' => 'required',
            'idhv' => 'required',
            'datamoi' => 'required',
        ]);
        
        if ($validator->passes()) {
            $ky2 = $request->ky2;
            $id = $request->id;
            $idhv = $request->idhv;
            $datamoi = $request->datamoi;
            $params = array();
            parse_str($datamoi, $params);
            
            foreach ($params['khaokinh'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    $data = DB::table('khaokinh')
                    ->where('idh', $key)
                    ->where('lophoc', $id)
                    ->where('hocky', $ky2)
                    ->where('ngay', $_key)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->first();
                    
                    $weight = DB::table('khaokinh')
                    ->where('lophoc', $id)
                    ->where('hocky', $ky2)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->max('weight');
                    
                    if(empty($weight)){
                        $weight = 1;
                    }else{
                        $weight = intval($weight) + 1;
                    }
                    if(!empty($data->id)){
                        if($data->khaokinh != $_row){
                            //update
                            khaokinh::where('id', $data->id)->where('idh', $data->idh)->where('lophoc', $data->lophoc)->where('hocky', $data->hocky)->where('ngay', $data->ngay)->where('status', $data->status)->update(['khaokinh' => $_row]);
                        }
                    }else{
                        //insert
                        khaokinh::create([
                            'idh'       => $key,
                            'lophoc'    => $id,
                            'hocky'     => $ky2,
                            'ngay'      => $_key,
                            'khaokinh'  => $_row,
                            'weight'    => $weight,
                            'status'    => 1,
                        ]);
                    }
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh cho cả lớp.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
