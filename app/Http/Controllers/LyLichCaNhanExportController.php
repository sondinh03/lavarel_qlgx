<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord;
use App\Models\Parishioners;
use App\Models\ParishManagement;
use App\Models\Parish;
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
use App\Models\MarriageAnnouncement;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Family;
use App\Models\Marriage;
use App\Models\Priest;

class LyLichCaNhanExportController extends Controller
{
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
        $holy = Holymanagement::where('id', $parishioners['holy'])->first();
        if(!empty($holy->name)){
            $parishioners['holy'] = $holy->name;
        }else{
            $parishioners['holy'] = '';
        }
        if($parishioners->assid != ''){
            $associations = Association::where('id', $parishioners['assid'])->first();
            $parishioners['assid'] = $associations->name;
        }else{
            $parishioners['assid'] = '';
        }
        if($parishioners->paid != ''){
            $parish = Parish::where('id', $parishioners['paid'])->first();
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
        }else{
            $parishioners['origin'] = '';
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
            $parishioners['residence'] = $parishioners->residence . ',';
        }
        if($parishioners->resi_ward != ''){
            $xaphuong = $this->GetXaTruQuan($parishioners->resi_ward);
            $parishioners['resi_ward'] = $xaphuong['name'] . ',';
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
        if($parishioners->email != ''){
            $parishioners['email'] = 'Email: ' . $parishioners->email;
        }else{
            $parishioners['email'] = '';
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
        if(!empty($parishioners->married)){
            if($parishioners->married == 1){
                $parishioners->married = 'Đã lập gia đình';
            }else{
                $parishioners->married = 'Chưa lập gia đình';
            }
        }
        if(!empty($parishioners->die_time)){
            $parishioners->die_time = 'Qua đời: ' . date("d-m-Y", strtotime($parishioners->die_time));
        }else{
            $parishioners->die_time = '';
        }
        if(!empty($parishioners->die_burial)){
            $parishioners->die_burial = 'Nơi an táng: ' . $parishioners->die_burial;
        }else{
            $parishioners->die_burial = '';
        }
        if(!empty($parishioners->die_lottery)){
            $parishioners->die_lottery = 'Số sổ: ' . $parishioners->die_lottery;
        }else{
            $parishioners->die_lottery = '';
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
        if(!empty($parishioners->id)){
            $family = Family::where('father', $parishioners->id)->orWhere('mother', $parishioners->id)->where('status', '1')->orderBy('id', 'ASC')->first();
            if(!empty($family->origin)){
                $family['origin'] = $family->origin . ',';
            }else{
                $family['origin'] = '';
            }
            if(!empty($family->ward)){
                $xaphuong = $this->GetXaTruQuan($family->ward);
                $family['ward'] = $xaphuong['name'] . ',';
            }else{
                $family['ward'] = '';
            }
            if(!empty($family->province)){
                $tinhthanh = $this->GetTinhThanhQuan($family->province);
                $family['province'] = $tinhthanh;
            }else{
                $family['province'] = '';
            }
            if(!empty($family->paid)){
                $parish = Parish::where('id', $family['paid'])->first();
                $family['paid'] = ', Giáo họ ' . $parish->name;
            }else{
                $family['paid'] = '';
            }
            if(!empty($family->pid)){
                $parish_management = ParishManagement::where('id', $family['pid'])->first();
                $family['pid'] = ', ' .$parish_management->name;
                $family['giaoxu'] = $parish_management->name;
            }else{
                $family['pid'] = '';
                $family['giaoxu'] = '';
            }
            if(!empty($family->deid)){
                $deanery = Deanery::where('id', $family['deid'])->first();
                $family['deid'] = ', '.$deanery->name;
            }else{
                $family['deid'] = '';
            }
            if(!empty($family->did)){
                $diocese = Diocese::where('id', $family['did'])->first();
                $family['did'] = $diocese->name;
            }else{
                $family['did'] = '';
            }
            
            if(!empty($family->id)){
                $marriage = Marriage::where('id', $family->id)->orderBy('id', 'ASC')->first();
                
                if(!empty($marriage->date)){
                    $marriage['date'] = date("d-m-Y", strtotime($marriage->date));
                }else{
                    $marriage['date'] = '';
                }
                if(!empty($marriage->marriage_address)){
                    $marriage['marriage_address'] = $marriage->marriage_address . ', ';
                }else{
                    $marriage['marriage_address'] = '';
                }
                if(!empty($marriage->marriage_ward)){
                    $xaphuong = $this->GetXaTruQuan($marriage->marriage_ward);
                    $marriage['marriage_ward'] = $xaphuong['name'] . ', ';
                }else{
                    $marriage['marriage_ward'] = '';
                }
                if(!empty($marriage->marriage_province)){
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
                $marriage['date'] = '';
                $marriage['sohonphoi'] = '';
                $marriage['marriage_address'] = '';
                $marriage['marriage_ward'] = '';
                $marriage['marriage_province'] = '';
                $marriage['priest'] = '';
                $marriage['peopleone'] = '';
                $marriage['peopletwo'] = '';
                $marriage['tinhtrang'] = '';
            }
        }
        
        $templateProcessor = new TemplateProcessor('word-template/LyLichCaNhan.docx');
        $templateProcessor -> setValue('did', $parishioners->did);
        $templateProcessor -> setValue('deid', $parishioners->deid);
        $templateProcessor -> setValue('pid', $parishioners->pid);
        $templateProcessor -> setValue('giaoxu', $parishioners->giaoxu);
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
        $templateProcessor -> setValue('career', $parishioners->career);
        $templateProcessor -> setValue('level', $parishioners->level);
        $templateProcessor -> setValue('married', $parishioners->married);
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
        $templateProcessor -> setValue('die_time', $parishioners->die_time);
        $templateProcessor -> setValue('die_burial', $parishioners->die_burial);
        $templateProcessor -> setValue('die_lottery', $parishioners->die_lottery);
        $templateProcessor -> setValue('date', $marriage['date']);
        $templateProcessor -> setValue('sohonphoi', $marriage['sohonphoi']);
        $templateProcessor -> setValue('marriage_address', $marriage['marriage_address']);
        $templateProcessor -> setValue('marriage_ward', $marriage['marriage_ward']);
        $templateProcessor -> setValue('marriage_province', $marriage['marriage_province']);
        $templateProcessor -> setValue('priest', $marriage['priest']);
        $templateProcessor -> setValue('peopleone', $marriage['peopleone']);
        $templateProcessor -> setValue('peopletwo', $marriage['peopletwo']);
        $templateProcessor -> setValue('tinhtrang', $marriage['tinhtrang']);
        $templateProcessor -> setValue('day', date('d'));
        $templateProcessor -> setValue('month', date('m'));
        $templateProcessor -> setValue('year', date('Y'));
        $templateProcessor -> saveAs('LyLichCaNhan.docx');
        return  response()->download('LyLichCaNhan.docx')->deleteFileAfterSend(true);
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
