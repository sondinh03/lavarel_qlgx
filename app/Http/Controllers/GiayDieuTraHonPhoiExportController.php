<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\SacramentGiver;
use App\Models\Sponsor;
use PhpOffice\PhpWord\TemplateProcessor;

class GiayDieuTraHonPhoiExportController extends Controller
{
    public function store($slug, $id)
    {
        $parishioners = Parishioners::where('id', $id)->orderBy('created_at', 'desc')->first();
        if(!empty($parishioners->last_name)){
            $parishioners->name = $parishioners->last_name . ' ' . $parishioners->name;
        }
        $holy = Holymanagement::where('id', $parishioners['holy'])->first();
        if(!empty($holy->name)){
            $parishioners['holy'] = $holy->name;
        }else{
            $parishioners['holy'] = '';
        }
        if($parishioners->paid != ''){
            $parish = ParishGroup::where('id', $parishioners['paid'])->first();
            $parishioners['paid'] = ', Giáo họ ' . $parish->name;
            $parishioners['giaoho'] = $parish->name;
        }else{
            $parishioners['paid'] = '';
            $parishioners['giaoho'] = '';
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
            $parishioners['giaohat'] = '';
        }else{
            $parishioners['deid'] = '';
            $parishioners['giaohat'] = '';
        }
        if($parishioners->did != ''){
            $diocese = Diocese::where('id', $parishioners['did'])->first();
            $parishioners['did'] = $diocese->name;
            $parishioners['giaophan'] = $diocese->name;
        }else{
            $parishioners['did'] = '';
            $parishioners['giaophan'] = '';
        }
        
        if($parishioners->sex == 0){
            $parishioners['sex'] = 'Chị ';
        }else{
            $parishioners['sex'] = 'Anh ';
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
        if($parishioners->residence != ''){
            $parishioners['residence'] = $parishioners->residence . ', ';
        }
        if($parishioners->resi_ward != ''){
            $xaphuong = $this->GetXaTruQuan($parishioners->resi_ward);
            $parishioners['resi_ward'] = $xaphuong['name'] . ', ';
        }else{
            $parishioners['resi_ward'] = '';
        }
        if($parishioners->resi_province != ''){
            $tinhthanh = $this->GetTinhThanhQuan($parishioners->resi_province);
            $parishioners['resi_province'] = $tinhthanh;
        }else{
            $parishioners['resi_province'] = '';
        }
        if(!empty($parishioners->birthday)){
            $parishioners->birthday = date("d-m-Y", strtotime($parishioners->birthday));
        }
        if($parishioners->phone != ''){
            $parishioners['phone'] = 'Điện Thoại: 0' . $parishioners->phone;
        }else{
            $parishioners['phone'] = '';
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
        
        $templateProcessor = new TemplateProcessor('word-template/DieutraHonPhoi.docx');
        $templateProcessor -> setValue('did', $parishioners->did);
        $templateProcessor -> setValue('deid', $parishioners->deid);
        $templateProcessor -> setValue('pid', $parishioners->pid);
        $templateProcessor -> setValue('paid', $parishioners->paid);
        $templateProcessor -> setValue('holy', $parishioners->holy);
        $templateProcessor -> setValue('id', $parishioners->id);
        $templateProcessor -> setValue('name', $parishioners->name);
        $templateProcessor -> setValue('birthday', $parishioners->birthday);
        $templateProcessor -> setValue('sex', $parishioners->sex);
        $templateProcessor -> setValue('email', $parishioners->email);
        $templateProcessor -> setValue('origin', $parishioners->origin);
        $templateProcessor -> setValue('ward', $parishioners->ward);
        $templateProcessor -> setValue('province', $parishioners->province);
        $templateProcessor -> setValue('residence', $parishioners->residence);
        $templateProcessor -> setValue('resi_ward', $parishioners->resi_ward);
        $templateProcessor -> setValue('resi_province', $parishioners->resi_province);
        $templateProcessor -> setValue('father', $parishioners->father);
        $templateProcessor -> setValue('mother', $parishioners->mother);
        $templateProcessor -> setValue('phone', $parishioners->phone);
        $templateProcessor -> setValue('giaoho', $parishioners->giaoho);
        $templateProcessor -> setValue('giaoxu', $parishioners->giaoxu);
        $templateProcessor -> setValue('giaohat', $parishioners->giaohat);
        $templateProcessor -> setValue('giaophan', $parishioners->giaophan);
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
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('DieutraHonPhoi.docx');
        return  response()->download('DieutraHonPhoi.docx')->deleteFileAfterSend(true);
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
