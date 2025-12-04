<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Slug;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\Lop;
use App\Models\Block;
use App\Models\DiHoc;
use App\Models\NamHoc;

class LopHocController extends Controller
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
    
    public function index($slug, $id)
    {
    }
    public function show($slug, $id): View
    {
        \Assets::add('fontawesome');
        
        $giatri = Slug::where('keyword', '=', $slug)->where('sluggable_id', $id)->first();
        if(!empty($giatri)){
            
            $lop = Lop::where('id', $id)->where('status', 1)->first();
                
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
            
            $day_date = date('Y-m-d');
            
            $start_date = $lop->start_date_one;
            $end_Date = $lop->end_date_one;
            
            $startTime = strtotime($start_date);
            $endTime = strtotime($end_Date);
            $weeks = array();
            $date = new \DateTime();
            $i=1;
            while ($startTime < $endTime) {
                $weeks[$i]['week'] = date('W', $startTime);
                $weeks[$i]['year'] = date('Y', $startTime);
                $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                $weeks[$i]['Monday']=$date->format('Y-m-d');
                $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                $startTime += strtotime('+1 week', 0);
                $i++;
            }
            $this->data['weeks_ky1'] = count($weeks);
            
            $start_date = $lop->start_date_two;
            $end_Date = $lop->end_date_two;
            
            $startTime = strtotime($start_date);
            $endTime = strtotime($end_Date);
            
            $weeks = array();
            $date = new \DateTime();
            $i=1;
            while ($startTime < $endTime) {
                $weeks[$i]['week'] = date('W', $startTime);
                $weeks[$i]['year'] = date('Y', $startTime);
                $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                $weeks[$i]['Monday']=$date->format('Y-m-d');
                $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                $startTime += strtotime('+1 week', 0);
                $i++;
            }
            
            $this->data['weeks_ky2'] = count($weeks);
            
            $student = Student::where('lop', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
            
            $stt_start = $student->firstItem();
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
                
                // địa chỉ
                if(!empty($item->ward)){
                    $xaphuong = $this->GetXaTruQuan($item->ward);
                    $item['ward'] = $xaphuong['name'];
                }else{
                    $item['ward'] = '';
                }
                
                if(!empty($item->province)){
                    $tinhthanh = $this->GetTinhThanhQuan($item->province);
                    $item['province'] = $tinhthanh;
                }else{
                    $item['province'] = '';
                }
                if(!empty($item['schoolyear'])){
                    $schoolyear = NamHoc::where('id', $item['schoolyear'])->where('status', 1)->get()->first();
                    $item['schoolyear'] = $schoolyear->name;
                }
            }
            
            
            $this->data['student'] = $student;
            
            $this->data['pagination'] = $student->links();
            
            $this->data['lop'] = $lop;
                
            return view()->first([
                'frontend.lophoc',
                'frontend.layout.main',
            ], $this->data);
        }else{
            return view()->first([
                'errors.403',
                'errors.layout.main',
            ], $this->data);
        }
    }
    
    public function GetTinhThanhQuan($id){
        @include(resource_path().'/cities/tinh_thanhpho.php');
        
        $tinhthanh_child = '';
        foreach($tinh_thanhpho as $key => $tinhthanh){
            if($key == $id){
                $tinhthanh_child = $tinhthanh;
            }
        }
        
        return $tinhthanh_child;
    }
    
    public function GetXaTruQuan($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $xaphuong_child = '';
        foreach($xa_phuong_thitran as $key => $xaphuong){
            if($xaphuong['xaid'] == $id){
                $xaphuong_child = $xaphuong;
            }
        }
        
        return $xaphuong_child;
    }
    
    /**
     * Display a listing of the submitInfo.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfo(Request $request)
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
            
            foreach ($params['dihoc'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    $data = DB::table('dihoc')
                        ->where('idh', $key)
                        ->where('lophoc', $id)
                        ->where('hocky', $ky1)
                        ->where('tuan', $_key)
                        ->where('status', 1)
                        ->orderBy('id', 'ASC')
                        ->get()->first();
                        
                    $weight = DB::table('dihoc')
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
                        if($data->dihoc != $_row){
                            //update
                            DiHoc::where('id', $data->id)->where('idh', $data->idh)->where('lophoc', $data->lophoc)->where('hocky', $data->hocky)->where('tuan', $data->tuan)->where('status', $data->status)->update(['dihoc' => $_row]);
                        }
                    }else{
                        //insert
                        DiHoc::create([
                            'idh'       => $key,
                            'lophoc'    => $id,
                            'hocky'     => $ky1,
                            'tuan'      => $_key,
                            'dihoc'     => $_row,
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
    public function submitInfoHk2(Request $request)
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
            
            foreach ($params['dihoc'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    $data = DB::table('dihoc')
                    ->where('idh', $key)
                    ->where('lophoc', $id)
                    ->where('hocky', $ky2)
                    ->where('tuan', $_key)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->first();
                    
                    $weight = DB::table('dihoc')
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
                        if($data->dihoc != $_row){
                            //update
                            DiHoc::where('id', $data->id)->where('idh', $data->idh)->where('lophoc', $data->lophoc)->where('hocky', $data->hocky)->where('tuan', $data->tuan)->where('status', $data->status)->update(['dihoc' => $_row]);
                        }
                    }else{
                        //insert
                        DiHoc::create([
                            'idh'       => $key,
                            'lophoc'    => $id,
                            'hocky'     => $ky2,
                            'tuan'      => $_key,
                            'dihoc'     => $_row,
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
