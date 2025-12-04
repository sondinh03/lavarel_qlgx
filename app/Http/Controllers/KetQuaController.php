<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Slug;
use App\Models\Lop;
use App\Models\Block;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\Holymanagement;
use App\Models\NamHoc;

class KetQuaController extends Controller
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
            $lop = Lop::where('id', $id)->where('status', 1)->get()->first();
            if($lop->block != ''){
                $block = Block::where('id', $lop->block)->where('status', 1)->first();
                $lop['block'] = $block->name;
            }
            
            if(!empty($lop->schoolyear)){
                $schoolyear = NamHoc::where('id', $lop->schoolyear)->where('status', 1)->get()->first();
                $lop->schoolyear = $schoolyear->name;
            }
            
            $weeks_one = $this->week_tow($lop->start_date_one, $lop->end_date_one);
            
            $weeks_two = $this->week_tow($lop->start_date_two, $lop->end_date_two);
            
            $weeks = $weeks_one + $weeks_two;
            
            $day_one = $this->days_tow($lop->start_date_one, $lop->end_date_one);
            
            $day_two = $this->days_tow($lop->start_date_two, $lop->end_date_two);
            
            $days = $day_one + $day_two;
            
            $student = Student::where('lop', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
            $stt_start = $student->firstItem();
            foreach($student as $item){
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
                
                $item['stt'] = $stt_start++;
                $item->diem = DB::table('diemthi')
                ->where('ihv', $item->id)
                ->where('lop', $id)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->first();
                
                $item->weeks = $weeks;
                $item->days = $days;
            }
            
            $this->data['student'] = $student;
            
            $this->data['pagination'] = $student->links();
            
            $this->data['lop'] = $lop;
            
            return view()->first([
                'frontend.ketqua',
                'frontend.layout.main',
            ], $this->data);
        }else{
            return view()->first([
                'errors.403',
                'errors.layout.main',
            ], $this->data);
        }
    }
    
    public function week_tow($strtDate, $endDate) {
        $startDateWeekCnt = round(floor( date('d',strtotime($strtDate)) / 7)) ;
        
        $endDateWeekCnt = round(ceil( date('d',strtotime($endDate)) / 7)) ;
        
        $datediff = strtotime(date('Y-m',strtotime($endDate))."-01") - strtotime(date('Y-m',strtotime($strtDate))."-01");
        $totalnoOfWeek = round(floor($datediff/(60*60*24)) / 7) + $endDateWeekCnt - $startDateWeekCnt ;
        
        return $totalnoOfWeek;
    }
    
    public function days_tow($start_day, $end_day){
        $begin = Carbon::parse($start_day);
        $end = Carbon::parse($end_day);
        
        $interval = \DateInterval::createFromDateString('1 day');
        
        $period = New \DatePeriod($begin, $interval, $end);
        
        $array_day = array();
        foreach($period as $date_one){
            $day = date('l', strtotime($date_one->format("Y-m-d")));
            if($day == 'Thursday' OR $day == 'Sunday'){
                $array_day[$date_one->format("Y-m-d")] = $day;
            }
        }
        
        return count($array_day);
    }
}
