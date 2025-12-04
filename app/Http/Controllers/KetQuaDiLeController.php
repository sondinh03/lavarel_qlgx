<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Lop;
use App\Models\Block;
use App\Models\Student;
use App\Models\DiLe;
use Carbon\Carbon;
use App\Models\Holymanagement;
use App\Models\NamHoc;

class KetQuaDiLeController extends Controller
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
        
        $lop = Lop::where('id', $id)->where('status', 1)->orderBy('name', 'asc')->get()->first();
        
        if($lop->block != ''){
            $block = Block::where('id', $lop->block)->where('status', 1)->first();
            $lop['block'] = $block->name;
        }
        
        if(!empty($lop->schoolyear)){
            $schoolyear = NamHoc::where('id', $lop->schoolyear)->where('status', 1)->get()->first();
            $lop->schoolyear = $schoolyear->name;
        }
        
        $day_date = date('Y-m-d');
        
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
        $value_tuan = '';
        
        $this->data['weeks_ky1'] = $weeks;
        
        $student = Student::where('lop', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
        
        $stt_start = $student->firstItem();
        
        foreach($student as $item){
            $item['stt'] = $stt_start++;
            $holy = Holymanagement::where('id', $item['holy'])->first();
            if(!empty($holy->name)){
                $item['holy'] = $holy->name;
            }else{
                $item['holy'] = '';
            }
            $item['birthday'] = date("d-m-Y", strtotime($item['birthday']));
        }
        
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
        
        $this->data['weeks_ky2'] = $weeks;
        
        $this->data['student'] = $student;
        
        $this->data['pagination'] = $student->links();
        
        $this->data['lop'] = $lop;
        
        return view()->first([
            'frontend.ketquadile',
            'frontend.layout.main',
        ], $this->data);
    }
}
