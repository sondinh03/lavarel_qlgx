<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Lop;
use App\Models\Block;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\KhaoKinh;
use App\Models\NamHoc;

class KetQuaKhaoKinhController extends Controller
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

        if(!empty($lop->schoolyear)){
            $schoolyear = NamHoc::where('id', $lop->schoolyear)->where('status', 1)->get()->first();
            $lop->schoolyear = $schoolyear->name;
        }
        
        if($lop->block != ''){
            $block = Block::where('id', $lop->block)->where('status', 1)->first();
            $lop['block'] = $block->name;
        }
        
        $student = Student::where('lop', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
        
        $stt_start = $student->firstItem();
        
        $stt1 = $stt2 = 0;
        //$hk1 = $hk2 = 0;
        $maxhk1 = $maxhk2 = array();
        foreach($student as $item){
            $item['stt'] = $stt_start++;
            
            $holy = Holymanagement::where('id', $item['holy'])->first();
            if(!empty($holy->name)){
                $item['holy'] = $holy->name;
            }else{
                $item['holy'] = '';
            }
            
            $item['birthday'] = date("d-m-Y", strtotime($item['birthday']));
            
            $hocsinh1 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 1)->where('status', 1)->orderBy('ngay', 'asc')->get();
            
            if(!empty($hocsinh1)){
                $hk1[] = $stt1 = count($hocsinh1);
                foreach($hocsinh1 as $row){
                    $maxhk1[] = $row->ngay;
                }
                
            }
            
            $item['hs1'] = $hocsinh1;
            
            $hocsinh2 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 2)->where('status', 1)->orderBy('ngay', 'asc')->get();
            if(!empty($hocsinh2)){
                $hk2[] = $stt2 = count($hocsinh2);
                foreach($hocsinh2 as $row){
                    $maxhk2[] = $row->ngay;
                }
            }
            $item['hs2'] = $hocsinh2;
        }
        
        if($stt1 == 0){
            $stt1 = 1;
        }else{
            $stt1 = $stt1 + 1;
        }
        $items_k1 = $stt1;
        if(!empty($maxhk1)){
            $this->data['hk1'] = max($maxhk1);
        }else{
            $this->data['hk1'] = 0;
        }
        
        $this->data['items_k1'] = $items_k1;
        
        if($stt2 == 0){
            $stt2 = 1;
        }else{
            $stt2 = $stt2 + 1;
        }
        $items_k2 = $stt2;
        
        //$this->data['hk2'] = max($hk2);
        if(!empty($maxhk2)){
            $this->data['hk2'] = max($maxhk2);
        }else{
            $this->data['hk2'] = 0;
        }
        
        $this->data['items_k2'] = $items_k2;
        
        $this->data['student'] = $student;
        
        $this->data['pagination'] = $student->links();
        
        $this->data['lop'] = $lop;
        
        return view()->first([
            'frontend.ketquakhaokinh',
            'frontend.layout.main',
        ], $this->data);
    }
}
