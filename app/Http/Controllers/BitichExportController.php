<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord;
use App\Models\Parishioners;
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

class BitichExportController extends Controller
{
    public function index()
    {
    }    
    public function show($id)
    {
    }    
    public function store($slug, $id)
    {
        $parishioners = Parishioners::where('id', $id)->orderBy('created_at', 'desc')->first();      
        if(!empty($parishioners->last_name)){
            $parishioners->name = $parishioners->last_name . ' ' . $parishioners->name;
        }
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
        if($parishioners->paid != ''){
            $parish = ParishGroup::where('id', $parishioners['paid'])->first();
            $parishioners['paid'] = ', Giáo họ ' . $parish->name;
        }else{
            $parishioners['paid'] = '';
        }        
        if($parishioners->pid != ''){
            $parish_management = ParishManagement::where('id', $parishioners['pid'])->first();
            $parishioners['pid'] = ', ' .$parish_management->name;
            $parishioners['giaoxu'] = $parish_management->name;
        }else{
            $parishioners['pid'] = '';
            $parishioners['giaoxu'] = '';
        }        
        if($parishioners->deid != ''){
            $deanery = Deanery::where('id', $parishioners['deid'])->first();
            $parishioners['deid'] = ', '.$deanery->name;
        }else{
            $parishioners['deid'] = '';
        }        
        if($parishioners->did != ''){
            $diocese = Diocese::where('id', $parishioners['did'])->first();
            $parishioners['did'] = $diocese->name;
        }else{
            $parishioners['did'] = '';
        }        
        // địa chỉ
        if($parishioners->origin != ''){
            $parishioners['origin'] = $parishioners->origin . ',';
        }        
        if($parishioners->ward != ''){
            $xaphuong = $this->GetXaTruQuan($parishioners->ward);
            $parishioners['ward'] = $xaphuong['name'] . ',';
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
            $parishioners['resi_ward'] = $xaphuong['name'];
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
        $templateProcessor = new TemplateProcessor('word-template/BiTich.docx');
        $templateProcessor -> setValue('did', $parishioners->did);
        $templateProcessor -> setValue('deid', $parishioners->deid);
        $templateProcessor -> setValue('pid', $parishioners->pid);
        $templateProcessor -> setValue('giaoxu', $parishioners->giaoxu);
        $templateProcessor -> setValue('paid', $parishioners->paid);
        $templateProcessor -> setValue('holy', $parishioners->holy);
        $templateProcessor -> setValue('id', $parishioners->id);
        $templateProcessor -> setValue('name', $parishioners->name);
        $templateProcessor -> setValue('birthday', $parishioners->birthday);
        $templateProcessor -> setValue('origin', $parishioners->origin);
        $templateProcessor -> setValue('ward', $parishioners->ward);
        $templateProcessor -> setValue('province', $parishioners->province);
        $templateProcessor -> setValue('father', $parishioners->father);
        $templateProcessor -> setValue('mother', $parishioners->mother);
        $templateProcessor -> setValue('baptism_date', $parishioners->baptism_date);
        $templateProcessor -> setValue('baptism_number', $parishioners->baptism_number);
        $templateProcessor -> setValue('baptism_giver', $parishioners->baptism_giver);
        $templateProcessor -> setValue('baptism_sponsor', $parishioners->baptism_sponsor);
        $templateProcessor -> setValue('baptism_dioceses', $parishioners->baptism_dioceses);
        $templateProcessor -> setValue('baptism_deanerys', $parishioners->baptism_deanerys);
        $templateProcessor -> setValue('baptism_parish', $parishioners->baptism_parish);
        $templateProcessor -> setValue('more_power_date', $parishioners->more_power_date);
        $templateProcessor -> setValue('more_power_number', $parishioners->more_power_number);
        $templateProcessor -> setValue('more_power_giver', $parishioners->more_power_giver);
        $templateProcessor -> setValue('more_power_sponsor', $parishioners->more_power_sponsor);
        $templateProcessor -> setValue('more_power_dioceses', $parishioners->more_power_dioceses);
        $templateProcessor -> setValue('more_power_deanerys', $parishioners->more_power_deanerys);
        $templateProcessor -> setValue('more_power_parish', $parishioners->more_power_parish);
        $templateProcessor -> setValue('communion_date', $parishioners->communion_date);
        $templateProcessor -> setValue('communion_number', $parishioners->communion_number);
        $templateProcessor -> setValue('communion_giver', $parishioners->communion_giver);
        $templateProcessor -> setValue('communion_dioceses', $parishioners->communion_dioceses);
        $templateProcessor -> setValue('communion_deanerys', $parishioners->communion_deanerys);
        $templateProcessor -> setValue('communion_parish', $parishioners->communion_parish);
        $templateProcessor -> setValue('anoint_date', $parishioners->anoint_date);
        $templateProcessor -> setValue('anoint_status', $parishioners->anoint_status);
        $templateProcessor -> setValue('anoint_giver', $parishioners->anoint_giver);
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('BiTich.docx');
        return  response()->download('BiTich.docx')->deleteFileAfterSend(true);
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
