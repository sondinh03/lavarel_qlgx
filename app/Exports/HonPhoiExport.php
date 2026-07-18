<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithMapping;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style;
use PhpOffice\PhpSpreadsheet\Style\Style as DefaultStyles;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use App\Models\MarriageAnnouncement;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\FromQuery;
use App\Models\MarriageParishioner;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\ParishManagement;
use App\Models\Deanery;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use App\Models\Marriage;
use App\Models\Diocese;
use App\Models\ParishGroup;

class HonPhoiExport implements FromCollection, WithHeadingRow, WithHeadings, WithTitle, ShouldAutoSize, WithMapping, WithEvents, WithStyles
{
    use RegistersEventListeners;
    public $rowNumber = 0;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $user = backpack_user();
        $userId = $user->id;
        $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
        if(!empty($_POST['date'])){
            $honphoi = MarriageAnnouncement::where('pid', $decen->pid)
            ->orWhere('announcements_one', $_POST['date'])
            ->orWhere('announcements_two', $_POST['date'])
            ->orWhere('announcements_two', $_POST['date'])
            ->where('status', '1')
            ->get();
        }else{
            $honphoi = array();
        }
        return $honphoi;
    }
    
    public function title():string
    {
        return 'Danh sachs rao hôn phối';
    }
    
    public function headings(): array
    {
        if(!empty($_POST['giaoxu'])){
            $parist = ParishManagement::where('id', $_POST['giaoxu'])->where('status', 1)->get()->first();
            $giaoxu = $parist->name;
        }else{
            $giaoxu = '';
        }
        if(!empty($_POST['date']) AND strlen($_POST['date']) == 10){
            $date = date('d/m/Y', strtotime($_POST['date']));
        }else{
            $date = '';
        }
        return [
            [
                ' ',
            ],
            [
                'DANH SÁCH RAO HÔN PHỐI ' . $date . ' ' . $giaoxu,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
            ],
            [
                ' ',
            ],
            [
                'STT',
                'Lần rao',
                'Phái',
                'Tên thánh',
                'Họ tên đệm',
                'Tên',
                'Năm sinh',
                'Tên cha',
                'Tên mẹ',
                'Hiện ở xứ',
                'Trước ở xứ',
            ],
        ];
    }
    
    public function map($row): array
    {
        $user = backpack_user();
        $userId = $user->id;
        $decen = Decen::where('use', $userId)->where('pid', $row->pid)->where('status', '1')->get()->first();
        if(!empty($decen) AND $decen->parish == 1){
            $this->rowNumber++;        
            $array_data = $array_nam = $array_nu = array();
            $rao = '';
            $tiep = 0;
            if(!empty($row->announcements_one) AND $row->announcements_one == $_POST['date']){
                $rao = '1';
                $tiep = 1;
            }
            if(!empty($row->announcements_two) AND $row->announcements_two == $_POST['date']){
                $rao = '2';
                $tiep = 1;
            }
            if(!empty($row->announcements_three) AND $row->announcements_three == $_POST['date']){
                $rao = '3';
                $tiep = 1;
            }
                
            if(!empty($tiep)){
                $array_data = array(
                    $this->rowNumber,
                    $rao,
                );
                $honphoi = MarriageParishioner::where('idannouncement', $row->id)->where('status', 1)->orderBy('sex', 'desc')->get()->toArray();
                foreach($honphoi as $item){
                    if(!empty($item['sex'])){
                        $item['sex'] = 'Nam';
                        $sex = 1;
                    }else{
                        $sex = 0;
                        $item['sex'] = 'Nữ';
                    }
                    $parishioners = Parishioners::where('id', $item['idgiaodan'])->where('status', 1)->get()->first();
                    if(!empty($parishioners->holy)){
                        $holy = Holymanagement::where('id', $parishioners->holy)->get()->first();
                        $item['holy'] = $holy->name;
                    }
                    $item['last_name'] = $parishioners->last_name;
                    $item['name'] = $parishioners->name;
                    if(!empty($parishioners->birthday) AND strlen($parishioners->birthday) == 10){
                        $item['birthday'] = date('d-m-Y', strtotime($parishioners->birthday));
                    }else{
                        $item['birthday'] = '';
                    }
                    if(!empty($parishioners->father)){
                        $item['father'] = $parishioners->father;
                    }else{
                        $item['father'] = '';
                    }
                    if(!empty($parishioners->mother)){
                        $item['mother'] = $parishioners->mother;
                    }else{
                        $item['mother'] = '';
                    }
                    if(!empty($item['parishs'])){
                        $giaoho = ParishGroup::where('id', $item['parishs'])->where('status', 1)->get()->first();
                        $item['giaoho'] = $giaoho->name . ', ';
                    }else{
                        $item['giaoho'] = '';
                    }
                    if(!empty($item['parishmanagements'])){
                        $giaoxu = ParishManagement::where('id', $item['parishmanagements'])->where('status', 1)->get()->first();
                        $item['giaoxu'] = $giaoxu->name;
                    }else{
                        $item['giaoxu'] = '';
                    }
                    if(!empty($item['deanerys'])){
                        $giaohat = Deanery::where('id', $item['deanerys'])->where('status', 1)->get()->first();
                        $item['giaohat'] = ', ' . $giaohat->name;
                    }else{
                        $item['giaohat'] = '';
                    }
                    if(!empty($item['dioceses'])){
                        $giaophan = Diocese::where('id', $item['dioceses'])->where('status', 1)->get()->first();
                        $item['giaophan'] = ', ' . $giaophan->name;
                    }else{
                        $item['giaophan'] = '';
                    }
                    // trước đây
                    if(!empty($item['parishsbefore'])){
                        $giaoho = ParishGroup::where('id', $item['parishsbefore'])->where('status', 1)->get()->first();
                        $item['giaoho_before'] = $giaoho->name . ', ';
                    }else{
                        $item['giaoho_before'] = '';
                    }
                    if(!empty($item['parishmanagementsbefore'])){
                        $giaoxu = ParishManagement::where('id', $item['parishmanagementsbefore'])->where('status', 1)->get()->first();
                        $item['giaoxu_before'] = $giaoxu->name;
                    }else{
                        $item['giaoxu_before'] = '';
                    }
                    if(!empty($item['deanerysbefore'])){
                        $giaohat = Deanery::where('id', $item['deanerysbefore'])->where('status', 1)->get()->first();
                        $item['giaohat_before'] = ', ' . $giaohat->name;
                    }else{
                        $item['giaohat_before'] = '';
                    }
                    if(!empty($item['diocesesbefore'])){
                        $giaophan = Diocese::where('id', $item['diocesesbefore'])->where('status', 1)->get()->first();
                        $item['giaophan_before'] = ', ' . $giaophan->name;
                    }else{
                        $item['giaophan_before'] = '';
                    }
                    
                    if(!empty($sex)){
                        $array_nam = array(
                            $item['sex'],
                            $item['holy'],
                            $item['last_name'],
                            $item['name'],
                            $item['birthday'],
                            $item['father'],
                            $item['mother'],
                            $item['giaoho'] . $item['giaoxu'] . $item['giaohat'] . $item['giaophan'],
                            $item['giaoho_before'] . $item['giaoxu_before'] . $item['giaohat_before'] . $item['giaophan_before'],
                        );
                    }else{
                        $array_nu = array(
                            '',
                            '',
                            $item['sex'],
                            $item['holy'],
                            $item['last_name'],
                            $item['name'],
                            $item['birthday'],
                            $item['father'],
                            $item['mother'],
                            $item['giaoho'] . $item['giaoxu'] . $item['giaohat'] . $item['giaophan'],
                            $item['giaoho_before'] . $item['giaoxu_before'] . $item['giaohat_before'] . $item['giaophan_before'],
                        );
                    }
                    
                }
                $array_data = array_merge($array_data, $array_nam);
                
                return array($array_data, $array_nu);
            }else{
                return array();
            }
        }else{
            return array();
        }
    }
    
    public function styles(Worksheet $sheet) {
        $sheet->setShowGridlines(false);
        
        $sheet->mergeCells('A2:K2');
        
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold'      => TRUE,
                'name'      =>  'Times New Roman',
                'size'      =>  18,
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);
        
        $sheet->getStyle('A4:K4')->applyFromArray([
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
        ]);
        
        $lastRow = $sheet->getHighestRow();
        
        $range = 'A5:K' . $lastRow;
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
        ]);
        
        $array_cap = array();
        for($i = 5; $i <= $lastRow; $i++){
            $array_cap[] = $i;
        }
        $array_cap = array_chunk($array_cap, 2);
        
        foreach($array_cap as $item){
            $sheet->mergeCells('A' . $item['0'] . ':A' . $item['1']);
            $sheet->mergeCells('B' . $item['0'] . ':B' . $item['1']);
        }
        
        $range = 'A5:B' . $lastRow;
        $sheet->getStyle($range)->applyFromArray([
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
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
}
