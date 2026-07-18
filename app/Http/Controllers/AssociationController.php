<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Association;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use Illuminate\Database\Query\Builder;

class AssociationController extends Controller
{
    public function __invoke(Request $request)
    {
        //
    }
    
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
    
    public function show($id): View
    {
        \Assets::add('fontawesome');
        
        if (Auth::check()) {
            $user = backpack_user();
            $userId = $user->id;
            if(!empty($user->id)){
                $decen = Decen::where('use', $user->id )->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1){
                    $association = Association::where('id', $id)->where('pid', $decen->pid)->orderBy('created_at', 'desc')->get()->first();
                }elseif($user->id < 3){
                    $association = Association::where('id', $id)->orderBy('created_at', 'desc')->get()->first();
                }
                
                if(!empty($association)){
                    if($association->pid != ''){
                        $parish_management = ParishManagement::where('id', $association['pid'])->get()->first();
                        $association['pid'] = $parish_management->name . ', ';
                    }else{
                        $association['pid'] = '';
                    }
                    
                    if($association->deid != ''){
                        $deanery = Deanery::where('id', $association['deid'])->first();
                        $association['deid'] = $deanery->name . ', ';
                    }else{
                        $association['deid'] = '';
                    }
                    
                    if($association->did != ''){
                        $diocese = Diocese::where('id', $association['did'])->first();
                        $association['did'] = $diocese->name;
                    }else{
                        $association['did'] = '';
                    }
                    
                    $array_thanhvien = array();
                    if(!empty($association->id)){
                        if($user->id < 3){
                            $thanhvien = Parishioners::where('assid', $association->id)->where('status', 1)->orderBy('name', 'ASC')->paginate($this->per_page)->withQueryString();
                        }else{
                            $thanhvien = Parishioners::where('assid', $association->id)->where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'ASC')->paginate($this->per_page)->withQueryString();
                        }
                        $stt_start = $thanhvien->firstItem();
                        foreach($thanhvien as $item){
                            $item['stt'] = $stt_start++;
                            
                            $item['slug'] = url(slug($item).$this->url_prefix);
                            
                            $holy = Holymanagement::where('id', $item->holy)->first();
                            
                            if(!empty($holy->name)){
                                $item['holy'] = $holy->name;
                            }else{
                                $item['holy'] = '';
                            }
                            
                            if($item->sex == 0){
                                $item['sex'] = 'Nữ';
                            }else{
                                $item['sex'] = 'Nam';
                            }
                            
                            if(!empty($item->birthday)){
                                $item->birthday = date("d-m-Y", strtotime($item->birthday));
                            }
                            
                            if($item->paid != ''){
                                $parish = ParishGroup::where('id', $item['paid'])->first();
                                if(!empty($parish)){
                                    $item['paid'] = $parish->name . ', ';
                                }else{
                                    $item['paid'] = '';
                                }
                            }else{
                                $item['paid'] = '';
                            }
                            
                            if($item->pid != ''){
                                $parish_management = ParishManagement::where('id', $item['pid'])->first();
                                $item['pid'] = $parish_management->name . ', ';
                            }else{
                                $item['pid'] = '';
                            }
                            
                            if($item->deid != ''){
                                $deanery = Deanery::where('id', $item['deid'])->first();
                                $item['deid'] = $deanery->name . ', ';
                            }else{
                                $item['deid'] = '';
                            }
                            
                            if($item->did != ''){
                                $diocese = Diocese::where('id', $item['did'])->first();
                                $item['did'] = $diocese->name;
                            }else{
                                $item['did'] = '';
                            }
                            
                            if($item->residence != ''){
                                $item['residence'] = $item->residence . ', ';
                            }else{
                                $item['residence'] = '';
                            }
                            
                            if(!empty($item->resi_ward)){
                                $xaphuong = $this->GetXaTruQuan($item->resi_ward);
                                if(!empty($xaphuong)){
                                    $item['resi_ward'] = $xaphuong['name'] . ', ';
                                }else{
                                    $item['resi_ward'] = '';
                                }
                            }else{
                                $item['resi_ward'] = '';
                            }
                            if(!empty($item->resi_province)){
                                $tinhthanh = $this->GetTinhThanhQuan($item->resi_province);
                                if(!empty($tinhthanh)){
                                    $item['resi_province'] = $tinhthanh;
                                }else{
                                    $item['resi_province'] = '';
                                }
                            }else{
                                $item['resi_province'] = '';
                            }
                            $array_thanhvien[$item['stt']] = $item;
                        }
                    }
                    
                    $this->data['thanhvien'] = $array_thanhvien;
                    
                    $this->data['association'] = $association;
                    
                    return view()->first([
                        'frontend.association',
                        'frontend.layout.main',
                    ], $this->data);
                }
            }else{
                return view()->first([
                    'frontend.association',
                    'frontend.layout.main',
                ], $this->data);
            }
            
        }else{
            return view('home');   
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
}
