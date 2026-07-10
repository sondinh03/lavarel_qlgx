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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMappedCells;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use App\Models\Student;
use App\Models\DiHoc;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class DiHocExport implements FromCollection, WithHeadingRow, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithEvents, WithStyles
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
        return 'Điểm danh đi học';
    }
    
    public function headings(): array
    {
        if(!empty($_POST)){
            $user = backpack_user();
            $userId = $user->id;
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                
                $start_date = $lop->start_date_one;
                $end_Date = $lop->end_date_one;
                
                $startTime = strtotime($start_date);
                $endTime = strtotime($end_Date);
                
                $weeks = array();
                $date = new \DateTime();
                $i = 1;
                while ($startTime < $endTime) {
                    $weeks[$i]['week'] = date('W', $startTime);
                    $weeks[$i]['year'] = date('Y', $startTime);
                    $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                    $weeks[$i]['Monday']=$date->format('Y-m-d');
                    $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                    $startTime += strtotime('+1 week', 0);
                    $i++;
                }
                $weeks_ky1 = array();
                foreach($weeks as $key => $row){
                    $weeks_ky1[] = 'Tuần ' . $key;
                }
                
                $count_ky1 = count($weeks);
                
                $cotmoi = 9 + $count_ky1;
                
                //$columnLetter = Coordinate::stringFromColumnIndex($cotmoi); // Returns 'D' for 4
                
                $array_trong = array();
                for ($i = 2; $i < $cotmoi; $i++) {
                    $array_trong[$i] = ' ';
                }
                
                $array_ten = array(
                    'STT',
                    'Tên thánh',
                    'Họ tên đệm',
                    'Tên',
                    'Ngày sinh',
                    'Vắng/Tổng',
                );
                
                $array_tenk1 = array_merge($array_ten, $weeks_ky1);
                
                $array_tenk1 = array_merge($array_tenk1, array('', ''), $array_ten);
                
                $ten_lop =  array('DANH SÁCH LỚP ' . $lop->name . ' ' . $lop->schoolyear);
                
                $ten_lopmoi = array_merge($ten_lop, $array_trong);
                
                $ten_lopmoi = array_merge($ten_lopmoi, $ten_lop);
                
                $tenhocky = array_merge(array('HỌC KỲ I'), $array_trong, array('HỌC KỲ II'));
                
                $start_date = $lop->start_date_two;
                $end_Date = $lop->end_date_two;
                
                $startTime = strtotime($start_date);
                $endTime = strtotime($end_Date);
                
                $weeks = array();
                $date = new \DateTime();
                $i = 1;
                while ($startTime < $endTime) {
                    $weeks[$i]['week'] = date('W', $startTime);
                    $weeks[$i]['year'] = date('Y', $startTime);
                    $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                    $weeks[$i]['Monday']=$date->format('Y-m-d');
                    $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                    $startTime += strtotime('+1 week', 0);
                    $i++;
                }
                
                $weeks_ky2 = array();
                foreach($weeks as $key => $row){
                    $weeks_ky2[] = 'Tuần ' . $key;
                }
                
                $array_ten = array_merge($array_tenk1, $weeks_ky2);
                
                $array_dihoc = array(
                    $ten_lopmoi,
                    $tenhocky,
                    array(''),
                    $array_ten,
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
                    if(!empty($hocsinh->phone)){
                        $hocsinh->phone = 0 . $hocsinh->phone;
                    }
                    
                    $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                    
                    $start_date = $lop->start_date_one;
                    $end_Date = $lop->end_date_one;
                    
                    $startTime = strtotime($start_date);
                    $endTime = strtotime($end_Date);
                    
                    $weeks = array();
                    $date = new \DateTime();
                    $i = 1;
                    while ($startTime < $endTime) {
                        $weeks[$i]['week'] = date('W', $startTime);
                        $weeks[$i]['year'] = date('Y', $startTime);
                        $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                        $weeks[$i]['Monday']=$date->format('Y-m-d');
                        $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                        $startTime += strtotime('+1 week', 0);
                        $i++;
                    }
                    
                    $dihoc1 = array();
                    foreach($weeks as $key => $row){
                        $dihochk1 = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 1)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->first();
                        if(!empty($dihochk1)){
                            if($dihochk1->dihoc == 1){
                                $dihoc1[] = 'Đi học';
                            }elseif($dihochk1->dihoc == 2){
                                $dihoc1[] = 'Vắng có phép';
                            }elseif($dihochk1->dihoc == 0){
                                $dihoc1[] = 'Vắng';
                            }else{
                                $dihoc1[] = '---';
                            }
                        }
                    }
                    
                    $vang = 'Vắng';
                    $totalvang = array_filter($dihoc1, function($item) use ($vang) {
                        return $item === $vang;
                    });
                        
                    $tong_vang = count($totalvang);
                    
                    $ketquadihock1 = array(
                        $this->rowNumber,
                        $hocsinh->holy,
                        $hocsinh->last_name,
                        $hocsinh->name,
                        $hocsinh->birthday,
                        $tong_vang . '/' . count($weeks),
                    );
                    
                    $ketquadihock1 = array_merge($ketquadihock1, $dihoc1);
                    
                    $count_ky1 = count($weeks);
                    
                    $cotmoi = 8 + $count_ky1;
                    
                    if( count($ketquadihock1) < $cotmoi){
                        $giatricot = $cotmoi - count($ketquadihock1);
                        for ($i = 0; $i < $giatricot; $i++) {
                            $array_trong[$i] = ' ';
                        }
                    }
                    $start_date = $lop->start_date_two;
                    $end_Date = $lop->end_date_two;
                    
                    $startTime = strtotime($start_date);
                    $endTime = strtotime($end_Date);
                    
                    $weeks = array();
                    $date = new \DateTime();
                    $i=1;
                    while ($startTime < $endTime) {
                        $weeks[$i]['week'] = date('W', $startTime);
                        $weeks[$i]['year'] = date('Y', $startTime);
                        $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
                        $weeks[$i]['Monday']=$date->format('Y-m-d');
                        $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
                        $startTime += strtotime('+1 week', 0);
                        $i++;
                    }
                    
                    $dihoc2 = array();
                    foreach($weeks as $key => $row){
                        $dihochk2 = DiHoc::where('idh', $hocsinh->id)->where('lophoc', $hocsinh->lop)->where('hocky', 2)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->first();
                        if(!empty($dihochk2)){
                            if($dihochk2->dihoc == 1){
                                $dihoc2[] = 'Đi học';
                            }elseif($dihochk2->dihoc == 2){
                                $dihoc2[] = 'Vắng có phép';
                            }elseif($dihochk2->dihoc == 0){
                                $dihoc2[] = 'Vắng';
                            }else{
                                $dihoc2[] = '---';
                            }
                        }
                    }
                    
                    $vang = 'Vắng';
                    $totalvang = array_filter($dihoc2, function($item) use ($vang) {
                        return $item === $vang;
                    });
                        
                    $tong_vang = count($totalvang);
                    
                    $ketquadihock2 = array(
                        $this->rowNumber,
                        $hocsinh->holy,
                        $hocsinh->last_name,
                        $hocsinh->name,
                        $hocsinh->birthday,
                        $tong_vang . '/' . count($weeks),
                    );
                    
                    $ketquadihock2 = array_merge($ketquadihock2, $dihoc2);
                    
                    $ketquadihoc = array_merge($ketquadihock1, $array_trong, $ketquadihock2);
                    
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
        
        
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A2:J2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
        
        $start_date = $lop->start_date_one;
        $end_Date = $lop->end_date_one;
        
        $startTime = strtotime($start_date);
        $endTime = strtotime($end_Date);
        
        $weeks = array();
        $date = new \DateTime();
        $i=1;
        while ($startTime < $endTime) {
            $weeks[$i]['week'] = date('W', $startTime);
            $weeks[$i]['year'] = date('Y', $startTime);
            $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
            $weeks[$i]['Monday']=$date->format('Y-m-d');
            $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
            $startTime += strtotime('+1 week', 0);
            $i++;
        }
        
        $count_ky1 = count($weeks);
        
        $cotmoi = 6 + $count_ky1;
        
        $columnLetter = Coordinate::stringFromColumnIndex($cotmoi);
        
        
        $sheet->mergeCells('A1:' . $columnLetter . '1');
        $sheet->mergeCells('A2:' . $columnLetter . '2');
        
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
        
        
        
        $count_ky1 = count($weeks);
        
        $cotmoi = 9 + $count_ky1;
        
        $columnLetter = Coordinate::stringFromColumnIndex($cotmoi); // Returns 'D' for 4
        
        $start_date = $lop->start_date_two;
        $end_Date = $lop->end_date_two;
        
        $startTime = strtotime($start_date);
        $endTime = strtotime($end_Date);
        
        $weeks = array();
        $date = new \DateTime();
        $i=1;
        while ($startTime < $endTime) {
            $weeks[$i]['week'] = date('W', $startTime);
            $weeks[$i]['year'] = date('Y', $startTime);
            $date->setISODate($weeks[$i]['year'], $weeks[$i]['week']);
            $weeks[$i]['Monday']=$date->format('Y-m-d');
            $weeks[$i]['Sunday'] = date('Y-m-d',strtotime($weeks[$i]['Monday'] . "+6 days"));
            $startTime += strtotime('+1 week', 0);
            $i++;
        }
        
        $count_ky2 = count($weeks);
        
        $cotcuoi = 5 + $count_ky2 + $cotmoi;        
        
        $columnLetter = Coordinate::stringFromColumnIndex($cotmoi);
        
        $columnk2end = Coordinate::stringFromColumnIndex($cotcuoi);
        
        $sheet->getStyle($columnLetter . '1:' . $columnk2end . '1')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->mergeCells($columnLetter . '1:' . $columnk2end . '1');
        $sheet->mergeCells($columnLetter . '2:' . $columnk2end . '2');
        $sheet->getStyle($columnLetter . '2:' . $columnk2end . '2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle($columnLetter . '4:' . $columnk2end . '4')->applyFromArray([
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
        
        $lastRow = $sheet->getHighestRow();
        $range = $columnLetter . '4:' . $columnk2end . $lastRow;
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
