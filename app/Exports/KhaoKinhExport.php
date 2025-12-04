<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Lop;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithMappedCells;
//use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use Carbon\Carbon;

use App\Models\Student;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Models\KhaoKinh;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class KhaoKinhExport implements FromCollection, WithTitle, WithHeadings, WithHeadingRow, WithStyles, WithMapping, ShouldAutoSize //, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithHeadingRow
{
    use RegistersEventListeners;
    
    public $rowNumber = 0;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('lop', $_POST['lop'])->orderBy('id', 'asc')->get(); // Or a filtered collection
    }
    
    public function title():string
    {
        return 'Khảo kinh';
    }
    
    public function headings(): array
    {
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                $lop = Lop::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                
                $ten_loph =  array('DANH SÁCH LỚP ' . $lop->name . ' ' . $lop->schoolyear);
                
                $hk = array('HỌC KỲ I');
                $hk2 = array('HỌC KỲ II');
                
                $ten = array(
                    'STT',
                    'Tên thánh',
                    'Họ tên đệm',
                    'Tên',
                    'Ngày sinh',
                    'Điểm/Tổng',
                );
                
                $student = Student::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('lop', $_POST['lop'])->get();
                $maxhk1 = $maxhk2 = array();
                foreach($student as $item){                    
                    $hocsinh1 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 1)->where('status', 1)->orderBy('ngay', 'asc')->get();
                    if(!empty($hocsinh1)){
                        foreach($hocsinh1 as $row){
                            $maxhk1[] = $row->ngay;
                        }                
                    }
                    $hocsinh2 = KhaoKinh::where('idh', $item->id)->where('lophoc', $item->lop)->where('hocky', 2)->where('status', 1)->orderBy('ngay', 'asc')->get();
                    if(!empty($hocsinh2)){
                        foreach($hocsinh2 as $row){
                            $maxhk2[] = $row->ngay;
                        }
                    }
                }
                
                if(is_array($maxhk1)){
                    if(!empty($maxhk1)){
                        $maxhk1 = max($maxhk1);
                    }else{
                        $maxhk1 = 0;
                    }
                }else{
                    $maxhk1 = 0;
                }
                  
                if(is_array($maxhk2)){
                    if(!empty($maxhk2)){
                        $maxhk2 = max($maxhk2);
                    }else{
                        $maxhk2 = 0;
                    }
                }else{
                    $maxhk2 = 0;
                }
                
                $array_hk1 = array();
                $trong_hk1 = array();
                for ($i = 1; $i <= $maxhk1; $i++){
                    $array_hk1[$i] = $i;
                    $trong_hk1[] = '';
                }
                
                $array_hk2 = array();
                $trong_hk2 = array();
                for ($i = 1; $i <= $maxhk2; $i++){
                    $array_hk2[$i] = $i;
                    $trong_hk2[] = '';
                }
                
                $ten = array_merge($ten, $array_hk1, array(''), array(''), $ten, $array_hk2);
                
                $ten_loph = array_merge($ten_loph, array(''), array(''), array(''), array(''), array(''), array(''), array(''), $trong_hk1, $ten_loph);
                $hk = array_merge($hk, array(''), array(''), array(''), array(''), array(''), array(''), array(''), $trong_hk1, $hk2);
                
                $array_khaokinh = array(
                    $ten_loph,
                    $hk,
                    array(''),
                    $ten,
                );
                
                return $array_khaokinh;
            }else{
                return [];
            }
        }
    }
    
    public function map($hocsinh): array
    {
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                if($_POST['giaophan'] == $hocsinh->did AND  $_POST['giaohat'] == $hocsinh->deid AND $_POST['giaoxu'] == $hocsinh->pid){
                    $this->rowNumber++;
                    if(!empty($hocsinh->paid)){
                        $paid = DB::table('parishs')
                        ->where('status', '1')
                        ->where('id', $hocsinh->paid)
                        ->orderBy('id', 'ASC')
                        ->first();
                        if(!empty($paid->name)){
                            $hocsinh->paid = $paid->name;
                        }
                    }
                    if(!empty($hocsinh->holy)){
                        $holy = DB::table('holymanagements')
                        ->where('id', $hocsinh->holy)
                        ->orderBy('id', 'ASC')
                        ->first();
                        $hocsinh->holy = $holy->name;
                    }
                    
                    if(!empty($hocsinh->birthday) AND strlen($hocsinh->birthday) == 10){
                        $hocsinh->birthday = date('d-m-Y', strtotime($hocsinh->birthday));
                    }
                    
                    $ten = array(
                        $this->rowNumber,
                        $hocsinh->holy,
                        $hocsinh->last_name,
                        $hocsinh->name,
                        $hocsinh->birthday,
                    );
                    
                    $maxhk1 = $maxhk2 = array();
                    
                    $hocsinh1 = KhaoKinh::where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('status', 1)->orderBy('ngay', 'asc')->get();
                    
                    if(!empty($hocsinh1)){
                        foreach($hocsinh1 as $row){
                            $maxhk1[] = $row->ngay;
                        }
                    }
                    
                    if(!empty($maxhk1)){
                        $maxhk1 = max($maxhk1);
                    }else{
                        $maxhk1 = 0;
                    }
                    
                    $hocsinh2 = KhaoKinh::where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('status', 1)->orderBy('ngay', 'asc')->get();
                    
                    if(!empty($hocsinh2)){
                        foreach($hocsinh2 as $row){
                            $maxhk2[] = $row->ngay;
                        }
                    }
                    if(!empty($maxhk2)){
                        $maxhk2 = max($maxhk2);
                    }else{
                        $maxhk2 = 0;
                    }
                    
                    $ketqua1 = $bai1 = array();
                    for ($i = 1; $i <= $maxhk1; $i++){
                        $trong_hk1[] = '';
                        $khaokinh1 = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('ngay', $i)->where('status', 1)->orderBy('ngay', 'asc')->get()->first();
                        if(!empty($khaokinh1->khaokinh)){
                            if($khaokinh1->khaokinh == 1){
                                $bai1[] = 'Thuộc bài';
                                $ketqua1[] = '1';
                            }elseif($khaokinh1->khaokinh == 2){
                                $bai1[] = 'Ấp úng';
                                $ketqua1[] = '0.5';
                            }elseif($khaokinh1->khaokinh == 3){
                                $bai1[] = 'Không thuộc';
                                $ketqua1[] = '0';
                            }else{
                                $bai1[] = '';
                                $ketqua1[] = '0';
                            }
                        }else{
                            $bai1[] = '';
                            $ketqua1[] = '0';
                        }
                    }
                    
                    $ketquahk1 = array_merge($ten, array(array_sum($ketqua1) . '/' . $maxhk1), $bai1);
                    
                    $ketqua2 = $bai2 = array();
                    for ($i = 1; $i <= $maxhk2; $i++){
                        $trong_hk2[] = '';
                        $khaokinh2 = KhaoKinh::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('ngay', $i)->where('status', 1)->orderBy('ngay', 'asc')->get()->first();
                        if(!empty($khaokinh2->khaokinh)){
                            if($khaokinh2->khaokinh == 1){
                                $bai2[] = 'Thuộc bài';
                                $ketqua2[] = '1';
                            }elseif($khaokinh2->khaokinh == 2){
                                $bai2[] = 'Ấp úng';
                                $ketqua2[] = '0.5';
                            }elseif($khaokinh2->khaokinh == 3){
                                $bai2[] = 'Không thuộc';
                                $ketqua2[] = '0';
                            }else{
                                $bai2[] = '';
                                $ketqua2[] = '0';
                            }
                        }else{
                            $bai2[] = '';
                            $ketqua2[] = '0';
                        }
                    }
                    
                    $ketquahk2 = array_merge($ten, array(array_sum($ketqua2) . '/' . $maxhk2), $bai2);
                    
                    $ketqua = array_merge($ketquahk1, array(''), array(''), $ketquahk2);
                    
                    $ketquadihoc = array(
                        $ketqua,
                    );
                    
                    return $ketquadihoc;
                }else{
                    return [];
                }
            }else{
                return [];
            }
        }        
    }
    
    public function styles(Worksheet $sheet) {
        $sheet->setShowGridlines(true);
        
        $lop = Lop::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
        
        $hocsinh1 = KhaoKinh::where('lophoc', $lop->id)->where('hocky', 1)->where('status', 1)->orderBy('ngay', 'asc')->get();
        
        if(!empty($hocsinh1)){
            foreach($hocsinh1 as $row){
                $maxhk1[] = $row->ngay;
            }
        }        
        if(!empty($maxhk1)){
            $maxhk1 = max($maxhk1);
        }else{
            $maxhk1 = 0;
        }
        
        $columnLetter = Coordinate::stringFromColumnIndex($maxhk1 + 6);
        
        $sheet->mergeCells('A1:' . $columnLetter . '1');
        
        $sheet->mergeCells('A2:' . $columnLetter . '2');
        
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $lastRow = $sheet->getHighestRow();
        $range = 'A4:' . $columnLetter . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Microsoft Sans Serif',
                'size'      =>  8.5,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '#000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        
        $sheet->getStyle('A4:'. $columnLetter . '4')->applyFromArray([
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
        
        $sheet->getStyle('G4:'.$columnLetter.'4')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $hocsinh2 = KhaoKinh::where('lophoc', $lop->id)->where('hocky', 2)->where('status', 1)->orderBy('ngay', 'asc')->get();
        
        if(!empty($hocsinh2)){
            foreach($hocsinh2 as $row){
                $maxhk2[] = $row->ngay;
            }
        }
        if(!empty($maxhk2)){
            $maxhk2 = max($maxhk2);
        }else{
            $maxhk2 = 0;
        }
        
        $columnLetter_start = Coordinate::stringFromColumnIndex($maxhk1 + 4 + 3 + 2);
        $columnLetter_end = Coordinate::stringFromColumnIndex($maxhk1 + 4 + 3 + 3 + 4 + $maxhk2);
        
        $sheet->mergeCells($columnLetter_start . '1:' . $columnLetter_end . '1');
        
        $sheet->mergeCells($columnLetter_start . '2:' . $columnLetter_end . '2');
        
        $sheet->getStyle($columnLetter_start . '1')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle($columnLetter_start . '2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $lastRow = $sheet->getHighestRow();
        $range = $columnLetter_start . '4:' . $columnLetter_end . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'font' => [
                'bold'      => FALSE,
                'name'      =>  'Microsoft Sans Serif',
                'size'      =>  8.5,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '#000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_BOTTOM,
            ],
        ]);
        
        $sheet->getStyle($columnLetter_start . '4:'. $columnLetter_end . '4')->applyFromArray([
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
        
        $sheet->getStyle($columnLetter_start . '4:'.$columnLetter_end.'4')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
    }
    
    public static function afterSheet(AfterSheet $event)
    {
        $default_font_style = [
            'font' => ['name' => 'Microsoft Sans Serif', 'size' => 8.5],
        ];
        $active_sheet = $event->sheet->getDelegate();
        
        // Apply Style Arrays
        $active_sheet->getParent()->getDefaultStyle()->applyFromArray($default_font_style);
    }
}
