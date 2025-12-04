<?php

namespace App\Exports;

use App\Models\GiaoDan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithMapping;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;

use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class GiaoDanExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithMapping
{
    use RegistersEventListeners;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $giaodan = GiaoDan::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->first();
        return $giaodan;
    }
    
    public function title():string 
    {
        return 'Giáo Dân';
    }
    
    public function headings(): array
    {
        return[
            'ID CSDL',
            'Mã GD',
            'Giáo Họ',
            'Giáo Xứ',
            'Giáo hạt',
            'Giáo phận',
            'Hội Đoàn',
            'Tên Thánh',
            'Họ Tên Đệm',
            'Tên',
            'Phái',
            'Ngày Sinh',
            'Mã Nhận Dạng (CMND/CCCD)',
            'Tên Cha',
            'Tên Mẹ',
            'Số điện thoại',
            'Email',
            'Nguyên quán',
            'Xã / phường',
            'Tỉnh / TP',
            'Trú quán',
            'Xã / phường',
            'Tỉnh / TP',
            'Dân tộc',
            'Ngôn ngữ',
            'Trình độ',
            'Nghề nghiệp',
            'Chức vụ',
            'Trình độ chuyên môn',
            'Giáo dục',
            'Tân tòng',
            'Có gia đình',
            'Thống kê',
            'Mô tả thêm',
            'Ngày rửa tội',
            'Số rửa tội',
            'Người ban bí tích rửa tội',
            'Người đỡ đầu rửa tội',
            'Giáo xứ',
            'Giáo hạt',
            'Giáo phận',
            'Ngày thêm sức',
            'Số thêm sức',
            'Người ban bí tích thêm sức',
            'Người đỡ đầu thêm sức',
            'Giáo xứ',
            'Giáo hạt',
            'Giáo phận',    
            'Ngày rước lễ',
            'Số rước lễ',
            'Người ban bí tích rước lễ',
            'Giáo xứ',
            'Giáo hạt',
            'Giáo phận',
            'Ngày xức dầu',
            'Tình trạng xức dầu',
            'Người ban bí tích xức dầu',
            'Ghi chú',
            'Trạng thái sống còn (x là mất)',
            'Thời gian mất',
            'Số xổ mất',
            'Nơi qua đời',
            'Nơi an táng',
            'Ngày thêm dữ liệu',
            'Ngày cập nhật',
        ];
    }
    
    public function map($parishioners): array
    {
        //if(!empty($_POST)){
            //print_r($_POST);die('ok');die;
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->parish == 1){
                if($_POST['giaophan'] == $parishioners->did AND  $_POST['giaohat'] == $parishioners->deid AND $_POST['giaoxu'] == $parishioners->pid){
                    if(!empty($parishioners->paid)){
                        $paid = DB::table('parishs')
                        ->where('status', '1')
                        ->where('id', $parishioners->paid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->paid = $paid->name;
                    }
                    if(!empty($parishioners->pid)){
                        $paid = DB::table('parish_managements')
                        ->where('status', '1')
                        ->where('id', $parishioners->pid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->pid = $paid->name;
                    }
                    if(!empty($parishioners->deid)){
                        $deid = DB::table('deanerys')
                        ->where('status', '1')
                        ->where('id', $parishioners->deid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->deid = $deid->name;
                    }
                    if(!empty($parishioners->did)){
                        $did = DB::table('dioceses')
                        ->where('status', '1')
                        ->where('id', $parishioners->did)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->did = $did->name;
                    }
                    if(!empty($parishioners->assid)){
                        $assid = DB::table('associations')
                        ->where('status', '1')
                        ->where('id', $parishioners->assid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->assid = $assid->name;
                    }
                    if(!empty($parishioners->holy)){
                        $holy = DB::table('holymanagements')
                        ->where('id', $parishioners->holy)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->holy = $holy->name;
                    }
                    if(!empty($parishioners->sex)){
                        if($parishioners->sex == 1){
                            $parishioners->sex = 'Nam';
                        }else{
                            $parishioners->sex = 'Nữ';
                        }
                    }else{
                        $parishioners->sex = 'Nữ';
                    }
                    if(!empty($parishioners->birthday)){
                        $parishioners->birthday = date("d-m-Y", strtotime($parishioners->birthday));
                    }
                    
                    if(!empty($parishioners->phone)){
                        $parishioners->phone = 0 . $parishioners->phone;
                    }
                                        
                    @include(resource_path().'/cities/xa_phuong_thitran.php');
                    foreach($xa_phuong_thitran as $xp){
                        if($xp['xaid'] == $parishioners->ward){
                            $parishioners->ward = $xp['name'];
                        }
                        if($xp['xaid'] == $parishioners->resi_ward){
                            $parishioners->resi_ward = $xp['name'];
                        }
                    }
                    
                    @include(resource_path().'/cities/tinh_thanhpho.php');
                    if(!empty($parishioners->province)){
                        $parishioners->province = $tinh_thanhpho[$parishioners->province];
                    }
                    if(!empty($parishioners->resi_province)){
                        $parishioners->resi_province = $tinh_thanhpho[$parishioners->resi_province];
                    }
                    
                    if(!empty($parishioners->ethnic)){
                        $ethnic = DB::table('ethnicmanagements')
                        ->where('id', '=', $parishioners->ethnic)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->ethnic = $ethnic->name;
                    }
                    
                    if(!empty($parishioners->language)){
                        $language = DB::table('languagemanagements')
                        ->where('id', '=', $parishioners->language)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->language = $language->name;
                    }
                    
                    if(!empty($parishioners->level)){
                        $level = DB::table('levelmanagements')
                        ->where('id', '=', $parishioners->level)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->level = $level->name;
                    }
                    
                    if(!empty($parishioners->career)){
                        $career = DB::table('careermanagements')
                        ->where('id', '=', $parishioners->career)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->career = $career->name;
                    }
                    
                    if(!empty($parishioners->position)){
                        $position = DB::table('positionmanagements')
                        ->where('id', '=', $parishioners->position)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->position = $position->name;
                    }
                    if(!empty($parishioners->study)){
                        if($parishioners->study == 1){
                            $parishioners->study = 'Đang học';
                        }else{
                            $parishioners->study = 'Đã học xong';
                        }
                    }
                    
                    if(!empty($parishioners->new_convert)){
                        if($parishioners->new_convert == 1){
                            $parishioners->new_convert = 'x';
                        }else{
                            $parishioners->new_convert = '';
                        }
                    }
                    if(!empty($parishioners->married)){
                        if($parishioners->married == 1){
                            $parishioners->married = 'x';
                        }else{
                            $parishioners->married = '';
                        }
                    }
                    if(!empty($parishioners->statistical)){
                        if($parishioners->statistical == 1){
                            $parishioners->statistical = 'x';
                        }else{
                            $parishioners->statistical = '';
                        }
                    }
                    
                    if(!empty($parishioners->baptism_date)){
                        $parishioners->baptism_date = date("d-m-Y", strtotime($parishioners->baptism_date));
                    }
                    
                    if(!empty($parishioners->baptism_giver)){
                        $baptism_giver = DB::table('sacrament_givers')
                        ->where('id', '=', $parishioners->baptism_giver)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->baptism_giver = $baptism_giver->name;
                    }
                    
                    if(!empty($parishioners->baptism_sponsor)){
                        $baptism_sponsor = DB::table('sponsors')
                        ->where('id', '=', $parishioners->baptism_sponsor)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->baptism_sponsor = $baptism_sponsor->name;
                    }
                    
                    if(!empty($parishioners->baptism_parish)){
                        $baptism_parish = DB::table('parish_managements')
                        ->where('status', '1')
                        ->where('id', $parishioners->baptism_parish)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->baptism_parish = $baptism_parish->name;
                    }
                    if(!empty($parishioners->baptism_deanerys)){
                        $baptism_deanerys = DB::table('deanerys')
                        ->where('status', '1')
                        ->where('id', $parishioners->baptism_deanerys)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->baptism_deanerys = $baptism_deanerys->name;
                    }
                    if(!empty($parishioners->baptism_dioceses)){
                        $baptism_dioceses = DB::table('dioceses')
                        ->where('status', '1')
                        ->where('id', $parishioners->baptism_dioceses)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->baptism_dioceses = $baptism_dioceses->name;
                    }
                    
                    if(!empty($parishioners->more_power_date)){
                        $parishioners->more_power_date = date("d-m-Y", strtotime($parishioners->more_power_date));
                    }
                    
                    if(!empty($parishioners->more_power_giver)){
                        $more_power_giver = DB::table('sacrament_givers')
                        ->where('id', '=', $parishioners->more_power_giver)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->more_power_giver = $more_power_giver->name;
                    }
                    
                    if(!empty($parishioners->more_power_sponsor)){
                        $more_power_sponsor = DB::table('sponsors')
                        ->where('id', '=', $parishioners->more_power_sponsor)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->more_power_sponsor = $more_power_sponsor->name;
                    }
                    
                    if(!empty($parishioners->more_power_parish)){
                        $more_power_parish = DB::table('parish_managements')
                        ->where('status', '1')
                        ->where('id', $parishioners->more_power_parish)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->more_power_parish = $more_power_parish->name;
                    }
                    if(!empty($parishioners->more_power_deanerys)){
                        $more_power_deanerys = DB::table('deanerys')
                        ->where('status', '1')
                        ->where('id', $parishioners->more_power_deanerys)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->more_power_deanerys = $more_power_deanerys->name;
                    }
                    if(!empty($parishioners->more_power_dioceses)){
                        $more_power_dioceses = DB::table('dioceses')
                        ->where('status', '1')
                        ->where('id', $parishioners->more_power_dioceses)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->more_power_dioceses = $more_power_dioceses->name;
                    }
                    
                    if(!empty($parishioners->communion_date)){
                        $parishioners->communion_date = date("d-m-Y", strtotime($parishioners->communion_date));
                    }
                    
                    if(!empty($parishioners->communion_giver)){
                        $communion_giver = DB::table('sacrament_givers')
                        ->where('id', '=', $parishioners->communion_giver)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->communion_giver = $communion_giver->name;
                    }
                    
                    if(!empty($parishioners->communion_parish)){
                        $communion_parish = DB::table('parish_managements')
                        ->where('status', '1')
                        ->where('id', $parishioners->communion_parish)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->communion_parish = $communion_parish->name;
                    }
                    if(!empty($parishioners->communion_deanerys)){
                        $communion_deanerys = DB::table('deanerys')
                        ->where('status', '1')
                        ->where('id', $parishioners->communion_deanerys)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->communion_deanerys = $communion_deanerys->name;
                    }
                    if(!empty($parishioners->communion_dioceses)){
                        $communion_dioceses = DB::table('dioceses')
                        ->where('status', '1')
                        ->where('id', $parishioners->communion_dioceses)
                        ->orderBy('id', 'ASC')
                        ->first();
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
                        $anoint_giver = DB::table('sacrament_givers')
                        ->where('id', '=', $parishioners->anoint_giver)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $parishioners->anoint_giver = $anoint_giver->name;
                    }
                    
                    if(!empty($parishioners->die_status)){
                        if($parishioners->die_status == 1){
                            $parishioners->die_status = 'x';
                        }else{
                            $parishioners->die_status = '';
                        }
                    }
                    if(!empty($parishioners->die_time)){
                        $parishioners->die_time = date("d-m-Y", strtotime($parishioners->die_time));
                    }
                    
                    return [
                        $parishioners->id,
                        $parishioners->magd,
                        $parishioners->paid,
                        $parishioners->pid,
                        $parishioners->deid,
                        $parishioners->did,
                        $parishioners->assid,
                        $parishioners->holy,
                        $parishioners->last_name,
                        $parishioners->name,
                        $parishioners->sex,
                        $parishioners->birthday,
                        $parishioners->cccd,
                        $parishioners->father,
                        $parishioners->mother,
                        $parishioners->phone,
                        $parishioners->email,
                        $parishioners->origin,
                        $parishioners->ward,
                        $parishioners->province,
                        $parishioners->residence,
                        $parishioners->resi_ward,
                        $parishioners->resi_province,
                        $parishioners->ethnic,
                        $parishioners->language,
                        $parishioners->level,
                        $parishioners->career,
                        $parishioners->position,
                        $parishioners->professional_level,
                        $parishioners->study,
                        $parishioners->new_convert,
                        $parishioners->married,
                        $parishioners->statistical,
                        $parishioners->note,
                        $parishioners->baptism_date,
                        $parishioners->baptism_number,
                        $parishioners->baptism_giver,
                        $parishioners->baptism_sponsor,
                        $parishioners->baptism_parish,
                        $parishioners->baptism_deanerys,
                        $parishioners->baptism_dioceses,
                        $parishioners->more_power_date,
                        $parishioners->more_power_number,
                        $parishioners->more_power_giver,
                        $parishioners->more_power_sponsor,
                        $parishioners->more_power_parish,
                        $parishioners->more_power_deanerys,
                        $parishioners->more_power_dioceses,
                        $parishioners->communion_date,
                        $parishioners->communion_number,
                        $parishioners->communion_giver,
                        $parishioners->communion_parish,
                        $parishioners->communion_deanerys,
                        $parishioners->communion_dioceses,
                        $parishioners->anoint_date,
                        $parishioners->anoint_status,
                        $parishioners->anoint_giver,
                        $parishioners->anoint_note,
                        $parishioners->die_status,
                        $parishioners->die_time,
                        $parishioners->die_lottery,
                        $parishioners->die_death,
                        $parishioners->die_burial,
                        $parishioners->created_at,
                        $parishioners->updated_at
                    ];
                }else{
                    //return back()->withErrors('Xin lỗi, bạn không có quyền thực hiện điều này');
                    return [];
                }
            }else{
                return [];
                
                //return redirect()->back()->with('error', 'Có dữ liệu không hợp lệ');
                
                //return back()->withErrors('Xin lỗi, bạn không có quyền thực hiện điều này');
            }
        //}else{
        //    print_r($_POST);die;
        //}
    }
    
    public function styles(Worksheet $sheet) {        
        $sheet->getStyle('1')->applyFromArray([
            'font' => [
                'bold' => FALSE,
                'name'      =>  'Microsoft Sans Serif',
                'size'      =>  8.5,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE, // works
                    'argb' => 'EAF2FD', // doesn't work
                ],
            ],
        ]);
    }
    
    public static function afterSheet(AfterSheet $event)
    {
        $default_font_style = [
            'font' => ['name' => 'Microsoft Sans Serif', 'size' => 8.5]
        ];
        $active_sheet = $event->sheet->getDelegate();
        
        // Apply Style Arrays
        $active_sheet->getParent()->getDefaultStyle()->applyFromArray($default_font_style);
    }
}
