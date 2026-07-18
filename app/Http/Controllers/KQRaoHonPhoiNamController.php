<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarriageAnnouncement;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Priest;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Parishioners;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Holymanagement;

class KQRaoHonPhoiNamController extends Controller
{
    public function store($slug, $id)
    {
        $marriageannouncement = MarriageAnnouncement::where('id', $id)->orderBy('created_at', 'desc')->first();
        if(!empty($marriageannouncement->priest)){
            $priest = Priest::where('id', $marriageannouncement->priest)->first();
            $marriageannouncement->priest = $priest->name;
        }
        if(!empty($marriageannouncement->announcements_one)){
            $marriageannouncement['announcements_one'] = date("d-m-Y", strtotime($marriageannouncement->announcements_one));
        }
        if(!empty($marriageannouncement->announcements_two)){
            $marriageannouncement['announcements_two'] = date("d-m-Y", strtotime($marriageannouncement->announcements_two));
        }
        if(!empty($marriageannouncement->announcements_three)){
            $marriageannouncement['announcements_three'] = date("d-m-Y", strtotime($marriageannouncement->announcements_three));
        }
        if(!empty($marriageannouncement->id)){
            $nu = MarriageAnnouncementParishioners::where('idannouncement', $marriageannouncement->id)->where('sex', 0)->orderBy('created_at', 'desc')->first();
            if(!empty($nu->idgiaodan)){
                $giaodan_nu = Parishioners::where('id', $nu->idgiaodan)->where('status', 1)->orderBy('created_at', 'desc')->first();
                if(!empty($giaodan_nu->last_name)){
                    $giaodan_nu->name = $giaodan_nu->last_name . ' ' . $giaodan_nu->name;
                }
                if(!empty($giaodan_nu->holy)){
                    $holy = Holymanagement::where('id', $giaodan_nu['holy'])->first();
                    if(!empty($holy->name)){
                        $nu['holy'] = $holy->name;
                    }else{
                        $nu['holy'] = '';
                    }
                }
                if(!empty($giaodan_nu->name)){
                    $nu['name_nu'] = $nu['holy'] . ' ' . $giaodan_nu->name;
                }
                if(!empty($giaodan_nu->phone)){
                    $nu['phone'] = 0 . $giaodan_nu['phone'];
                    if(  preg_match( '/^(\d{4})(\d{3})(\d{3})$/', $nu['phone'],  $matches ) ) {
                        $phone = $matches[1] . '.' .$matches[2] . '.' . $matches[3];
                    }
                    $nu['phone'] = $phone;
                }else{
                    $nu['phone'] = '';
                }
                if(!empty($giaodan_nu->birthday)){
                    $nu['birthday'] = date("d-m-Y", strtotime($giaodan_nu->birthday));
                    
                    $birthDate = $nu['birthday']; //"12/17/1983";
                    //explode the date to get month, day and year
                    $birthDate = explode("-", $birthDate);
                    //get age from date or birthdate
                    $nu['age'] = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
                        ? ((date("Y") - $birthDate[2]) - 1)
                        : (date("Y") - $birthDate[2]));
                }
                if(!empty($giaodan_nu->father)){
                    $nu['father'] = $giaodan_nu->father;
                }
                if(!empty($giaodan_nu->mother)){
                    $nu['mother'] = $giaodan_nu->mother;
                }
                
                if($giaodan_nu->origin != ''){
                    $nu['origin'] = $giaodan_nu->origin . ', ';
                }
                if($giaodan_nu->ward != ''){
                    $xaphuong = $this->GetXaTruQuan($giaodan_nu->ward);
                    $nu['ward'] = $xaphuong['name'] . ', ';
                }else{
                    $nu['ward'] = '';
                }
                if($giaodan_nu->province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($giaodan_nu->province);
                    $nu['province'] = $tinhthanh;
                }else{
                    $nu['province'] = '';
                }
                
                if($giaodan_nu->residence != ''){
                    $nu['residence'] = $giaodan_nu->residence . ', ';
                }else{
                    $nu['residence'] = '';
                }
                if($giaodan_nu->resi_ward != ''){
                    $xaphuong = $this->GetXaTruQuan($giaodan_nu->resi_ward);
                    $nu['resi_ward'] = $xaphuong['name'] . ', ';
                }else{
                    $nu['resi_ward'] = '';
                }
                if($giaodan_nu->resi_province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($giaodan_nu->resi_province);
                    $nu['resi_province'] = $tinhthanh;
                }else{
                    $nu['resi_province'] = '';
                }
                
                if($giaodan_nu->pid != ''){
                    $parish_management = ParishManagement::where('id', $giaodan_nu['pid'])->first();
                    $nu['pid'] = ', ' .$parish_management->name;
                    $nu['giaoxu'] = $parish_management->name;
                }else{
                    $nu['pid'] = '';
                    $nu['giaoxu'] = '';
                }
                if($giaodan_nu->deid != ''){
                    $deanery = Deanery::where('id', $giaodan_nu['deid'])->first();
                    $nu['deid'] = ', '.$deanery->name;
                    $nu['giaohat'] = $deanery->name;
                }else{
                    $nu['deid'] = '';
                    $nu['giaohat'] = '';
                }
                if($giaodan_nu->did != ''){
                    $diocese = Diocese::where('id', $giaodan_nu['did'])->first();
                    $nu['did'] = $diocese->name;
                    $nu['giaophan'] = $diocese->name;
                }else{
                    $nu['did'] = '';
                    $nu['giaophan'] = '';
                }
                
                if(!empty($giaodan_nu->baptism_date)){
                    $nu['baptism_date'] = date("d-m-Y", strtotime($giaodan_nu->baptism_date));
                }else{
                    $nu['baptism_date'] = '';
                }
                if(!empty($giaodan_nu->baptism_number)){
                    $nu['baptism_number'] = $giaodan_nu->baptism_number;
                }else{
                    $nu['baptism_number'] = '';
                }
                if(!empty($giaodan_nu->baptism_parish)){
                    $baptism_parish = ParishManagement::where('id', '=', $giaodan_nu->baptism_parish)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $nu['baptism_parish'] = $baptism_parish->name;
                }else{
                    $nu['baptism_parish'] = '';
                }
                if(!empty($giaodan_nu->more_power_date)){
                    $nu['more_power_date'] = date("d-m-Y", strtotime($giaodan_nu->more_power_date));
                }else{
                    $nu['more_power_date'] = '';
                }
                if(!empty($giaodan_nu->more_power_parish)){
                    $more_power_parish = ParishManagement::where('status', '1')->where('id', $giaodan_nu->more_power_parish)->orderBy('id', 'ASC')->first();
                    $nu['more_power_parish'] = $more_power_parish->name;
                }else{
                    $nu['more_power_parish'] = '';
                }
            }
        }
        if(!empty($marriageannouncement->id)){
            $nam = MarriageAnnouncementParishioners::where('idannouncement', $marriageannouncement->id)->where('sex', 1)->orderBy('created_at', 'desc')->first();
            if(!empty($nam->idgiaodan)){
                $giaodan_nam = Parishioners::where('id', $nam->idgiaodan)->where('status', 1)->orderBy('created_at', 'desc')->first();
                if(!empty($giaodan_nam->last_name)){
                    $giaodan_nam->name = $giaodan_nam->last_name . ' ' . $giaodan_nam->name;
                }
                if(!empty($giaodan_nam->holy)){
                    $holy = Holymanagement::where('id', $giaodan_nam['holy'])->first();
                    if(!empty($holy->name)){
                        $nam['holy'] = $holy->name;
                    }else{
                        $nam['holy'] = '';
                    }
                }
                if(!empty($giaodan_nam->name)){
                    $nam['name_nam'] = $nam['holy'] . ' ' . $giaodan_nam->name;
                }
                if(!empty($giaodan_nam->phone)){
                    $nam['phone'] = 0 . $giaodan_nam['phone'];
                    if(  preg_match( '/^(\d{4})(\d{3})(\d{3})$/', $nam['phone'],  $matches ) ) {
                        $phone = $matches[1] . '.' .$matches[2] . '.' . $matches[3];
                    }
                    $nam['phone'] = $phone;
                }else{
                    $nam['phone'] = '';
                }
                if(!empty($giaodan_nam->birthday)){
                    $nam['birthday'] = date("d-m-Y", strtotime($giaodan_nam->birthday));
                    
                    $birthDate = $nam['birthday']; //"12/17/1983";
                    //explode the date to get month, day and year
                    $birthDate = explode("-", $birthDate);
                    //get age from date or birthdate
                    $nam['age'] = (date("md", date("U", mktime(0, 0, 0, $birthDate[0], $birthDate[1], $birthDate[2]))) > date("md")
                        ? ((date("Y") - $birthDate[2]) - 1)
                        : (date("Y") - $birthDate[2]));
                }
                if(!empty($giaodan_nam->father)){
                    $nam['father'] = $giaodan_nam->father;
                }
                if(!empty($giaodan_nam->mother)){
                    $nam['mother'] = $giaodan_nam->mother;
                }
                
                if($giaodan_nam->origin != ''){
                    $nam['origin'] = $giaodan_nam->origin . ', ';
                }
                if($giaodan_nam->ward != ''){
                    $xaphuong = $this->GetXaTruQuan($giaodan_nam->ward);
                    $nam['ward'] = $xaphuong['name'] . ', ';
                }else{
                    $nam['ward'] = '';
                }
                if($giaodan_nam->province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($giaodan_nam->province);
                    $nam['province'] = $tinhthanh;
                }else{
                    $nam['province'] = '';
                }
                
                if($giaodan_nam->residence != ''){
                    $nam['residence'] = $giaodan_nam->residence . ', ';
                }
                if($giaodan_nam->resi_ward != ''){
                    $xaphuong = $this->GetXaTruQuan($giaodan_nam->resi_ward);
                    $nam['resi_ward'] = $xaphuong['name'] . ', ';
                }else{
                    $nam['resi_ward'] = '';
                }
                if($giaodan_nam->resi_province != ''){
                    $tinhthanh = $this->GetTinhThanhQuan($giaodan_nam->resi_province);
                    $nam['resi_province'] = $tinhthanh;
                }else{
                    $nam['resi_province'] = '';
                }
                
                if($giaodan_nam->pid != ''){
                    $parish_management = ParishManagement::where('id', $giaodan_nam['pid'])->first();
                    $nam['pid'] = ', ' .$parish_management->name;
                    $nam['giaoxu'] = $parish_management->name;
                }else{
                    $nam['pid'] = '';
                    $nam['giaoxu'] = '';
                }
                if($giaodan_nam->deid != ''){
                    $deanery = Deanery::where('id', $giaodan_nam['deid'])->first();
                    $nam['deid'] = ', '.$deanery->name;
                    $nam['giaohat'] = $deanery->name;
                }else{
                    $nam['deid'] = '';
                    $nam['giaohat'] = '';
                }
                if($giaodan_nam->did != ''){
                    $diocese = Diocese::where('id', $giaodan_nam['did'])->first();
                    $nam['did'] = $diocese->name;
                    $nam['giaophan'] = $diocese->name;
                }else{
                    $nam['did'] = '';
                    $nam['giaophan'] = '';
                }
                
                if(!empty($giaodan_nam->baptism_date)){
                    $nam['baptism_date'] = date("d-m-Y", strtotime($giaodan_nam->baptism_date));
                }else{
                    $nam['baptism_date'] = '';
                }
                if(!empty($giaodan_nam->baptism_number)){
                    $nam['baptism_number'] = $giaodan_nam->baptism_number;
                }else{
                    $nam['baptism_number'] = '';
                }
                if(!empty($giaodan_nam->baptism_parish)){
                    $baptism_parish = ParishManagement::where('id', '=', $giaodan_nam->baptism_parish)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $nam['baptism_parish'] = $baptism_parish->name;
                }else{
                    $nam['baptism_parish'] = '';
                }
                if(!empty($giaodan_nam->more_power_date)){
                    $nam['more_power_date'] = date("d-m-Y", strtotime($giaodan_nam->more_power_date));
                }else{
                    $nam['more_power_date'] = '';
                }
                if(!empty($giaodan_nam->more_power_parish)){
                    $more_power_parish = ParishManagement::where('status', '1')->where('id', $giaodan_nam->more_power_parish)->orderBy('id', 'ASC')->first();
                    $nam['more_power_parish'] = $more_power_parish->name;
                }else{
                    $nam['more_power_parish'] = '';
                }
            }
        }
        
        $templateProcessor = new TemplateProcessor('word-template/KQRaoHonPhoi_Nam.docx');
        $templateProcessor -> setValue('nu', $nu->name_nu);
        $templateProcessor -> setValue('nu_age', $nu->age);
        $templateProcessor -> setValue('nu_phone', $nu->phone);
        $templateProcessor -> setValue('nu_father', $nu->father);
        $templateProcessor -> setValue('nu_mother', $nu->mother);
        $templateProcessor -> setValue('nu_birthday', $nu->birthday);
        
        $templateProcessor -> setValue('nu_origin', $nu->origin);
        $templateProcessor -> setValue('nu_ward', $nu->ward);
        $templateProcessor -> setValue('nu_province', $nu->province);
        
        $templateProcessor -> setValue('nu_residence', $nu->residence);
        $templateProcessor -> setValue('nu_resi_ward', $nu->resi_ward);
        $templateProcessor -> setValue('nu_resi_province', $nu->resi_province);
        
        $templateProcessor -> setValue('nu_baptism_date', $nu->baptism_date);
        $templateProcessor -> setValue('nu_baptism_parish', $nu->baptism_parish);
        $templateProcessor -> setValue('nu_baptism_number', $nu->baptism_number);
        
        $templateProcessor -> setValue('nu_more_power_date', $nu->more_power_date);
        $templateProcessor -> setValue('nu_more_power_parish', $nu->more_power_parish);
        $templateProcessor -> setValue('nu_pid', $nu->pid);
        $templateProcessor -> setValue('nu_deid', $nu->deid);
        $templateProcessor -> setValue('nu_did', $nu->did);
        $templateProcessor -> setValue('nu_giaophan', $nu->giaophan);
        $templateProcessor -> setValue('nu_giaohat', $nu->giaohat);
        $templateProcessor -> setValue('nu_giaoxu', $nu->giaoxu);
        
        $templateProcessor -> setValue('linhmuc', $marriageannouncement->priest);
        
        $templateProcessor -> setValue('nam', $nam->name_nam);
        $templateProcessor -> setValue('nam_age', $nam->age);
        $templateProcessor -> setValue('nam_phone', $nam->phone);
        $templateProcessor -> setValue('nam_father', $nam->father);
        $templateProcessor -> setValue('nam_mother', $nam->mother);
        $templateProcessor -> setValue('nam_birthday', $nam->birthday);
        
        $templateProcessor -> setValue('nam_origin', $nam->origin);
        $templateProcessor -> setValue('nam_ward', $nam->ward);
        $templateProcessor -> setValue('nam_province', $nam->province);
        
        $templateProcessor -> setValue('nam_residence', $nam->residence);
        $templateProcessor -> setValue('nam_resi_ward', $nam->resi_ward);
        $templateProcessor -> setValue('nam_resi_province', $nam->resi_province);
        
        $templateProcessor -> setValue('nam_baptism_date', $nam->baptism_date);
        $templateProcessor -> setValue('nam_baptism_parish', $nam->baptism_parish);
        $templateProcessor -> setValue('nam_baptism_number', $nam->baptism_number);
        
        $templateProcessor -> setValue('nam_more_power_date', $nam->more_power_date);
        $templateProcessor -> setValue('nam_more_power_parish', $nam->more_power_parish);
        $templateProcessor -> setValue('nam_pid', $nam->pid);
        $templateProcessor -> setValue('nam_deid', $nam->deid);
        $templateProcessor -> setValue('nam_did', $nam->did);
        $templateProcessor -> setValue('nam_giaophan', $nam->giaophan);
        $templateProcessor -> setValue('nam_giaohat', $nam->giaohat);
        $templateProcessor -> setValue('nam_giaoxu', $nam->giaoxu);
        
        $templateProcessor -> setValue('announcements_one', $marriageannouncement->announcements_one);
        $templateProcessor -> setValue('announcements_two', $marriageannouncement->announcements_two);
        $templateProcessor -> setValue('announcements_three', $marriageannouncement->announcements_three);
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('KQRaoHonPhoi_Nam.docx');
        return  response()->download('KQRaoHonPhoi_Nam.docx')->deleteFileAfterSend(true);
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
