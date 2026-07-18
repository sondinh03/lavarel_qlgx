<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Parishioners;
use Spatie\SchemaOrg\Schema;
use App\Models\ParishManagement;
use App\Models\ParishGroup;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Association;
use App\Models\Holymanagement;
use App\Models\Ethnicmanagement;
use App\Models\Careermanagement;
use App\Models\Levelmanagement;
use App\Models\Positionmanagement;
use App\Models\Languagemanagement;
use App\Models\SacramentGiver;
use App\Models\Sponsor;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\SetAdmin;

class ParishionersController extends Controller
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
            $user = backpack_user();
            $userId = $user->id;
            $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
            
            $setadmin = SetAdmin::where('use', $user->id)->where('status', 1)->get()->first();
            
            if(!empty($decen) AND $decen->student == 1){
                $parishioners = Parishioners::where('id', $id)->where('pid', $decen->pid)->get()->first();
            }elseif(!empty($setadmin)){
                $parishioners = Parishioners::where('id', $id)->get()->first();
            }else{
                $parishioners = '';
            }
            
            //$parishioners = Parishioners::where('id', $id)->get()->first();
            if(!empty($parishioners)){
                $parishioners['edit'] = config('app.url') . '/admin/parishioners/'.$id.'/edit';
                $parishioners['bitich'] = url(slug($parishioners).$this->url_prefix . '/bitich=' . $id);
                $parishioners['lylichcanhan'] = url(slug($parishioners).$this->url_prefix . '/lylichcanhan=' . $id);
                $parishioners['giaygioithieugiaolyhonphoi'] = url(slug($parishioners).$this->url_prefix . '/giaygioithieugiaolyhonphoi=' . $id);
                $parishioners['giaygioithieuhonphoi'] = url(slug($parishioners).$this->url_prefix . '/giaygioithieuhonphoi=' . $id);
                $parishioners['giaydieutrahonphoi'] = url(slug($parishioners).$this->url_prefix . '/giaydieutrahonphoi=' . $id); 
                $parishioners['slug'] = url(slug($parishioners).$this->url_prefix); 
                    
                if($parishioners->sex == 0){
                    $parishioners['sex'] = 'Nữ';
                }else{
                    $parishioners['sex'] = 'Nam';
                }
                
                if($parishioners->assid != ''){
                    $associations = Association::where('id', $parishioners['assid'])->first();
                    $parishioners['assid'] = $associations->name;
                }else{
                    $parishioners['assid'] = '';
                }
                
                if(!empty($parishioners->paid)){
                    $parish = ParishGroup::where('id', $parishioners['paid'])->first();
                    $parishioners['paid'] = $parish->name . ', ';
                }else{
                    $parishioners['paid'] = '';
                }
                
                if(!empty($parishioners->pid)){
                    $parish_management = ParishManagement::where('id', $parishioners['pid'])->first();
                    $parishioners['pid'] = $parish_management->name . ', ';
                }else{
                    $parishioners['pid'] = '';
                }
                
                if(!empty($parishioners->deid)){
                    $deanery = Deanery::where('id', $parishioners['deid'])->first();
                    $parishioners['deid'] = $deanery->name . ', ';
                }else{
                    $parishioners['deid'] = '';
                }
                
                if(!empty($parishioners->did)){
                    $diocese = Diocese::where('id', $parishioners['did'])->first();
                    $parishioners['did'] = $diocese->name;
                }else{
                    $parishioners['did'] = '';
                }
                
                // địa chỉ
                if($parishioners->ward != ''){
                    $xaphuong = $this->GetXaTruQuan($parishioners->ward);
                    if(!empty($xaphuong)){
                        $parishioners['ward'] = $xaphuong['name'];
                    }else{
                        $parishioners['ward'] = '';
                    }
                }else{
                    $parishioners['ward'] = '';
                }
                
                if($parishioners->province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($parishioners->province);
                    $parishioners['province'] = $tinhthanh;
                }else{
                    $parishioners['province'] = '';
                }
                
                if($parishioners->resi_ward != ''){
                    $xaphuong = $this->GetXaTruQuan($parishioners->resi_ward);
                    if(!empty($xaphuong)){
                        $parishioners['resi_ward'] = $xaphuong['name'];
                    }else{
                        $parishioners['resi_ward'] = '';
                    }
                }else{
                    $parishioners['resi_ward'] = '';
                }
                
                if($parishioners->resi_province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($parishioners->resi_province);
                    $parishioners['resi_province'] = $tinhthanh;
                }else{
                    $parishioners['resi_province'] = '';
                }
                
                $holy = Holymanagement::where('id', $parishioners['holy'])->first();
                
                if(!empty($holy->name)){
                    $parishioners['holy'] = $holy->name;
                }else{
                    $parishioners['holy'] = '';
                }
                
                $ethnic = Ethnicmanagement::where('id', $parishioners['ethnic'])->first();
                
                if(!empty($ethnic->name)){
                    $parishioners['ethnic'] = $ethnic->name;
                }else{
                    $parishioners['ethnic'] = '';
                }
                
                $career = Careermanagement::where('id', $parishioners['career'])->first();
                
                if(!empty($career->name)){
                    $parishioners['career'] = $career->name;
                }else{
                    $parishioners['career'] = '';
                }
                
                $level = Levelmanagement::where('id', $parishioners['level'])->first();
                
                if(!empty($level->name)){
                    $parishioners['level'] = $level->name;
                }else{
                    $parishioners['level'] = '';
                }
                
                $position = Positionmanagement::where('id', $parishioners['position'])->first();
                
                if(!empty($position->name)){
                    $parishioners['position'] = $position->name;
                }else{
                    $parishioners['position'] = '';
                }
                
                $language = Languagemanagement::where('id', $parishioners['language'])->first();
                
                if(!empty($language->name)){
                    $parishioners['language'] = $language->name;
                }else{
                    $parishioners['language'] = '';
                }
                
                if(!empty($parishioners->birthday)){
                    $parishioners->birthday = date("d-m-Y", strtotime($parishioners->birthday));
                }
                
                if(!empty($parishioners->phone)){
                    $parishioners->phone = 0 . $parishioners->phone;
                }
                
                if(!empty($parishioners->baptism_date)){
                    $parishioners->baptism_date = date("d-m-Y", strtotime($parishioners->baptism_date));
                }
                
                if(!empty($parishioners->baptism_giver)){
                    $baptism_giver = SacramentGiver::where('id', '=', $parishioners->baptism_giver)->orderBy('id', 'ASC')->first();
                    $parishioners->baptism_giver = $baptism_giver->name;
                }
                
                if(!empty($parishioners->baptism_sponsor)){
                    $baptism_sponsor = Sponsor::where('id', '=', $parishioners->baptism_sponsor)->orderBy('id', 'ASC')->first();
                    $parishioners->baptism_sponsor = $baptism_sponsor->name;
                }
                
                if(!empty($parishioners->baptism_parish)){
                    $baptism_parish = ParishManagement::where('id', '=', $parishioners->baptism_parish)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $parishioners->baptism_parish = $baptism_parish->name . ', ';
                }
                
                if(!empty($parishioners->baptism_deanerys)){
                    $baptism_deanerys = Deanery::where('id', '=', $parishioners->baptism_deanerys)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $parishioners->baptism_deanerys = $baptism_deanerys->name . ', ';
                }
                
                if(!empty($parishioners->baptism_dioceses)){
                    $baptism_dioceses = Diocese::where('id', '=', $parishioners->baptism_dioceses)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $parishioners->baptism_dioceses = $baptism_dioceses->name;
                }
                
                if(!empty($parishioners->more_power_date)){
                    $parishioners->more_power_date = date("d-m-Y", strtotime($parishioners->more_power_date));
                }
                
                if(!empty($parishioners->more_power_giver)){
                    $more_power_giver = SacramentGiver::where('id', '=', $parishioners->more_power_giver)->orderBy('id', 'ASC')->first();
                    $parishioners->more_power_giver = $more_power_giver->name;
                }
                
                if(!empty($parishioners->more_power_sponsor)){
                    $more_power_sponsor = Sponsor::where('id', '=', $parishioners->more_power_sponsor)->orderBy('id', 'ASC')->first();
                    $parishioners->more_power_sponsor = $more_power_sponsor->name;
                }
                
                if(!empty($parishioners->more_power_parish)){
                    $more_power_parish = ParishManagement::where('status', '1')->where('id', $parishioners->more_power_parish)->orderBy('id', 'ASC')->first();
                    $parishioners->more_power_parish = $more_power_parish->name . ', ';
                }
                if(!empty($parishioners->more_power_deanerys)){
                    $more_power_deanerys = Deanery::where('status', '1')->where('id', $parishioners->more_power_deanerys)->orderBy('id', 'ASC')->first();
                    $parishioners->more_power_deanerys = $more_power_deanerys->name . ', ';
                }
                if(!empty($parishioners->more_power_dioceses)){
                    $more_power_dioceses = Diocese::where('status', '1')->where('id', $parishioners->more_power_dioceses)->orderBy('id', 'ASC')->first();
                    $parishioners->more_power_dioceses = $more_power_dioceses->name;
                }
                
                if(!empty($parishioners->communion_date)){
                    $parishioners->communion_date = date("d-m-Y", strtotime($parishioners->communion_date));
                }
                
                if(!empty($parishioners->communion_giver)){
                    $communion_giver = SacramentGiver::where('id', '=', $parishioners->communion_giver)->orderBy('id', 'ASC')->first();
                    $parishioners->communion_giver = $communion_giver->name;
                }
                
                if(!empty($parishioners->communion_parish)){
                    $communion_parish = ParishManagement::where('status', '1')->where('id', $parishioners->communion_parish)->orderBy('id', 'ASC')->first();
                    $parishioners->communion_parish = $communion_parish->name . ', ';
                }
                if(!empty($parishioners->communion_deanerys)){
                    $communion_deanerys = Deanery::where('status', '1')->where('id', $parishioners->communion_deanerys)->orderBy('id', 'ASC')->first();
                    $parishioners->communion_deanerys = $communion_deanerys->name . ', ';
                }
                if(!empty($parishioners->communion_dioceses)){
                    $communion_dioceses = Diocese::where('status', '1')->where('id', $parishioners->communion_dioceses)->orderBy('id', 'ASC')->first();
                    $parishioners->communion_dioceses = $communion_dioceses->name;
                }
                
                if(!empty($parishioners->anoint_date)){
                    $parishioners->anoint_date = date("d-m-Y", strtotime($parishioners->anoint_date));
                }
                
                if(!empty($parishioners->anoint_status)){
                    if($parishioners->anoint_status == 1){
                        $parishioners->anoint_status = 'Nguy tử';
                    }elseif($parishioners->anoint_status == 2){
                        $parishioners->anoint_status = 'Thông thường';
                    }else{
                        $parishioners->anoint_status = '';
                    }
                }
                
                if(!empty($parishioners->anoint_giver)){
                    $anoint_giver = SacramentGiver::where('id', '=', $parishioners->anoint_giver)->orderBy('id', 'ASC')->first();
                    $parishioners->anoint_giver = $anoint_giver->name;
                }
                
                if(!empty($parishioners->die_status)){
                    if($parishioners->die_status == 1){
                        $parishioners->die_status = '<i class="bi bi-check2"></i>';
                    }else{
                        $parishioners->die_status = '';
                    }
                }
                if(!empty($parishioners->die_time)){
                    $parishioners->die_time = date("d-m-Y", strtotime($parishioners->die_time));
                }
                
                if(!empty($parishioners->study)){
                    if($parishioners->study == 1){
                        $parishioners->study = 'Đang học';
                    }elseif($parishioners->study == 2){
                        $parishioners->study = 'Đã học xong';
                    }else{
                        $parishioners->study = 'Nghỉ học';
                    }
                }
                if(!empty($parishioners->new_convert)){
                    
                    if($parishioners->new_convert == 1){
                        $parishioners->new_convert = '<i class="bi bi-check2"></i>';
                    }else{
                        $parishioners->new_convert = '';
                    }
                }
                if(!empty($parishioners->married)){
                    if($parishioners->married == 1){
                        $parishioners->married = '<i class="bi bi-check2"></i>';
                    }else{
                        $parishioners->married = '';
                    }
                }
                if(!empty($parishioners->statistical)){
                    if($parishioners->statistical == 1){
                        $parishioners->statistical = '<i class="bi bi-check2"></i>';
                    }else{
                        $parishioners->statistical = '';
                    }
                }
                
                if(!empty($parishioners->die_status)){
                    if($parishioners->die_status == 1){
                        $parishioners->die_status = 'Đã mất';
                    }else{
                        $parishioners->die_status = '';
                    }
                }
                
                $this->data['parishioners'] = $parishioners;
                
                // return view()->first([
                //     'frontend.parishioners',
                //     'frontend.layout.main',
                // ], $this->data);
                return view('frontend.parishioners', $this->data);
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
