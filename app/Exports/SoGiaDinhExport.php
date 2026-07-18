<?php

namespace App\Exports;

use App\Models\Family;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use Illuminate\Support\Facades\DB;
use App\Models\Marriage;
use App\Models\Priest;
use App\Models\Child;
use App\Models\ParishGroup;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\SacramentGiver;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class SoGiaDinhExport implements FromCollection, WithTitle, WithMapping, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    use RegistersEventListeners;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $layurl = explode('/', $_SERVER['REQUEST_URI']);
        if(!empty($layurl['2'])){
            $layid = explode('=', $layurl['2']);
            if(!empty($layid[1])){
                $id = $layid[1];
                return $child = DB::table('family')
                    ->where('id', $id)
                    ->where('status', 1)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }else{
                return back()->withStatus('Không tồn tại dữ liệu');
            }
        }else{
            return back()->withStatus('Không tồn tại dữ liệu');
        }
        //return Family::all();
    }
    public function title():string
    {
        return 'Sổ Gia Đình';
    }
    
    public function headings(): array
    {
        return [
            [
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
            ],
            [
                '   ',
                '   ',
                '   ',
                'SỔ GIA ĐÌNH CÔNG GIÁO - GIÁO XỨ',
                '   ',
                '   ',
                '   ',
            ],
            [
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
                '   ',
            ],
        ];
    }
    
    public function map($row): array
    {
        $user = backpack_user();
        $userId = $user->id;
        $decen = Decen::where('use', $userId)->where('pid', $row->pid)->where('status', '1')->get()->first();
        if(!empty($decen) AND $decen->parish == 1){
            $boy = Parishioners::where('id', $row->father)->where('status', 1)->orderBy('created_at', 'desc')->first();
            if(!empty($boy->father) AND !empty($boy->mother)){
                $boy['fathermother'] = $boy->father . ', ' . $boy->mother;
            }elseif(!empty($boy->father)){
                $boy['fathermother'] = $boy->father;
            }elseif(!empty($boy->mother)){
                $boy['fathermother'] = $boy->mother;
            }else{
                $boy['fathermother'] = '';
            }
            
            if(!empty($boy->last_name)){
                $boy->name = $boy->last_name . ' ' . $boy->name;
            }
            
            $holy = Holymanagement::where('id', $boy['holy'])->first();
            if(!empty($holy->name)){                    
                $boy['holy'] = $holy->name;
            }else{
                $boy['holy'] = '';
            }
            if(!empty($boy->birthday)){
                $boy['birthday'] = date("d-m-Y", strtotime($boy->birthday));
            }
            if(!empty($boy->baptism_date)){
                $boy['baptism_date'] = date("d-m-Y", strtotime($boy->baptism_date));
            }
            if(!empty($boy->more_power_date)){
                $boy['more_power_date'] = date("d-m-Y", strtotime($boy->more_power_date));
            }
            if(!empty($body->communion_date)){
                $boy['communion_date'] = date("d-m-Y", strtotime($boy->communion_date));
            }
            if($boy->sex == 0){
                $boy['sex'] = 'Nữ';
            }else{
                $boy['sex'] = 'Nam';
            }
            if($boy->id){
                $family = Family::where('father', $boy->id)->orderBy('created_at', 'desc')->first();
                $house = Marriage::where('idfamily', $family->id)->first();
                $boy['date'] = date("d-m-Y", strtotime($boy->date));
            }else{
                $boy['date'] = '';
            }                    
            
            $girl = Parishioners::where('id', $row->mother)->where('status', 1)->orderBy('created_at', 'desc')->first();
            if(!empty($girl->father) AND !empty($girl->mother)){
                $girl['fathermother'] = $girl->father . ', ' . $girl->mother;
            }elseif(!empty($boy->father)){
                $girl['fathermother'] = $girl->father;
            }elseif(!empty($boy->mother)){
                $girl['fathermother'] = $girl->mother;
            }else{
                $girl['fathermother'] = '';
            }
            
            if(!empty($girl->last_name)){
                $girl->name = $girl->last_name . ' ' . $girl->name;
            }
            
            $holy = Holymanagement::where('id', $girl['holy'])->first();        
            if(!empty($holy->name)){
                $girl['holy'] = $holy->name;
            }else{
                $girl['holy'] = '';
            }
            
            if(!empty($girl->birthday)){
                $girl['birthday'] = date("d-m-Y", strtotime($girl->birthday));
            }
            if(!empty($girl->baptism_date)){
                $girl['baptism_date'] = date("d-m-Y", strtotime($girl->baptism_date));
            }
            if(!empty($girl->more_power_date)){
                $girl['more_power_date'] = date("d-m-Y", strtotime($girl->more_power_date));
            }
            if(!empty($girl->communion_date)){
                $girl['communion_date'] = date("d-m-Y", strtotime($girl->communion_date));
            }
            if($girl->sex == 0){
                $girl['sex'] = 'Nữ';
            }else{
                $girl['sex'] = 'Nam';
            }
            if($girl->id){
                $family = Family::where('mother', $girl->id)->orderBy('created_at', 'desc')->first();
                $house = Marriage::where('idfamily', $family->id)->first();
                $girl['date'] = date("d-m-Y", strtotime($house->date));
            }else{
                $girl['date'] = '';
            }
            $boy['house'] = 'Cha';
            $girl['house'] = 'Mẹ';
            /*
            if($row->idhouse != '' AND $row->idhouse == 1){
                $boy['house'] = 'Cha';
                $girl['house'] = 'Mẹ';
            }else{
                $boy['house'] = '';
                $girl['house'] = '';
            }
            */
            /*
            if($row->idhouse == '' AND $row->idhouse == 0){
                $girl['house'] = 'Mẹ';
            }else{
                $girl['house'] = '';
            }
            */
            
            $array_thanhvien = array();
            if(!empty($family->id)){
                $children = Child::where('childrengable_id', $family->id)->where('childrengable_type', 'App\Models\Family')->get()->toArray();
                if(is_array($children)){
                    foreach($children as $child){
                        $thanhvien = Parishioners::where('id', $child['children_id'])->where('status', 1)->first();
                        if(!empty($thanhvien->last_name)){
                            $thanhvien->name = $thanhvien->last_name . ' ' . $thanhvien->name;
                        }
                        if(!empty($thanhvien->father) AND !empty($thanhvien->mother)){
                            $thanhvien['fathermother'] = $thanhvien->father . ', ' . $thanhvien->mother;
                        }elseif(!empty($thanhvien->father)){
                            $thanhvien['fathermother'] = $thanhvien->father;
                        }elseif(!empty($thanhvien->mother)){
                            $thanhvien['fathermother'] = $thanhvien->mother;
                        }else{
                            $thanhvien['fathermother'] = '';
                        }
                        $holy = Holymanagement::where('id', $thanhvien['holy'])->first();
                        if(!empty($holy->name)){
                            $thanhvien['holy'] = $holy->name;
                        }else{
                            $thanhvien['holy'] = '';
                        }
                        if(!empty($thanhvien->birthday)){
                            $thanhvien['birthday'] = date("d-m-Y", strtotime($thanhvien->birthday));
                        }
                        if(!empty($thanhvien->baptism_date)){
                            $girl['baptism_date'] = date("d-m-Y", strtotime($thanhvien->baptism_date));
                        }
                        if(!empty($thanhvien->more_power_date)){
                            $thanhvien['more_power_date'] = date("d-m-Y", strtotime($thanhvien->more_power_date));
                        }
                        if(!empty($thanhvien->communion_date)){
                            $thanhvien['communion_date'] = date("d-m-Y", strtotime($thanhvien->communion_date));
                        }
                        $thanhvien['name'] = $thanhvien['holy'] . ' ' . $thanhvien['name'];
                        if(!empty($thanhvien->birthday)){
                            $thanhvien->birthday = date("d-m-Y", strtotime($thanhvien->birthday));
                        }
                        if($thanhvien->sex == 0){
                            $thanhvien['sex'] = 'Nữ';
                        }else{
                            $thanhvien['sex'] = 'Nam';
                        }
                        $thanhvien['house'] = 'Con';
                        $array_thanhvien[$thanhvien->id] = $thanhvien;
                    }
                }
            }
            
            $count_thanhvien = count($array_thanhvien);
            
            if(!empty($family->phone)){
                $family['phone'] = 0 . $family->phone;
            }
            $marriage = Marriage::where('id', $family->id)->orderBy('created_at', 'desc')->first();
            if(!empty($marriage->priest)){
                $priest = SacramentGiver::where('id', $marriage->priest)->orderBy('created_at', 'desc')->first();
                $family->linhmuc = $priest->name;
            }else{
                $family->linhmuc = '';
            }
            if(!empty($marriage->sohonphoi)){
                $family['sohonphoi'] = $marriage->sohonphoi;
            }else{
                $family['sohonphoi'] = '';
            }
            if(!empty($marriage->peopleone)){
                $family['peopleone'] = $marriage->peopleone;
            }else{
                $family['peopleone'] = '';
            }
            if(!empty($marriage->peopletwo)){
                $family['peopletwo'] = $marriage->peopletwo;
            }else{
                $family['peopletwo'] = '';
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
                $family['tinhtrang'] = $array_tinhtrang[$marriage->tinhtrang];
            }else{
                $family['tinhtrang'] = '';
            }        
            if(!empty($marriage->date)){
                $family['date'] = date("d-m-Y", strtotime($marriage->date));
            }else{
                $family['date'] = '';
            }
            if($marriage->marriage_address != ''){
                $family['diachi'] = $marriage->marriage_address . ', ';
            }else{
                $family['diachi'] = '';
            }
            if($marriage->marriage_ward != ''){
                $xaphuong_marriage = $this->GetXaTruQuan($marriage->marriage_ward);
                $family['xaphuong'] = $xaphuong_marriage['name'] . ', ';
            }else{
                $family['xaphuong'] = '';
            }
            if($marriage->marriage_province != ''){
                $tinhthanh_marriage = $this->GetTinhThanhQuan($marriage->marriage_province);
                $family['tinhthanh'] = $tinhthanh_marriage;
            }else{
                $family['tinhthanh'] = '';
            }
            if($family->origin != ''){
                $family['origin'] = $family->origin . ', ';
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
            if($family->paid != ''){
                $parish = ParishGroup::where('id', $family['paid'])->first();
                $family['paid'] = 'Giáo họ ' . $parish->name . ', ';
            }else{
                $family['paid'] = '';
            }
            if($family->pid != ''){
                $parish_management = ParishManagement::where('id', $family['pid'])->first();
                $family['pid'] = $parish_management->name;
            }else{
                $family['pid'] = '';
            }
            if($family->deid != ''){
                $deanery = Deanery::where('id', $family['deid'])->first();
                $family['deid'] = $deanery->name . ', ';
            }else{
                $family['deid'] = '';
            }
            if($family->did != ''){
                $diocese = Diocese::where('id', $family['did'])->first();
                $family['did'] = $diocese->name;
            }else{
                $family['did'] = '';
            }
            
            $array_intha = array(
                array(
                    'Số gia đình:',
                    $family->id,
                    'Số ĐT:',
                    $family->phone,
                    '',
                    'Số HP',
                    $family->sohonphoi,
                    '',
                    'Linh mục',
                    $family->linhmuc,
                ),
                array(
                    'Ông bà:',
                    $family->name,
                    'Địa chỉ:',
                    $family->origin . $family->ward . $family->province,
                    '',
                    'Tình trạng HP:',
                    $family->tinhtrang,
                    '',
                    'Người chứng 1:',
                    $family->peopleone
                ),
                array(
                    'Giáo họ',
                    $family->paid . $family->pid,
                    '',
                    '',
                    '',
                    'Nơi HP:',
                    $family->diachi . $family->xaphuong . $family->quanhuyen . $family->tinhthanh,
                    '',
                    'Người chứng 2:',
                    $family->peopletwo
                ),
                array(
                    'STT',
                    'Họ và tên',
                    'Giới tính',
                    'Họ tên cha mẹ',
                    'Liên hệ',
                    'Ngày sinh',
                    'Rửa tội',
                    'XTRL lần đầu',
                    'Thêm sức',
                    'Hôn phối'
                ),
                array(
                    1,
                    $boy->holy . ' ' . $boy->last_name . ' ' . $boy->name,
                    $boy->sex,
                    $boy->fathermother,
                    $boy->house,
                    $boy->birthday,
                    $boy->baptism_date,
                    $boy->more_power_date,
                    $boy->communion_date,
                    $boy->date,
                ),
                array(
                    2,
                    $girl->holy . ' ' . $girl->last_name . ' ' . $girl->name,
                    $girl->sex,
                    $girl->fathermother,
                    $girl->house,
                    $girl->birthday,
                    $girl->baptism_date,
                    $girl->more_power_date,
                    $girl->communion_date,
                    $girl->date,
                )
            );
            $i = 2;
            foreach ($array_thanhvien as $key => $item){
                $i++;
                $array_intha[] = array(
                    $i,
                    $item->name,
                    $item->sex,
                    $item->fathermother,
                    $item->house,
                    $item->birthday,
                    $item->baptism_date,
                    $item->more_power_date,
                    $item->communion_date,
                    $item->date,
                );
            }
            $array_moi = array();
            foreach ($array_intha as $key => $_row){
                $array_moi[] = array(
                    $key + 1,
                    $_row[1],
                    $_row[2],
                    $_row[3],
                    $_row[4],
                    $_row[5],
                    $_row[6],
                    $_row[7],
                    $_row[8],
                    $_row[9],          
                );
            }
            $array_data = $array_intha;
            return $array_data;
        }else{
            return [];
        }
    }
    
    public function styles(Worksheet $sheet) {
        $sheet->setShowGridlines(false);
        
        $sheet->getStyle('D2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  18,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $sheet->getStyle('A4:A6')->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
        $sheet->getStyle('B4:B6')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('C4:C6')->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
        $sheet->getStyle('D4:D6')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('F4:F6')->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
        $sheet->getStyle('G4:G6')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('I4:I6')->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
            ],
        ]);
        $sheet->getStyle('J4:J6')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ],
        ]);
        $sheet->getStyle('A7:J7')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '#000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        $lastColumn = $sheet->getHighestColumn();
        $lastRow = $sheet->getHighestRow();
        $range = 'A8:' . $lastColumn . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Times New Roman',
                'size'      =>  11,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '#000000'],
                ],
            ],
        ]);
    }
    
    public static function afterSheet(AfterSheet $event)
    {
        $default_font_style = [
            'font' => ['name' => 'Times New Roman', 'size' => 11],
        ];
        $active_sheet = $event->sheet->getDelegate();
        
        // Apply Style Arrays
        $active_sheet->getParent()->getDefaultStyle()->applyFromArray($default_font_style);
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
