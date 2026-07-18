<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MarriageAnnouncement;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Priest;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Parishioners;
use App\Models\ParishGroup;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Holymanagement;

class RaoHonPhoiNamController extends Controller
{
    public function store($slug, $id)
    {
        $marriageannouncement = MarriageAnnouncement::where('id', $id)->orderBy('created_at', 'desc')->first();
        if(!empty($marriageannouncement->priest)){
            $priest = Priest::where('id', $marriageannouncement->priest)->first();
            $marriageannouncement->priest = $priest->name;
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
                if($giaodan_nu->residence != ''){
                    $nu['residence'] = $giaodan_nu->residence . ', ';
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
            }
            if($nu->parishsold != ''){
                $parish = ParishGroup::where('id', $nu['parishsold'])->first();
                $nu['parishsold'] = $parish->name;
            }else{
                $nu['parishsold'] = '';
            }
            if($nu->parishmanagementsold != ''){
                $parish_management = ParishManagement::where('id', $nu['parishmanagementsold'])->first();
                $nu['parishmanagementsold'] = $parish_management->name;
            }else{
                $nu['parishmanagementsold'] = '';
            }
            if($nu->deanerysold != ''){
                $deanery = Deanery::where('id', $nu['deanerysold'])->first();
                $nu['deanerysold'] = $deanery->name . ', ';
            }else{
                $nu['deanerysold'] = '';
            }
            if($nu->diocesesold != ''){
                $diocese = Diocese::where('id', $nu['diocesesold'])->first();
                $nu['diocesesold'] = $diocese->name;
            }else{
                $nu['diocesesold'] = '';
            }
            
            if($nu->parishs != ''){
                $parish = ParishGroup::where('id', $nu['parishs'])->first();
                $nu['parishs'] = $parish->name . ', ';
            }else{
                $nu['parishs'] = '';
            }
            if($nu->parishmanagements != ''){
                $parish_management = ParishManagement::where('id', $nu['parishmanagements'])->first();
                $nu['parishmanagements'] = $parish_management->name . ', ';
                $nu['giaoxu'] = $parish_management->name;
            }else{
                $nu['parishmanagements'] = '';
                $nu['giaoxu'] = '';
            }
            if($nu->deanerys != ''){
                $deanery = Deanery::where('id', $nu['deanerys'])->first();
                $nu['deanerys'] = $deanery->name . ', ';
                $nu['giaohat'] = $deanery->name;
            }else{
                $nu['deanerys'] = '';
                $nu['giaohat'] = '';
            }
            if($nu->dioceses != ''){
                $diocese = Diocese::where('id', $nu['dioceses'])->first();
                $nu['dioceses'] = $diocese->name;
                $nu['giaophan'] = $diocese->name;
            }else{
                $nu['dioceses'] = '';
                $nu['giaophan'] = '';
            }
            
            if($nu->parishsbefore != ''){
                $parish = ParishGroup::where('id', $nu['parishsbefore'])->first();
                $nu['parishsbefore'] = $parish->name . ', ';
            }else{
                $nu['parishsbefore'] = '';
            }
            if($nu->parishmanagementsbefore != ''){
                $parish_management = ParishManagement::where('id', $nu['parishmanagementsbefore'])->first();
                $nu['parishmanagementsbefore'] = $parish_management->name;
            }else{
                $nu['parishmanagementsbefore'] = '';
            }
            if($nu->deanerysbefore != ''){
                $deanery = Deanery::where('id', $nu['deanerysbefore'])->first();
                $nu['deanerysbefore'] = $deanery->name . ', ';
            }else{
                $nu['deanerysbefore'] = '';
            }
            if($nu->diocesesbefore != ''){
                $diocese = Diocese::where('id', $nu['diocesesbefore'])->first();
                $nu['diocesesbefore'] = $diocese->name;
            }else{
                $nu['diocesesbefore'] = '';
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
            }
            if($nam->parishsold != ''){
                $parish = ParishGroup::where('id', $nam['parishsold'])->first();
                $nam['parishsold'] = $parish->name . ', ';
            }else{
                $nam['parishsold'] = '';
            }
            if($nam->parishmanagementsold != ''){
                $parish_management = ParishManagement::where('id', $nam['parishmanagementsold'])->first();
                $nam['parishmanagementsold'] = $parish_management->name;
            }else{
                $nam['parishmanagementsold'] = '';
            }
            if($nam->deanerysold != ''){
                $deanery = Deanery::where('id', $nam['deanerysold'])->first();
                $nam['deanerysold'] = $deanery->name . ', ';
            }else{
                $nam['deanerysold'] = '';
            }
            if($nam->diocesesold != ''){
                $diocese = Diocese::where('id', $nam['diocesesold'])->first();
                $nam['diocesesold'] = $diocese->name;
            }else{
                $nam['diocesesold'] = '';
            }
            
            if($nam->parishs != ''){
                $parish = ParishGroup::where('id', $nam['parishs'])->first();
                $nam['parishs'] = $parish->name . ', ';
            }else{
                $nam['parishs'] = '';
            }
            if($nam->parishmanagements != ''){
                $parish_management = ParishManagement::where('id', $nam['parishmanagements'])->first();
                $nam['parishmanagements'] = $parish_management->name . ', ';
                $nam['giaoxu'] = $parish_management->name;
            }else{
                $nam['parishmanagements'] = '';
                $nam['giaoxu'] = '';
            }
            if($nam->deanerys != ''){
                $deanery = Deanery::where('id', $nam['deanerys'])->first();
                $nam['deanerys'] = $deanery->name . ', ';
                $nam['giaohat'] = $deanery->name;
            }else{
                $nam['deanerys'] = '';
                $nam['giaohat'] = '';
            }
            if($nam->dioceses != ''){
                $diocese = Diocese::where('id', $nam['dioceses'])->first();
                $nam['dioceses'] = $diocese->name;
                $nam['giaophan'] = $diocese->name;
            }else{
                $nam['dioceses'] = '';
                $nam['giaophan'] = '';
            }
            
            if($nam->parishsbefore != ''){
                $parish = ParishGroup::where('id', $nam['parishsbefore'])->first();
                $nam['parishsbefore'] = $parish->name . ', ';
            }else{
                $nam['parishsbefore'] = '';
            }
            if($nam->parishmanagementsbefore != ''){
                $parish_management = ParishManagement::where('id', $nam['parishmanagementsbefore'])->first();
                $nam['parishmanagementsbefore'] = $parish_management->name;
            }else{
                $nam['parishmanagementsbefore'] = '';
            }
            if($nam->deanerysbefore != ''){
                $deanery = Deanery::where('id', $nam['deanerysbefore'])->first();
                $nam['deanerysbefore'] = $deanery->name . ', ';
            }else{
                $nam['deanerysbefore'] = '';
            }
            if($nam->diocesesbefore != ''){
                $diocese = Diocese::where('id', $nam['diocesesbefore'])->first();
                $nam['diocesesbefore'] = $diocese->name;
            }else{
                $nam['diocesesbefore'] = '';
            }
        }
        
        $templateProcessor = new TemplateProcessor('word-template/RaoHonPhoi_Nam.docx');
        $templateProcessor -> setValue('nu', $nu->name_nu);
        $templateProcessor -> setValue('nu_age', $nu->age);
        $templateProcessor -> setValue('nu_phone', $nu->phone);
        $templateProcessor -> setValue('nu_father', $nu->father);
        $templateProcessor -> setValue('nu_mother', $nu->mother);
        $templateProcessor -> setValue('nu_parishmanagementsold', $nu->parishmanagementsold);
        $templateProcessor -> setValue('nu_diocesesold', $nu->diocesesold);
        $templateProcessor -> setValue('nu_parishmanagements', $nu->parishmanagements);
        $templateProcessor -> setValue('nu_dioceses', $nu->dioceses);
        $templateProcessor -> setValue('nu_parishmanagementsbefore', $nu->parishmanagementsbefore);
        $templateProcessor -> setValue('nu_diocesesbefore', $nu->diocesesbefore);
        $templateProcessor -> setValue('nu_residence', $nu->residence);
        $templateProcessor -> setValue('nu_resi_ward', $nu->resi_ward);
        $templateProcessor -> setValue('nu_resi_province', $nu->resi_province);
        $templateProcessor -> setValue('nam', $nam->name_nam);
        $templateProcessor -> setValue('nam_age', $nam->age);
        $templateProcessor -> setValue('nam_phone', $nam->phone);
        $templateProcessor -> setValue('nam_father', $nam->father);
        $templateProcessor -> setValue('nam_mother', $nam->mother);
        $templateProcessor -> setValue('nam_parishmanagementsold', $nam->parishmanagementsold);
        $templateProcessor -> setValue('nam_diocesesold', $nam->diocesesold);
        $templateProcessor -> setValue('nam_parishmanagements', $nam->parishmanagements);
        $templateProcessor -> setValue('nam_dioceses', $nam->dioceses);
        $templateProcessor -> setValue('nam_parishmanagementsbefore', $nam->parishmanagementsbefore);
        $templateProcessor -> setValue('nam_diocesesbefore', $nam->diocesesbefore);
        $templateProcessor -> setValue('nam_residence', $nam->residence);
        $templateProcessor -> setValue('nam_resi_ward', $nam->resi_ward);
        $templateProcessor -> setValue('nam_resi_province', $nam->resi_province);
        $templateProcessor -> setValue('nam_giaoxu', $nam->giaoxu);
        $templateProcessor -> setValue('nam_giaohat', $nam->giaohat);
        $templateProcessor -> setValue('nam_giaophan', $nam->giaophan);
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('RaoHonPhoi_Nam.docx');
        return  response()->download('RaoHonPhoi_Nam.docx')->deleteFileAfterSend(true);
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
