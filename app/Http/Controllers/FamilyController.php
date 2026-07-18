<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Family;
use App\Models\Marriage;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\ParishManagement;
use App\Models\ParishGroup;
use App\Models\Priest;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\Child;
use Illuminate\Support\Facades\Auth;
//use Spatie\SchemaOrg\Schema;

class FamilyController extends Controller
{
    public function __invoke(Request $request)
    {
        //
    }
    
    protected array $data = [];
    
    protected mixed $url_prefix = null;
    
    protected mixed $cache_time = 0;
    
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
            $family = Cache::remember("family_$id", $this->cache_time, function () use ($id) {
                return Family::findOrFail($id);
            });
            
            $family['sogiadinhconggiao'] = url(slug($family).$this->url_prefix . '/sogiadinhconggiao=' . $id);
            
            $family['edit'] = config('app.url') . '/admin/family/'.$id.'/edit';
            
            if($family->father != ''){
                $father = Parishioners::where('id', $family->father)->where('status', 1)->first();
                $holy = Holymanagement::where('id', $father['holy'])->first();
                if(!empty($holy->name)){
                    $father['holy'] = $holy->name;
                }else{
                    $father['holy'] = '';
                }
                $family['father'] = $father['holy'] . ' ' . $father['last_name'] . ' ' . $father['name'];
            }else{
                $family['father'] = '';
            }
            if($family->mother != ''){
                $mother = Parishioners::where('id', $family->mother)->where('status', 1)->first();
                $holy = Holymanagement::where('id', $mother['holy'])->first();
                if(!empty($holy->name)){
                    $mother['holy'] = $holy->name;
                }else{
                    $mother['holy'] = '';
                }
                $family['mother'] = $mother['holy'] . ' ' . $mother['last_name'] . ' ' . $mother['name'];
            }else{
                $family['mother'] = '';
            }
            
            if($family->paid != ''){
                $parish = ParishGroup::where('id', $family->paid)->first();
                $family['paid'] = 'Giáo họ ' . $parish->name . ', ';
            }else{
                $family['paid'] = '';
            }
            
            if($family->pid != ''){
                $parish_management = ParishManagement::where('id', $family->pid)->first();
                $family['pid'] = $parish_management->name . ', ';
            }else{
                $family['pid'] = '';
            }
            
            if($family->deid != ''){
                $deanery = Deanery::where('id', $family->deid)->first();
                $family['deid'] = $deanery->name . ', ';
            }else{
                $family['deid'] = '';
            }
            
            if($family->did != ''){
                $diocese = Diocese::where('id', $family->did)->first();
                $family['did'] = $diocese->name;
            }else{
                $family['did'] = '';
            }
            
            // địa chỉ
            if($family->origin != ''){
                $family['origin'] = $family['origin'] . ', ';
            }else{
                $family['origin'] = '';
            }
            if($family->ward != ''){
                $xaphuong = $this->GetXaTruQuan($family->ward);
                $family['ward'] = $xaphuong['name'] . ', ';
            }else{
                $family['ward'] = '';
            }
            
            if($family->province != ''){
                $tinhthanh = $this->GetTinhThanhQuan($family->province);
                $family['province'] = $tinhthanh;
            }else{
                $family['province'] = '';
            }
            
            $family_areas = DB::table('family_areas')
                ->where('status', '1')
                ->orderBy('id', 'ASC')
                ->get()
                ->toArray();
            $family_areas = array_values($family_areas);
            $family_areas = json_decode(json_encode($family_areas, true), true);
            $array_areas = array();
            foreach($family_areas as $item){
                $array_areas[$item['id']] = $item['name'];
            }
            if(!empty($family['dien'])){
                $family['dien'] = $array_areas[$family->dien];
            }
            
            if(!empty($family->phone)){
                $family['phone'] = 0 . $family->phone;
            }
            if(!empty($family->noio) AND $family->noio == 1){
                $family['noio'] = 'Đã chuyển đi xứ khác';
            }
            
            if(!empty($family->thongke) AND $family->thongke == 1){
                $family['thongke'] = 'Là gia đình không được thống kê';
            }
            
            if($family->id){
                $marriage = Marriage::where('id', $family->id)->orderBy('id', 'ASC')->first();
                if(!empty($marriage->date)){
                    $marriage->date = date("d-m-Y", strtotime($marriage->date));
                }
                if($marriage->marriage_address != ''){
                    $marriage['marriage_address'] = $marriage->marriage_address . ', ';
                }
                if($marriage->marriage_ward != ''){
                    $xaphuong = $this->GetXaTruQuan($marriage->marriage_ward);
                    $marriage['marriage_ward'] = $xaphuong['name'] . ', ';
                }else{
                    $marriage['marriage_ward'] = '';
                }
                if($marriage->marriage_province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($marriage->marriage_province);
                    $marriage['marriage_province'] = $tinhthanh;
                }else{
                    $marriage['marriage_province'] = '';
                }
                if(!empty($marriage->priest)){
                    $priest = Priest::where('id', '=', $marriage->priest)->orderBy('id', 'ASC')->first();
                    $marriage['priest'] = $priest->name;
                }
                if(!empty($marriage->tinhtrang)){
                    $array_tinhtrang = array(
                        '1' => 'Hợp pháp',
                        '2' => 'Hợp thức hóa',
                        '3' => 'Chuẩn',
                        '4' => 'Không theo phép đạo',
                        '5' => 'Ly thân',
                        '6' => 'Ly dị',
                        '7' => 'Đã được tháo gỡ',
                        '8' => 'Không xác định',
                    );
                    $marriage['tinhtrang'] = $array_tinhtrang[$marriage->tinhtrang];
                }else{
                    $marriage['tinhtrang'] = '';
                }
            }else{
                $marriage->date = '';
                $marriage['marriage_ward'] = '';
                $marriage['marriage_province'] = '';
                $marriage['priest'] = '';
                $marriage['tinhtrang'] = '';
            }
            
            $array_thanhvien = array();
            if($family->id){
                $children = Child::where('childrengable_id', $family->id)->where('childrengable_type', 'App\Models\Family')->get()->toArray();
                if(is_array($children)){
                    foreach($children as $child){
                        $thanhvien = Parishioners::where('id', $child['children_id'])->where('status', 1)->first();
                        $thanhvien['slug'] = url(slug($thanhvien).$this->url_prefix);
                        $holy = Holymanagement::where('id', $thanhvien['holy'])->first();
                        if(!empty($holy->name)){
                            $thanhvien['holy'] = $holy->name;
                        }else{
                            $thanhvien['holy'] = '';
                        }
                        $thanhvien['name'] = $thanhvien['holy'] . ' ' . $thanhvien['last_name'] . ' ' . $thanhvien['name'];
                        if(!empty($thanhvien->birthday)){
                            $thanhvien->birthday = date("d-m-Y", strtotime($thanhvien->birthday));
                        }
                        if($thanhvien->paid != ''){
                            $parish = ParishGroup::where('id', $thanhvien['paid'])->first();
                            $thanhvien['paid'] = $parish->name;
                        }else{
                            $thanhvien['paid'] = '';
                        }
                        if($thanhvien->pid != ''){
                            $parish_management = ParishManagement::where('id', $thanhvien['pid'])->first();
                            $thanhvien['pid'] = $parish_management->name;
                        }else{
                            $parishioners['pid'] = '';
                        }
                        
                        if($thanhvien->deid != ''){
                            $deanery = Deanery::where('id', $thanhvien['deid'])->first();
                            $thanhvien['deid'] = $deanery->name;
                        }else{
                            $thanhvien['deid'] = '';
                        }
                        
                        if($thanhvien->did != ''){
                            $diocese = Diocese::where('id', $thanhvien['did'])->first();
                            $thanhvien['did'] = $diocese->name;
                        }else{
                            $thanhvien['did'] = '';
                        }
                        
                        if($thanhvien->residence != ''){
                            $thanhvien['residence'] = $thanhvien['residence'] . ', ';
                        }
                        
                        if($thanhvien->resi_ward != ''){
                            $xaphuong = $this->GetXaTruQuan($thanhvien->resi_ward);
                            $thanhvien['resi_ward'] = $xaphuong['name'] . ', ';
                        }else{
                            $thanhvien['resi_ward'] = '';
                        }
                        
                        if($thanhvien->resi_province != ''){
                            $tinhthanh = $this->GetTinhThanhQuan($thanhvien->resi_province);
                            $thanhvien['resi_province'] = $tinhthanh;
                        }else{
                            $thanhvien['resi_province'] = '';
                        }
                        
                        if($thanhvien->sex == 0){
                            $thanhvien['sex'] = 'Nữ';
                        }else{
                            $thanhvien['sex'] = 'Nam';
                        }
                        $array_thanhvien[$thanhvien->id] = $thanhvien;
                    }
                }
            }
            
            $this->data['family'] = $family;
            
            $this->data['marriage'] = $marriage;
            
            $this->data['children'] = $array_thanhvien;
            
            return view()->first([
                'frontend.family',
                'frontend.layout.main',
            ], $this->data);
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
