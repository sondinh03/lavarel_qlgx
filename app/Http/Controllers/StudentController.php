<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use App\Models\Student;
use Illuminate\Support\Facades\Cache;
use App\Models\Association;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Parish;
use App\Models\Holymanagement;
use Faker\Core\DateTime;
use App\Models\Lop;
use App\Models\DiHoc;
use App\Models\DiLe;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Decen;
use App\Models\SetAdmin;

class StudentController extends Controller
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
    
    public function show($id, $slug): View
    {
        \Assets::add('fontawesome');
        
        $user = backpack_user();
        if(!empty($user)){
            $userId = $user->id;
            if(!empty($_POST['giaoxu'])){
                $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            }else{
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
            }
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                $student = Student::where('id', $id)->where('pid', $decen->pid)->where('status', 1)->get()->first();    
            }elseif(!empty($setadmin)){
                $student = Student::where('id', $id)->where('status', 1)->get()->first();    
            }else{
                $student = '';
            }
            if(!empty($student)){
                //$student = Student::where('id', $id)->where('pid', $decen->pid)->where('status', 1)->get()->first();                
                if(!empty($student)){
                    $student['edit'] = config('app.url') . '/admin/student/'.$id.'/edit';
                    
                    $holy = Holymanagement::where('id', $student['holy'])->first();
                    if(!empty($holy->name)){
                        $student['holy'] = $holy->name;
                    }else{
                        $student['holy'] = '';
                    }
                    
                    $student['birthday'] = date("d-m-Y", strtotime($student['birthday']));
                    
                    if($student->paid != ''){
                        $parish = Parish::where('id', $student['paid'])->first();
                        $student['paid'] = $parish->name;
                    }else{
                        $student['paid'] = '';
                    }
                    
                    if($student->pid != ''){
                        $parish_management = ParishManagement::where('id', $student['pid'])->first();
                        $student['pid'] = ', ' . $parish_management->name;
                    }else{
                        $student['pid'] = '';
                    }
                    
                    if($student->deid != ''){
                        $deanery = Deanery::where('id', $student['deid'])->first();
                        $student['deid'] = ', ' . $deanery->name;
                    }else{
                        $student['deid'] = '';
                    }
                    
                    if($student->did != ''){
                        $diocese = Diocese::where('id', $student['did'])->first();
                        $student['did'] = ', ' . $diocese->name;
                    }else{
                        $student['did'] = '';
                    }
                    $lop = Lop::where('id', $student->lop)->where('status', 1)->first();
                    
                    $Date = date('Y-m-d');
                    
                    $ky1 = $ky2 = '';
                    if(!empty($lop->start_date_one) AND !empty($lop->end_date_one)){
                        if (($Date >= $lop->start_date_one) && ($Date <= $lop->end_date_one)){
                            $ky1 = 1;
                        }
                    }
                    
                    if(!empty($lop->start_date_two) AND !empty($lop->end_date_two)){
                        if (($Date >= $lop->start_date_two) && ($Date <= $lop->end_date_two)){
                            $ky2 = 2;
                        }
                    }
                    
                    $weeks = $this->get_first_and_last_day_of_week(date("Y"), date("W"));
                    
                    if(!empty($ky1)){
                        $dihoc = DiHoc::where('idh', $student->id)->where('lophoc', $student->lop )->where('hocky', $ky1)->where('status', 1)->where('created_at', '>=', $weeks->first_day->format('Y-m-d H:s:i'))->where('created_at', '<=', $weeks->last_day->format('Y-m-d H:s:i'))->get()->count();
                    }elseif(!empty($ky2)){
                        $dihoc = DiHoc::where('idh', $student->id)->where('lophoc', $student->lop )->where('hocky', $ky2)->where('status', 1)->where('created_at', '>=', $weeks->first_day->format('Y-m-d H:s:i'))->where('created_at', '<=', $weeks->last_day->format('Y-m-d H:s:i'))->get()->count();
                    }else{
                        $dihoc = array();
                    }
                    
                    if(!empty($ky1)){
                        $dile = DiLe::where('idh', $student->id)->where('lophoc', $student->lop )->where('hocky', $ky1)->where('status', 1)->where('created_at', '>=', $weeks->first_day->format('Y-m-d H:s:i'))->where('created_at', '<=', $weeks->last_day->format('Y-m-d H:s:i'))->get()->count();
                    }elseif(!empty($ky2)){
                        $dile = DiLe::where('idh', $student->id)->where('lophoc', $student->lop )->where('hocky', $ky2)->where('status', 1)->where('created_at', '>=', $weeks->first_day->format('Y-m-d H:s:i'))->where('created_at', '<=', $weeks->last_day->format('Y-m-d H:s:i'))->get()->count();
                    }else{
                        $dile = array();
                    }
                    
                    $diemthi = DB::table('diemthi')
                    ->where('ihv', $student->id)
                    ->where('status', 1)
                    ->orderBy('id', 'ASC')
                    ->get()->first();
                    
                    $this->data['diemthi'] = $diemthi;
                    
                    $this->data['dile'] = $dile;
                    
                    $this->data['dihoc'] = $dihoc;
                    
                    $this->data['student'] = $student;
                    
                    $this->data['lop'] = $lop;
                    
                    $this->data['date'] = $Date;
                    
                    return view()->first([
                        'frontend.student',
                        'frontend.layout.main',
                    ], $this->data);
                }else{
                    return view()->first([
                        'errors.403',
                        'errors.layout.main',
                    ], $this->data);
                }
            }else{
                return view()->first([
                    'errors.403',
                    'errors.layout.main',
                ], $this->data);
            }
        }else{
            return view('home');
        }
    }
    
    public function get_first_and_last_day_of_week( $year_number, $week_number ) {
        // we need to specify 'today' otherwise datetime constructor uses 'now' which includes current time
        $today = new \DateTime( 'today' );
        
        return (object) [
            'first_day' => clone $today->setISODate( $year_number, $week_number, 0 ),
            'last_day'  => clone $today->setISODate( $year_number, $week_number, 6 )
        ];
    }
    
    public function submitInfoDiHoc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hocky' => 'required',
            'id' => 'required',
            'lop'   => 'required',
        ]);
        
        if ($validator->passes()) {
            $hocky = $request->hocky;
            $id = $request->id;
            $lop = $request->lop;
            
            $week = $this->get_first_and_last_day_of_week(date("Y"), date("W"));
            
            $data = DB::table('dihoc')
                ->where('idh', $id)
                ->where('lophoc', $lop)
                ->where('hocky', $hocky)
                //->where('tuan', $_key)
                ->where('status', 1)
                ->where('created_at', '>=', $week->first_day->format('Y-m-d H:s:i'))
                ->where('created_at', '<=', $week->last_day->format('Y-m-d H:s:i'))
                ->orderBy('id', 'ASC')
                ->get()->first();
            
            $weight = DB::table('dihoc')
                ->where('lophoc', $lop)
                ->where('hocky', $hocky)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->max('weight');
            
            if(empty($weight)){
                $weight = 1;
            }else{
                $weight = intval($weight) + 1;
            }
            
            $lophoc = Lop::where('status', 1)->where('id', $lop)->get()->first();
            
            $day_date = date('Y-m-d');
            
            if($day_date >= $lophoc->start_date_one AND $day_date <= $lophoc->end_date_one){
                $start_date = $lophoc->start_date_one;
                $end_Date = $lophoc->end_date_one;
            }elseif($day_date >= $lophoc->start_date_two AND $day_date <= $lophoc->end_date_two){
                $start_date = $lophoc->start_date_two;
                $end_Date = $lophoc->end_date_two;
            }else{
                $start_date = $end_Date = '';
            }
            
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
            
            $value_tuan = '';
            
            foreach($weeks as $key => $tuan){
                $homnay = date('Y-m-d');
                if($tuan['Monday'] <= $homnay AND $homnay <= $tuan['Sunday']){
                    $value_tuan = $key;
                }
            }
            
            $tuan = DB::table('dihoc')
            ->where('lophoc', $lop)
            ->where('hocky', $hocky)
            ->where('status', 1)
            ->orderBy('id', 'ASC')
            ->get()->count();
            
            if(empty($data)){
                //insert
                if(!empty($value_tuan)){
                    DiHoc::create([
                        'idh'       => $id,
                        'lophoc'    => $lop,
                        'hocky'     => $hocky,
                        'tuan'      => $value_tuan,
                        'dihoc'     => 1,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    
    public function submitInfoDiLe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hocky' => 'required',
            'id' => 'required',
            'lop'   => 'required',
        ]);
        
        if ($validator->passes()) {
            $hocky = $request->hocky;
            $id = $request->id;
            $lop = $request->lop;
            
            $week = $this->get_first_and_last_day_of_week(date("Y"), date("W"));
            
            $data = DB::table('dile')
            ->where('idh', $id)
            ->where('lophoc', $lop)
            ->where('hocky', $hocky)
            ->where('status', 1)
            ->where('created_at', '>=', $week->first_day->format('Y-m-d H:s:i'))
            ->where('created_at', '<=', $week->last_day->format('Y-m-d H:s:i'))
            ->orderBy('id', 'ASC')
            ->get()->first();
            
            $weight = DB::table('dile')
            ->where('lophoc', $lop)
            ->where('hocky', $hocky)
            ->where('status', 1)
            ->orderBy('id', 'ASC')
            ->get()->max('weight');
            
            if(empty($weight)){
                $weight = 1;
            }else{
                $weight = intval($weight) + 1;
            }
            
            $lophoc = Lop::where('status', 1)->where('id', $lop)->get()->first();
            
            $start_date = $lophoc->start_date_two;
            $end_Date = $lophoc->end_date_two;
            
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
                $weeks[$i]['Friday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+4 days"));
                $startTime += strtotime('+1 week', 0);
                $i++;
            }
            $homnay = date('Y-m-d');
            $date_one = date("l");
            $ngay = $thang = '';
            foreach($weeks as $key => $tuan){
                if($tuan['Monday'] <= $homnay AND $homnay <= $tuan['Friday']){
                    if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                        $ngay = date('j');
                        $thang = date('n');
                    }
                }
            }
            
            $tuan = DB::table('dile')
                ->where('lophoc', $lop)
                ->where('hocky', $hocky)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->count();
            
            if(empty($data)){
                if(!empty($ngay) AND !empty($thang)){
                    DiLe::create([
                        'idh'       => $id,
                        'lophoc'    => $lop,
                        'hocky'     => $hocky,
                        'thang'     => $thang,
                        'ngay'      => $ngay,
                        'dile'      => 1,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                }
            }
            return response()->json(['success'=>'Cảm ơn bạn đã điểm danh']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
