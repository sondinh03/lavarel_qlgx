<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\CatechismClass;
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
use App\Models\DiLe;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class DiLeExport implements FromCollection, WithTitle, WithHeadings, WithHeadingRow, WithStyles, WithMapping, ShouldAutoSize //,  ShouldAutoSize, WithMapping, WithHeadingRow
{
    use RegistersEventListeners;
    
    public $rowNumber = 0;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('lop', $_POST['lop'])->get(); // Or a filtered collection
    }
    
    public function title():string
    {
        return 'Điểm danh đi lễ';
    }
    public function headings(): array
    {
        if(!empty($_POST)){
            $user = backpack_user();
            $userId = $user->id;
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                
                $ten_lop =  array('ĐIỂM DANH ĐI LỄ KỲ I');
                $ten_lopk2 =  array('ĐIỂM DANH ĐI LỄ KỲ II');
                
                $array_chinh = array(
                    'STT',
                    'Tên thánh',
                    'Họ tên đệm',
                    'Tên',
                    'Ngày sinh',
                    'Vắng/Tổng',
                );
                
                $begin = Carbon::parse($lop->start_date_one);
                $end = Carbon::parse($lop->end_date_one);
                
                $interval = \DateInterval::createFromDateString('1 day');
                
                $period = New \DatePeriod($begin, $interval, $end);
                
                $array_monthk1 = array('','','','','','',);
                $array_datek1 = array();
                foreach($period as $key => $dt){
                    $date_one = $dt->format("l");
                    $date_month = $dt->format("m");
                    $date_time = $dt->format("d/m/Y");
                    if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                        if($date_one == 'Thursday'){
                            $date_one_vi = 'Thứ 5';
                        }else{
                            $date_one_vi = 'Chủ nhật';
                        }
                        $array_monthk1[] = 'Tháng ' . $date_month;
                        $array_datek1[] = $date_one_vi . ', ' . $date_time;
                    }
                }
                
                $count_ky1 = count($array_monthk1);
                
                $cotmoi = 4 + $count_ky1;
                
                $array_trong = array();
                for ($i = 1; $i < $cotmoi; $i++) {
                    $array_trong[$i] = ' ';
                }
                
                $ten_lop = array_merge($ten_lop, $array_trong, $ten_lopk2);
                
                $array_heading = array_merge($array_chinh, $array_datek1);
                
                // hk2
                $begin = Carbon::parse($lop->start_date_two);
                $end = Carbon::parse($lop->end_date_two);
                
                $interval = \DateInterval::createFromDateString('1 day');
                
                $period = New \DatePeriod($begin, $interval, $end);
                
                $array_monthk2 = array('','','','',);
                $array_datek2 = array();
                foreach($period as $key => $dt){
                    $date_one = $dt->format("l");
                    $date_month = $dt->format("m");
                    $date_time = $dt->format("d/m/Y");
                    if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                        if($date_one == 'Thursday'){
                            $date_one_vi = 'Thứ 5';
                        }else{
                            $date_one_vi = 'Chủ nhật';
                        }
                        $array_monthk2[] = 'Tháng ' . $date_month;
                        $array_datek2[] = $date_one_vi . ', ' . $date_time;
                    }
                }
                
                $array_month = array_merge($array_monthk1, array(''), array(''), array(''), array(''), $array_monthk2);
                
                $array_ngaythang = array_merge($array_heading, array(''), array(''), $array_chinh, $array_datek2);
                
                $array_dihoc = array(
                    $ten_lop,
                    array(''),
                    $array_month,
                    $array_ngaythang,
                );
                
                return $array_dihoc;
            }else{
                return [];
            }
        }
    }
    
    public function map($hocsinh): array
    {
        if(!empty($_POST)){
            $user = backpack_user();
            $userId = $user->id;
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
                    
                    $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                    
                    $begin = Carbon::parse($lop->start_date_one);
                    $end = Carbon::parse($lop->end_date_one);
                    
                    $interval = \DateInterval::createFromDateString('1 day');
                    
                    $period = New \DatePeriod($begin, $interval, $end);
                    
                    $array_monthk1 = $data_k1 = array();
                    foreach($period as $key => $dt){
                        $date_one = $dt->format("l");
                        $date_month = $dt->format("m");
                        $date_time = $dt->format("d/m/Y");
                        if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                            $thang = $dt->format('n');
                            $ngay = $dt->format('j');
                            $dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                            if(!empty($dile)){
                                if($dile->dile == 1){
                                    $data_k1[] = 'Đi lễ';
                                }elseif($dile->dile == 3){
                                    $data_k1[] = 'CP';
                                }else{
                                    $data_k1[] = 'Vắng';
                                }
                            }else{
                                $data_k1[] = '';
                            }
                            $array_monthk1[] = $date_month;
                        }
                    }
                    
                    $vang = 'Vắng';
                    $totalvang = array_filter($data_k1, function($item) use ($vang) {
                        return $item === $vang;
                    });
                        
                    $tong_vang = count($totalvang);
                    
                    $ketquadihoc_ten = array(
                        $this->rowNumber,
                        $hocsinh->holy,
                        $hocsinh->last_name,
                        $hocsinh->name,
                        $hocsinh->birthday,
                    );
                    
                    $ketquadihock1 = array_merge($ketquadihoc_ten, array($tong_vang . '/' . count($array_monthk1)));
                    
                    //hk2
                    $begin = Carbon::parse($lop->start_date_two);
                    $end = Carbon::parse($lop->end_date_two);
                    
                    $interval = \DateInterval::createFromDateString('1 day');
                    
                    $period = New \DatePeriod($begin, $interval, $end);
                    
                    $array_monthk2 = $data_k2 = array();
                    foreach($period as $key => $dt){
                        $date_one = $dt->format("l");
                        $date_month = $dt->format("m");
                        $date_time = $dt->format("d/m/Y");
                        if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                            $thang = $dt->format('n');
                            $ngay = $dt->format('j');
                            $dile = DiLe::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                            if(!empty($dile)){
                                if($dile->dile == 1){
                                    $data_k2[] = 'Đi lễ';
                                }elseif($dile->dile == 3){
                                    $data_k2[] = 'CP';
                                }else{
                                    $data_k2[] = 'Vắng';
                                }
                            }else{
                                $data_k2[] = '';
                            }
                            $array_monthk2[] = $date_month;
                        }
                    }
                
                    $vang = 'Vắng';
                    $totalvang = array_filter($data_k2, function($item) use ($vang) {
                        return $item === $vang;
                    });
                    
                    $tong_vang = count($totalvang);
                    
                    $ketquadihoc_ten = array(
                        $this->rowNumber,
                        $hocsinh->holy,
                        $hocsinh->last_name,
                        $hocsinh->name,
                        $hocsinh->birthday,
                    );
                    
                    $ketquadihock2 = array_merge($ketquadihoc_ten, array($tong_vang . '/' . count($array_monthk2)));
                    
                    $ketquadihoc = array_merge($ketquadihock1, $data_k1, array(''), array(''), $ketquadihock2, $data_k2);
                    
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
        
        $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                
        $begin = Carbon::parse($lop->start_date_one);
        $end = Carbon::parse($lop->end_date_one);
        
        $interval = \DateInterval::createFromDateString('1 day');
        
        $period = New \DatePeriod($begin, $interval, $end);
        
        $array_monthk1 = array('','','','',);
        $array_datek1 = array();
        foreach($period as $key => $dt){
            $date_one = $dt->format("l");
            $date_month = $dt->format("m");
            $date_time = $dt->format("d/m/Y");
            if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                if($date_one == 'Thursday'){
                    $date_one_vi = 'Thứ 5';
                }else{
                    $date_one_vi = 'Chủ nhật';
                }
                $array_monthk1[] = 'Tháng ' . $date_month;
                $array_datek1[] = $date_one_vi . ', ' . $date_time;
            }
        }
        
        $soluongk1 = count($array_datek1)+6;
        
        $columnLetter = Coordinate::stringFromColumnIndex($soluongk1);
        
        $sheet->mergeCells('A1:' . $columnLetter . '1');
        
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
        
        $sheet->getStyle('G3:'.$columnLetter.'3')->applyFromArray([
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
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],            
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE, // works
                    'argb' => 'EAF2FD', // doesn't work
                ],
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
        
        $sheet->getStyle('E4:'.$columnLetter.'4')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        
        // hk2
        $begin = Carbon::parse($lop->start_date_two);
        $end = Carbon::parse($lop->end_date_two);
        
        $interval = \DateInterval::createFromDateString('1 day');
        
        $period = New \DatePeriod($begin, $interval, $end);
        
        $array_monthk2 = array('','','','',);
        $array_datek2 = array();
        foreach($period as $key => $dt){
            $date_one = $dt->format("l");
            $date_month = $dt->format("m");
            $date_time = $dt->format("d/m/Y");
            if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                if($date_one == 'Thursday'){
                    $date_one_vi = 'Thứ 5';
                }else{
                    $date_one_vi = 'Chủ nhật';
                }
                $array_monthk2[] = 'Tháng ' . $date_month;
                $array_datek2[] = $date_one_vi . ', ' . $date_time;
            }
        }
        
        $soluongk2_start = count($array_datek1) + 9;
        
        $columnLetter_start = Coordinate::stringFromColumnIndex($soluongk2_start);
        
        $soluongk2_end = count($array_datek2) + $soluongk2_start+5;
        
        $columnLetter_end = Coordinate::stringFromColumnIndex($soluongk2_end);
        
        $sheet->mergeCells( $columnLetter_start . '1:' . $columnLetter_end . '1');
        
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
        
        
        
        $columnLetter_start_thang = Coordinate::stringFromColumnIndex($soluongk2_start+6);
        
        $sheet->getStyle($columnLetter_start_thang . '3:'.$columnLetter_end.'3')->applyFromArray([
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
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLUE, // works
                    'argb' => 'EAF2FD', // doesn't work
                ],
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
