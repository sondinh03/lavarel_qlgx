<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\Parish;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\SacramentGiver;
use App\Models\Sponsor;
use App\Models\CatechismClass;
use App\Models\Block;

class ThuGioiThieuExportController extends Controller
{
    public function store($slug, $id)
    {
        $student = Student::where('id', $id)->where('status', 1)->orderBy('created_at', 'desc')->first();
        if(!empty($student->last_name)){
            $student->name = $student->last_name . ' ' . $student->name;
        }
        $holy = Holymanagement::where('id', $student['holy'])->first();
        if(!empty($holy->name)){
            $student->name = $holy->name . ' ' . $student->name;
        }
        
        if(!empty($student->birthday)){
            $student->birthday = date("d-m-Y", strtotime($student->birthday));
        }
        
        $lop = CatechismClass::where('id', $student->lop)->where('status', 1)->orderBy('created_at', 'desc')->first();
        
        if(!empty($lop->schoolyear)){
            $student->schoolyear = $lop->schoolyear;
        }else{
            $student->schoolyear = '';
        }
        
        if(!empty($lop->name)){
            $student->lop = $lop->name;
        }else{
            $student->lop = '';
        }
        
        if(!empty($lop->khoi)){
            $khoi = Block::where('id', $lop->khoi)->where('status', 1)->orderBy('created_at', 'desc')->first();
            $student->khoi = $khoi->name;
        }else{
            $student->khoi = '';
        }
        
        if($student->paid != ''){
            $parish = Parish::where('id', $student['paid'])->first();
            $student['paid'] = ', Giáo họ ' . $parish->name;
            $student['giaoho'] = $parish->name;
        }else{
            $student['paid'] = '';
            $student['giaoho'] = '';
        }
        if($student->pid != ''){
            $parish_management = ParishManagement::where('id', $student['pid'])->first();
            $student['pid'] = ', ' .$parish_management->name;
            $student['giaoxu'] = $parish_management->name;
        }else{
            $student['pid'] = '';
            $student['giaoxu'] = '';
        }
        if($student->deid != ''){
            $deanery = Deanery::where('id', $student['deid'])->first();
            $student['deid'] = ', '.$deanery->name;
            $student['giaohat'] = $deanery->name;;
        }else{
            $student['deid'] = '';
            $student['giaohat'] = '';
        }
        if($student->did != ''){
            $diocese = Diocese::where('id', $student['did'])->first();
            $student['did'] = $diocese->name;
            $student['giaophan'] = $diocese->name;
        }else{
            $student['did'] = '';
            $student['giaophan'] = '';
        }
        
        // địa chỉ
        if(!empty($student->origin)){
            $student['origin'] = $student->origin . ', ';
        }
        if(!empty($student->ward)){
            $xaphuong = $this->GetXaTruQuan($student->ward);
            $student['ward'] = $xaphuong['name'] . ', ';
        }else{
            $student['ward'] = '';
        }
        if(!empty($student->province)){
            $tinhthanh = $this->GetTinhThanhQuan($student->province);
            $student['province'] = $tinhthanh;
        }else{
            $student['province'] = '';
        }
        
        if(!empty($student->baptism_date)){
            $student->baptism_date = date("d-m-Y", strtotime($student->baptism_date));
        }
        if(!empty($student->baptism_giver)){
            $baptism_giver = SacramentGiver::where('id', '=', $student->baptism_giver)->orderBy('id', 'ASC')->first();
            $student->baptism_giver = $baptism_giver->name;
        }
        if(!empty($student->baptism_sponsor)){
            $baptism_sponsor = Sponsor::where('id', '=', $student->baptism_sponsor)->orderBy('id', 'ASC')->first();
            $student->baptism_sponsor = $baptism_sponsor->name;
        }
        if(!empty($student->baptism_parish)){
            $baptism_parish = ParishManagement::where('id', '=', $student->baptism_parish)->where('status', 1)->orderBy('id', 'ASC')->first();
            $student->baptism_parish = $baptism_parish->name . ', ';
        }
        if(!empty($student->baptism_deanerys)){
            $baptism_deanerys = Deanery::where('id', '=', $student->baptism_deanerys)->where('status', 1)->orderBy('id', 'ASC')->first();
            $student->baptism_deanerys = $baptism_deanerys->name . ', ';
        }
        if(!empty($student->baptism_dioceses)){
            $baptism_dioceses = Diocese::where('id', '=', $student->baptism_dioceses)->where('status', 1)->orderBy('id', 'ASC')->first();
            $student->baptism_dioceses = $baptism_dioceses->name;
        }
        if(!empty($student->more_power_date)){
            $student->more_power_date = date("d-m-Y", strtotime($student->more_power_date));
        }
        if(!empty($student->more_power_giver)){
            $more_power_giver = SacramentGiver::where('id', '=', $student->more_power_giver)->orderBy('id', 'ASC')->first();
            $student->more_power_giver = $more_power_giver->name;
        }
        if(!empty($student->more_power_sponsor)){
            $more_power_sponsor = Sponsor::where('id', '=', $student->more_power_sponsor)->orderBy('id', 'ASC')->first();
            $student->more_power_sponsor = $more_power_sponsor->name;
        }
        if(!empty($student->more_power_parish)){
            $more_power_parish = ParishManagement::where('status', '1')->where('id', $student->more_power_parish)->orderBy('id', 'ASC')->first();
            $student->more_power_parish = $more_power_parish->name . ', ';
        }
        if(!empty($student->more_power_deanerys)){
            $more_power_deanerys = Deanery::where('status', '1')->where('id', $student->more_power_deanerys)->orderBy('id', 'ASC')->first();
            $student->more_power_deanerys = $more_power_deanerys->name . ', ';
        }
        if(!empty($student->more_power_dioceses)){
            $more_power_dioceses = Diocese::where('status', '1')->where('id', $student->more_power_dioceses)->orderBy('id', 'ASC')->first();
            $student->more_power_dioceses = $more_power_dioceses->name;
        }
        
        $templateProcessor = new TemplateProcessor('word-template/ThuGioiThieuHocGL.docx');
        $templateProcessor -> setValue('did', $student->did);
        $templateProcessor -> setValue('deid', $student->deid);
        $templateProcessor -> setValue('pid', $student->pid);
        $templateProcessor -> setValue('paid', $student->paid);
        
        $templateProcessor -> setValue('giaoho', $student->giaoho);
        $templateProcessor -> setValue('giaoxu', $student->giaoxu);
        $templateProcessor -> setValue('giaohat', $student->giaohat);
        $templateProcessor -> setValue('giaophan', $student->giaophan);
        
        $templateProcessor -> setValue('name', $student->name);
        $templateProcessor -> setValue('birthday', $student->birthday);
        $templateProcessor -> setValue('mahv', $student->mahv);
        $templateProcessor -> setValue('lop', $student->lop);
        $templateProcessor -> setValue('khoi', $student->khoi);
        $templateProcessor -> setValue('schoolyear', $student->schoolyear);
        
        $templateProcessor -> setValue('origin', $student->origin);
        $templateProcessor -> setValue('ward', $student->ward);
        $templateProcessor -> setValue('province', $student->province);
        
        $templateProcessor -> setValue('father', $student->father);
        $templateProcessor -> setValue('mother', $student->mother);
        
        $templateProcessor -> setValue('baptism_date', $student->baptism_date);
        $templateProcessor -> setValue('baptism_number', $student->baptism_number);
        $templateProcessor -> setValue('baptism_giver', $student->baptism_giver);
        $templateProcessor -> setValue('baptism_sponsor', $student->baptism_sponsor);
        $templateProcessor -> setValue('baptism_dioceses', $student->baptism_dioceses);
        $templateProcessor -> setValue('baptism_deanerys', $student->baptism_deanerys);
        $templateProcessor -> setValue('baptism_parish', $student->baptism_parish);
        $templateProcessor -> setValue('more_power_date', $student->more_power_date);
        $templateProcessor -> setValue('more_power_number', $student->more_power_number);
        $templateProcessor -> setValue('more_power_giver', $student->more_power_giver);
        $templateProcessor -> setValue('more_power_sponsor', $student->more_power_sponsor);
        $templateProcessor -> setValue('more_power_dioceses', $student->more_power_dioceses);
        $templateProcessor -> setValue('more_power_deanerys', $student->more_power_deanerys);
        $templateProcessor -> setValue('more_power_parish', $student->more_power_parish);
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('ThuGioiThieuHocGL.docx');
        return  response()->download('ThuGioiThieuHocGL.docx')->deleteFileAfterSend(true);
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
