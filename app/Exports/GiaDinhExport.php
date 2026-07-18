<?php

namespace App\Exports;

use App\Models\GiaDinh;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;

use Illuminate\Support\Facades\DB;
use App\Models\Child;
use App\Models\FamilyArea;
use App\Models\Marriage;
use App\Models\SacramentGiver;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class GiaDinhExport implements FromCollection, WithHeadings, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithMapping
{
    use RegistersEventListeners;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        //return GiaDinh::all();
        $giadinh = GiaDinh::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->first();
        return $giadinh;
    }
    
    public function title():string 
    {
        return 'Gia Đình - Hôn Phối';
    }
    
    public function headings(): array
    {
        return [
            'Mã gia đình',
            'Tên gia đình',
            'Mã GD nam',
            'Người nam',
            'Mã GD nữ',
            'Người nữ',
            'Mã GD thành viên (ví dụ: 1,2,3,4,5)',
            'Số người',
            'Điện thoại',
            'Điện thoại Chồng',
            'Điện thoại Vợ',
            'Địa chỉ',
            'Xã / phường',
            'Tỉnh / TP',
            'Giáo họ',
            'Giáo xứ',
            'Giáo hạt',
            'Giáo phận',
            'Đã chuyển đi xứ khác',
            'Diện gia đình',
            'Là gia đình không được thống kê',
            'Ghi chú',
            'Số hôn phối',
            'Ngày hôn phối',
            'Nơi hôn phối',
            'Xã / phường nơi hôn phối',
            'Tỉnh / TP nơi hôn phối',
            'Linh mục chứng',
            'Người chứng 1',
            'Người chứng 2',
            'Tình trạng hôn phối',
            'Ghi chú hôn phối',
            'Created',
            'Updated',
        ];
    }
    
    public function map($family): array
    {
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->parish == 1 AND $decen->pid == $_POST['giaoxu']){
                if($_POST['giaophan'] == $family->did AND  $_POST['giaohat'] == $family->deid AND $_POST['giaoxu'] == $family->pid){
                    if(!empty($family->mother))
                    {
                        $mother = DB::table('parishioners')
                        ->where('id', $family->mother)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->name_mother = $mother->last_name . ' ' . $mother->name;
                        
                        $holy = DB::table('holymanagements')
                        ->where('id', $mother->holy)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->name_mother = $holy->name . ' ' .$family->name_mother;
                    }
                    if(!empty($family->father))
                    {
                        $father = DB::table('parishioners')
                        ->where('id', $family->father)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->name_father = $father->last_name . ' ' . $father->name;
                        
                        $holy = DB::table('holymanagements')
                        ->where('id', $father->holy)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->name_father = $holy->name . ' ' .$family->name_father;
                    }
                    
                    $child = Child::where('childrengable_id', $family->id)->get();
                    $array_child = array();
                    foreach($child as $item){
                        $array_child[] = $item->children_id;
                    }
                    $family->thanhvien = implode('-', $array_child);
                    
                    if(!empty($family->phone)){
                        $family->phone = 0 . $family->phone;
                    }
                    $family->phone_father = '';
                    $family->phone_mother = '';
                    
                    @include(resource_path().'/cities/xa_phuong_thitran.php');
                    foreach($xa_phuong_thitran as $xp){
                        if($xp['xaid'] == $family->ward){
                            $family->ward = $xp['name'];
                        }
                    }
                    
                    @include(resource_path().'/cities/tinh_thanhpho.php');
                    $family->province = $tinh_thanhpho[$family->province];
                    
                    if(!empty($family->paid)){
                        $paid = DB::table('parish_groups')
                        ->where('status', '1')
                        ->where('id', $family->paid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->paid = $paid->name;
                    }
                    if(!empty($family->pid)){
                        $paid = DB::table('parish_managements')
                        ->where('status', '1')
                        ->where('id', $family->pid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->pid = $paid->name;
                    }
                    if(!empty($family->deid)){
                        $deid = DB::table('deanerys')
                        ->where('status', '1')
                        ->where('id', $family->deid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->deid = $deid->name;
                    }
                    if(!empty($family->did)){
                        $did = DB::table('dioceses')
                        ->where('status', '1')
                        ->where('id', $family->did)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->did = $did->name;
                    }
                    if(!empty($family->assid)){
                        $assid = DB::table('associations')
                        ->where('status', '1')
                        ->where('id', $family->assid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $family->assid = $assid->name;
                    }
                    
                    if(!empty($family->noio)){
                        $family->noio = 'x';
                    }
                    
                    $FamilyArea = FamilyArea::where('id', '=', $family->dien)->where('status', '=', 1)->orderBy('created_at', 'desc')->first();
                    if(!empty($FamilyArea->id)){
                        $family->dien = $FamilyArea->id;
                    }else{
                        $family->dien = '';
                    }
                    
                    if(!empty($family->thongke)){
                        $family->thongke = 'x';
                    }
                    
                    $marriage = Marriage::where('idfamily', $family->id)->orderBy('created_at', 'desc')->first();
                    
                    if(!empty($marriage->id)){
                        $family->sohonphoi = $marriage->sohonphoi;
                        
                        $family->date = date("d-m-Y", strtotime($marriage->date));
                        
                        $family->marriage_address = $marriage->marriage_address;
                        
                        @include(resource_path().'/cities/xa_phuong_thitran.php');
                        foreach($xa_phuong_thitran as $xp){
                            if($xp['xaid'] == $marriage->marriage_ward){
                                $family->marriage_ward = $xp['name'];
                            }
                        }
                        
                        @include(resource_path().'/cities/tinh_thanhpho.php');
                        if(!empty($marriage->marriage_province)){
                            $family->marriage_province = $tinh_thanhpho[$marriage->marriage_province];
                        }else{
                            $family->marriage_province = '';
                        }
                        
                        $sacrament = SacramentGiver::where('id', $marriage->priest)->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $family->priest = $sacrament->name;
                        }else{
                            $family->priest = '';
                        }
                        
                        $family->peopleone = $marriage->peopleone;
                        $family->peopletwo = $marriage->peopleone;
                        
                        $tinhtrang = array(
                            '1' => 'Hợp pháp',
                            '2' => 'Hợp thức hóa',
                            '3' => 'Chuẩn',
                            '4' => 'Không theo phép đạo',
                            '5' => 'Ly thân',
                            '6' => 'Ly dị',
                            '7' => 'Đã được tháo gỡ',
                            '8' => 'Không xác định',
                        );
                        
                        if($marriage->tinhtrang > 0){
                            $family->tinhtrang = $tinhtrang[$marriage->tinhtrang];
                        }else{
                            $family->tinhtrang = '';
                        }
                        
                        $family->marriage_note = $marriage->marriage_note;
                    }else{
                        $family->sohonphoi = '';
                        $family->date = '';
                        $family->marriage_address = '';
                        $family->marriage_ward = '';
                        $family->marriage_province = '';
                        $family->priest = '';
                        $family->peopleone = '';
                        $family->peopletwo = '';
                        $family->tinhtrang = '';
                        $family->marriage_note = '';
                    }
                    
                    return [
                        $family->id,
                        $family->name,
                        $family->father,
                        $family->name_father,
                        $family->mother,
                        $family->name_mother,
                        $family->thanhvien,
                        $family->songuoi,
                        $family->phone,
                        $family->phone_father,
                        $family->phone_mother,
                        $family->origin,
                        $family->ward,
                        $family->province,
                        $family->paid,
                        $family->pid,
                        $family->deid,
                        $family->did,
                        $family->noio,
                        $family->dien,
                        $family->thongke,
                        $family->note,
                        $family->sohonphoi,
                        $family->date,
                        $family->marriage_address,
                        $family->marriage_ward,
                        $family->marriage_province,
                        $family->priest,
                        $family->peopleone,
                        $family->peopletwo,
                        $family->tinhtrang,
                        $family->marriage_note,
                        $family->created_at,
                        $family->updated_at,
                    ];
                }else{
                    return [];
                }
            }else{
                return [];
            }
        }else{
            return [];
        }
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
