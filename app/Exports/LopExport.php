<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\CatechismClass;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Illuminate\Support\Facades\Date;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\DiemThi;
use App\Models\Block;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class LopExport implements FromCollection, WithHeadingRow, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithEvents, WithStyles
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
        $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
        
        return $lop->name;
    }
    
    public function headings(): array
    {
        $lop = CatechismClass::where('did', '=', $_POST['giaophan'])->where('deid', '=', $_POST['giaohat'])->where('pid', '=', $_POST['giaoxu'])->where('id', $_POST['lop'])->first();
                
        return[
            [
                'DANH SÁCH LỚP ' . $lop->name . ' ' . $lop->schoolyear,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'KẾT QUẢ HỌC TẬP LỚP SỐNG ĐẠO ' . $lop->name . ' ' . $lop->schoolyear,
            ],
            [
                '',
            ],
            [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'STT',
                'Tên thánh',
                'Họ tên đệm',
                'Tên',
                'Ngày sinh',
                'Kỳ I',
                '',
                '',
                '',
                'Kỳ II',
                '',
                '',
                '',
                'TB cả năm',
                'Xếp loại',
                'Nghỉ lễ',
                'Bỏ học',
                'Hạnh kiểm',
                'Ghi chú',
            ],
            [
                'STT',
                'Tên thánh',
                'Họ tên đệm',
                'Tên',
                'Ngày sinh',
                'Bố',
                'Mẹ',
                'Giáo họ',
                'Số điện thoại',
                'Ghi chú',
                '',
                '',
                '',
                '',
                '',
                '',
                '8T',
                'KI',
                'Kinh',
                'Thi lại',
                '8T',
                'KII',
                'Kinh',
                'Thi lại'
            ],
        ];
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
                    if(!empty($hocsinh->phone)){
                        $hocsinh->phone = 0 . $hocsinh->phone;
                    }
                    
                    $lop = CatechismClass::where('id', $hocsinh->lop)->where('status', 1)->first();
                    
                    if(!empty($lop->block)){
                        $block = Block::where('id', $lop->block)->where('status', 1)->first();
                        $lop['block'] = $block->name;
                    }
                    
                    $weeks_one = $this->week_tow($lop->start_date_one, $lop->end_date_one);
                    
                    $weeks_two = $this->week_tow($lop->start_date_two, $lop->end_date_two);
                    
                    $weeks = $weeks_one + $weeks_two;
                    
                    $day_one = $this->days_tow($lop->start_date_one, $lop->end_date_one);
                    
                    $day_two = $this->days_tow($lop->start_date_two, $lop->end_date_two);
                    
                    $days = $day_one + $day_two;
                    
                    $hocsinh->diem = DiemThi::where('ihv', $hocsinh->id)->where('lop', $hocsinh->lop)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
                    
                    $hocsinh->weeks = $weeks;
                    $hocsinh->days = $days;
                    
                    if($hocsinh->diem){
                        return [
                            $this->rowNumber,
                            $hocsinh->holy,
                            $hocsinh->last_name,
                            $hocsinh->name,
                            $hocsinh->birthday,
                            $hocsinh->father,
                            $hocsinh->mother,
                            $hocsinh->paid,
                            $hocsinh->phone,
                            $hocsinh->note,
                            '',
                            $this->rowNumber,
                            $hocsinh->holy,
                            $hocsinh->last_name,
                            $hocsinh->name,
                            $hocsinh->birthday,
                            $hocsinh->diem->tuan1,
                            $hocsinh->diem->k1,
                            $hocsinh->diem->kinh1,
                            $hocsinh->diem->kq1,
                            $hocsinh->diem->tuan2,
                            $hocsinh->diem->k2,
                            $hocsinh->diem->kinh2,
                            $hocsinh->diem->kq2,
                            $hocsinh->diem->canam,
                            $hocsinh->diem->seploai,
                            $hocsinh->diem->nghile . '/' . $hocsinh->days,
                            $hocsinh->diem->bohoc . '/' . $hocsinh->weeks,
                            $hocsinh->diem->hanhkiem,
                            $hocsinh->diem->ghichu,
                        ];
                    }else{
                        return [
                            $this->rowNumber,
                            $hocsinh->holy,
                            $hocsinh->last_name,
                            $hocsinh->name,
                            $hocsinh->birthday,
                            $hocsinh->father,
                            $hocsinh->mother,
                            $hocsinh->paid,
                            $hocsinh->phone,
                            $hocsinh->note,
                            '',
                            $this->rowNumber,
                            $hocsinh->holy,
                            $hocsinh->last_name,
                            $hocsinh->name,
                            $hocsinh->birthday,
                        ];
                    }
                }
            }else{
                return [];                
            }
        }else{
            return [];
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
        
        $sheet->getStyle('L1:AD1')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  14,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('L1:AD1');
        $sheet->mergeCells('L3:L4');
        $sheet->mergeCells('M3:M4');
        $sheet->mergeCells('N3:N4');
        $sheet->mergeCells('O3:O4');
        $sheet->mergeCells('P3:P4');
        $sheet->mergeCells('Q3:T3');
        $sheet->mergeCells('U3:X3');
        $sheet->mergeCells('Y3:Y4');
        $sheet->mergeCells('Z3:Z4');
        $sheet->mergeCells('P3:P4');
        $sheet->mergeCells('AA3:AA4');
        $sheet->mergeCells('AB3:AB4');
        $sheet->mergeCells('AC3:AC4');
        $sheet->mergeCells('AD3:AD4');
        
        $lastRow = $sheet->getHighestRow();
        $range = 'A4:J' . $lastRow;
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
        $range_last = 'L3:AD' . $lastRow;
        
        $sheet->getStyle($range_last)->applyFromArray([
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
        
        $sheet->getStyle('A4:J4')->applyFromArray([
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
        $sheet->getStyle('L3:AD4')->applyFromArray([
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
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
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
    
    
    public function week_tow($strtDate, $endDate) {
        $startDateWeekCnt = round(floor( date('d',strtotime($strtDate)) / 7)) ;
        
        $endDateWeekCnt = round(ceil( date('d',strtotime($endDate)) / 7)) ;
        
        $datediff = strtotime(date('Y-m',strtotime($endDate))."-01") - strtotime(date('Y-m',strtotime($strtDate))."-01");
        $totalnoOfWeek = round(floor($datediff/(60*60*24)) / 7) + $endDateWeekCnt - $startDateWeekCnt ;
        
        return $totalnoOfWeek;
    }
    
    public function days_tow($start_day, $end_day){
        $begin = Carbon::parse($start_day);
        $end = Carbon::parse($end_day);
        
        $interval = \DateInterval::createFromDateString('1 day');
        
        $period = New \DatePeriod($begin, $interval, $end);
        
        $array_day = array();
        foreach($period as $date_one){
            $day = date('l', strtotime($date_one->format("Y-m-d")));
            if($day == 'Thursday' OR $day == 'Sunday'){
                $array_day[$date_one->format("Y-m-d")] = $day;
            }
        }
        
        return count($array_day);
    }
}
