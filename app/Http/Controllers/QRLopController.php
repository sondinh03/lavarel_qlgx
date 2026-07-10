<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Core\DateTime;
use BaconQrCode\Encoder\QrCode;
use Validator;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\CatechismClass;
use App\Models\Block;
use App\Models\Parish;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Slug;

class QRLopController extends Controller
{
    protected array $data = [];
    
    protected mixed $url_prefix = null;
    
    protected mixed $cache_time = 0;
    
    protected mixed $per_page = 10;
    
    private $assets;
    
    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    /**
     * Display a listing of the submitInfoQr.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfoQRLop(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
        
        if ($validator->passes()) {
            $id = $request->id;
            
            $noidung = array();
            
            $loptoi = CatechismClass::where('id', $id)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
            if(!empty($loptoi->block)){
                $blocks = Block::where('id', $loptoi->block)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
                $loptoi->khoi = $blocks->name;
            }
            
            $data = Student::where('lop', $id)->where('status', 1)->orderBy('id', 'ASC')->get();
            
            foreach($data as $item){
                if(!empty($item->last_name)){
                    $item->name = $item->last_name . ' ' . $item->name;
                }
                if($item->holy){
                    $holy = Holymanagement::where('id', $item->holy)->first();
                    if(!empty($holy->name)){
                        $item->name = $holy->name . ' ' . $item->name;
                    }else{
                        $item->name = '';
                    }
                }else{
                    $item->holy = '';
                }
                
                if($item->pid != ''){
                    $pid = ParishManagement::where('id', $item['pid'])->first();
                    $item->pid = $pid->name;
                }else{
                    $item->pid = '';
                }
                
                if($item->lop){
                    $lop = CatechismClass::where('id', $item->lop)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
                    $item->lop = $lop->name;
                    if(!empty($lop->block)){
                        $block = Block::where('id', $lop->block)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
                        $item->khoi = $block->name;
                    }
                }
                
                $item->slug = url(slug($item).$this->url_prefix);
                $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::backgroundColor(255, 255, 255)->color(0, 0, 0)->size(50)->generate($item->slug);
                
                $qrCode = html_entity_decode($qr);
                
                $item->qr = $qrCode;
            }
            $noidung['thanhvien'] = $data;
            $noidung['lophoc'] = $loptoi;
            //$noidung['danhsach'] = $data;
            
            return response()->json($noidung);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
