<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Holymanagement;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\CatechismClass;
use App\Models\Slug;
use Illuminate\Support\Facades\Cache;
use App\Models\Block;
use Illuminate\Contracts\View\View;
use Faker\Core\DateTime;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\DiLe;
use App\Models\NamHoc;

class DiLeController extends Controller
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
        
        $dile = Slug::where('keyword', '=', $slug)->where('sluggable_id', $id)->first();
        if(!empty($dile)){
            
            $lop = CatechismClass::where('id', $id)->where('status', 1)->orderBy('name', 'asc')->get()->first();
            
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
            foreach($student as $item){
                $item['stt'] = $stt_start++;
                
                $item['slug'] = url(slug($item).$this->url_prefix);
                
                $holy = Holymanagement::where('id', $item['holy'])->first();
                if(!empty($holy->name)){
                    $item['holy'] = $holy->name;
                }else{
                    $item['holy'] = '';
                }
                if(!empty($item['schoolyear'])){
                    $schoolyear = NamHoc::where('id', $item['schoolyear'])->where('status', 1)->get()->first();
                    $item['schoolyear'] = $schoolyear->name;
                }
                
                $item['birthday'] = date("d-m-Y", strtotime($item['birthday']));
            }
            
            $begin = Carbon::parse($lop->start_date_one);
            $end = Carbon::parse($lop->end_date_one);
            
            $interval = \DateInterval::createFromDateString('1 day');
            
            $period = New \DatePeriod($begin, $interval, $end);
            
            $this->data['period'] = $period;
            
            // ky 2
            $begin_hk2 = Carbon::parse($lop->start_date_two);
            $end_hk2 = Carbon::parse($lop->end_date_two);
            
            $interval = \DateInterval::createFromDateString('1 day');
            
            $period_hk2 = New \DatePeriod($begin_hk2, $interval, $end_hk2);
            
            $this->data['period_hk2'] = $period_hk2;
            
            $this->data['student'] = $student;
            
            $this->data['pagination'] = $student->links();
            
            $this->data['lop'] = $lop;
            
            return view()->first([
                'frontend.dile',
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
            'datamoi' => 'required',
        ]);
        
        if ($validator->passes()) {
            $ky1 = $request->ky1;
            $id = $request->id;
            $datamoi = $request->datamoi;
            $params = array();
            parse_str($datamoi, $params);
            
            foreach ($params['dile'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    foreach ($_row AS $_keyitem => $item){
                        $data = DB::table('dile')
                        ->where('idh', $key)
                        ->where('lophoc', $id)
                        ->where('hocky', $ky1)
                        ->where('thang', $_key)
                        ->where('ngay', $_keyitem)
                        ->where('status', 1)
                        ->orderBy('id', 'ASC')
                        ->get()->first();
                        
                        $weight = DB::table('dile')
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
                            //update
                            DiLe::where('id', $data->id)
                                ->where('idh', $data->idh)
                                ->where('lophoc', $data->lophoc)
                                ->where('hocky', $data->hocky)
                                ->where('thang', $data->thang)
                                ->where('ngay', $data->ngay)
                                ->where('status', 1)
                                ->update(['dile' => $item]);
                        }else{
                            //insert
                            DiLe::create([
                                'idh'       => $key,
                                'lophoc'    => $id,
                                'hocky'     => $ky1,
                                'thang'     => $_key,
                                'ngay'      => $_keyitem,
                                'dile'      => $item,
                                'weight'    => $weight,
                                'status'    => 1,
                            ]);
                        }
                    }
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh cho cả lớp.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    
    /**
     * Display a listing of the submitInfohk2.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfohk2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ky2' => 'required',
            'id' => 'required',
            'datamoi' => 'required',
        ]);
        
        if ($validator->passes()) {
            $ky2 = $request->ky2;
            $id = $request->id;
            $datamoi = $request->datamoi;
            $params = array();
            parse_str($datamoi, $params);
            
            foreach ($params['dile'] as $key => $row){
                foreach ($row as $_key => $_row) {
                    foreach ($_row AS $_keyitem => $item){
                        $data = DB::table('dile')
                        ->where('idh', $key)
                        ->where('lophoc', $id)
                        ->where('hocky', $ky2)
                        ->where('thang', $_key)
                        ->where('ngay', $_keyitem)
                        ->where('status', 1)
                        ->orderBy('id', 'ASC')
                        ->get()->first();
                        
                        $weight = DB::table('dile')
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
                            //update
                            DiLe::where('id', $data->id)
                            ->where('idh', $data->idh)
                            ->where('lophoc', $data->lophoc)
                            ->where('hocky', $data->hocky)
                            ->where('thang', $data->thang)
                            ->where('ngay', $data->ngay)
                            ->where('status', 1)
                            ->update(['dile' => $item]);
                        }else{
                            //insert
                            DiLe::create([
                                'idh'       => $key,
                                'lophoc'    => $id,
                                'hocky'     => $ky2,
                                'thang'     => $_key,
                                'ngay'      => $_keyitem,
                                'dile'      => $item,
                                'weight'    => $weight,
                                'status'    => 1,
                            ]);
                        }
                    }
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh cho cả lớp.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
