<?php

namespace App\Imports;

use App\Models\User;
//use Maatwebsite\Excel\Concerns\ToModel;
//use Maatwebsite\Excel\Concerns\ToArray;
//use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\ParishManagement;
use App\Models\Parish;
use App\Models\Association;
use App\Models\Holymanagement;
use App\Models\Positionmanagement;
use App\Models\Levelmanagement;
use App\Models\Careermanagement;
use App\Models\Languagemanagement;
use App\Models\Ethnicmanagement;
use App\Models\SacramentGiver;
use App\Models\Sponsor;
use App\Models\Parishioners;
use Carbon\Carbon;

//use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Http\Controllers\ParishionersController;
//PhpOffice\PhpSpreadsheet\Shared\Date
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\CatechismClass;
use App\Http\Controllers\LopController;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use App\Models\Student;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class LophocImport implements ToModel, ToCollection, WithHeadingRow, HasReferencesToOtherSheets
{
    private Slugify $slugify;
    
    protected $sheetName;
    
    public function __construct($sheetName)
    {
        $this->sheetName = $sheetName;
        $this->slugify = new Slugify();
    }
    
    public function headingRow(): int
    {
        return 1;
    }
    
    public function model(array $row)
    {
        @include(resource_path().'/cities/tinh_thanhpho.php');
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        if(!empty($_POST)){
            $userId = Auth::id();
            if(!empty($_POST['giaoxu'])){
                $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();                
            }else{
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                $giaoxu = $decen->pid;
            }
            
            if(!empty($decen) AND $decen->student == 1){
                $tensheet = $this->sheetName;
                
                $str = strtolower($tensheet);
                $str = preg_replace([
                    "/[àáạảãâầấậẩẫăằắặẳẵ]/u",
                    "/[èéẹẻẽêềếệểễ]/u",
                    "/[ìíịỉĩ]/u",
                    "/[òóọỏõôồốộổỗơờớợởỡ]/u",
                    "/[ùúụủũưừứựửữ]/u",
                    "/[ỳýỵỷỹ]/u",
                    "/[đ]/u"
                ], [
                    "a", "e", "i", "o", "u", "y", "d"
                ], $str);
                
                $array_ten = explode(' ', $str);
                $array_moi = array();
                foreach($array_ten as $ten){
                    $array_moi[] = mb_substr($ten, 0, 1);
                }
                $array_moi = implode('', $array_moi);
                $last_char = substr($str, -1);
                $symbol = $array_moi . $last_char;
                
                $lop = CatechismClass::where('name', $tensheet)
                ->where('did', $decen->did)
                ->where('deid', $decen->deid)
                ->where('pid', $giaoxu)
                ->get()->first();
                
                if(!empty($lop)){
                    CatechismClass::where('id', $lop->id)
                    ->update(['schoolyear' => $_POST['schoolyear']]);
                }else{
                    $lop = CatechismClass::create([
                        'name'          => $tensheet,
                        'did'           => $decen->did,
                        'deid'          => $decen->deid,
                        'pid'           => $giaoxu,
                        'symbol'        => $symbol,
                        'schoolyear'   => $_POST['schoolyear'],
                        'status'        => 1,
                    ]);                    
                }
                
                $sluglink = $this->slugify->slugify(request()->slug ?? $tensheet);
                $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
                //print_r($lop->id);die;
                if(!empty($slug)){
                    if($slug->sluggable_id != $lop->id){
                        $slugmoi = $sluglink . '-' . $lop->id;
                        $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $lop->id)->get()->first();                        
                        if(empty($checkslug)){
                            Slug::create([
                                'keyword' => $slugmoi,
                                'controller' => LopController::class,
                                'model' => CatechismClass::class,
                                'sluggable_id' => $lop->id
                            ]);                            
                        }
                    }
                }else{
                    Slug::create([
                        'keyword' => $sluglink,
                        'controller' => LopController::class,
                        'model' => CatechismClass::class,
                        'sluggable_id' => $lop->id
                    ]);
                }
                
                // giáo họ                
                if(!empty($row['giao_ho'])){
                    $giaoho = Parish::where('name', $row['giao_ho'])
                    ->where('did', $decen->did)
                    ->where('deid', $decen->deid)
                    ->where('pid', $giaoxu)
                    ->get()->first();
                    if(empty($giaoho)){
                        $giaoho = Parish::create([
                            'did'           => $decen->did,
                            'deid'          => $decen->deid,
                            'pid'           => $giaoxu,
                            'name'          => $row['giao_ho'],
                            'status'        => 1,
                        ]);
                        $row['giao_ho'] = $giaoho->id;
                    }else{
                        $row['giao_ho'] = $giaoho->id;
                    }
                }
                
                if(!empty($row['ten_thanh'])){
                    $tenthanh = Holymanagement::where('name', $row['ten_thanh'])->first();
                    if(!empty($tenthanh)){
                        $row['ten_thanh'] = $holy = $tenthanh->id;
                    }else{
                        $data['name'] = $row['ten_thanh'];
                        $holym = Holymanagement::create($data);
                        $row['ten_thanh'] = $holym->id;
                    }
                }
                if(!empty($row['ngay_sinh'])){
                    if(strlen($row['ngay_sinh']) != 8){
                        $row['ngay_sinh'] = str_replace('`', '', $row['ngay_sinh']);
                        $row['ngay_sinh'] = str_replace("//", "/", $row['ngay_sinh']);
                    }
                    $array_sinh = explode('/', $row['ngay_sinh']);
                    if(count($array_sinh) == 3){
                        $count = strlen($array_sinh['2']);
                        if($count == 4){
                            $row['ngay_sinh'] = $array_sinh['2'] . '-' . $array_sinh['1'] . '-' . $array_sinh[0];
                        }
                    }
                }
                
                if(!empty($row['tinh_tp'])){
                    $row['tinh_tp'] = array_search($row['tinh_tp'], $tinh_thanhpho);
                }elseif(!empty($row['tinhtp'])){
                    $row['tinh_tp'] = array_search($row['tinhtp'], $tinh_thanhpho);
                }else{
                    $row['tinh_tp'] = '0';
                }
                
                if(!empty($row['xa_phuong'])){
                    $ten_can_tim = $row['xa_phuong'];
                    $matp_can_tim = $row['tinh_tp'];
                    $ket_qua = array_filter($xa_phuong_thitran, function($item) use ($ten_can_tim, $matp_can_tim) {
                        return $item['name'] === $ten_can_tim && $item['matp'] === $matp_can_tim;
                    });
                    foreach($ket_qua as $xaphuong){
                        $row['xa_phuong'] = $xaphuong['xaid'];
                    }
                }else{
                    $row['xa_phuong'] = '0';
                }
                /*
                if(!empty($row['xa_phuong'])){
                    $row['xa_phuong'] = array_search('Xã ' . $row['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                    if(empty($row['xa_phuong'])){
                        $row['xa_phuong'] = array_search('Phường ' . $row['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                    }
                }elseif(!empty($row['xaphuong'])){
                    $row['xa_phuong'] = array_search('Xã ' . $row['xaphuong'], array_column($xa_phuong_thitran, 'name'));
                    if(empty($row['xaphuong'])){
                        $row['xa_phuong'] = array_search('Phường ' . $row['xaphuong'], array_column($xa_phuong_thitran, 'name'));
                    }
                }else{
                    $row['xa_phuong'] = '0';
                }
                */
                    
                if(!empty($row['gioi_tinh'])){
                    if($row['gioi_tinh'] == 'Nữ'){
                        $row['gioi_tinh'] = 0;
                    }else{
                        $row['gioi_tinh'] = 1;
                    }
                }else{
                    $row['gioi_tinh'] = 0;
                }
                
                if(!empty($row['ten'])){
                    $hocsinh = Student::where('did', $decen->did)
                    ->where('deid', $decen->deid)
                    ->where('pid', $giaoxu)
                    ->where('holy', $row['ten_thanh'])
                    ->where('name', $row['ten'])
                    ->where('father', $row['ten_cha'])
                    ->where('mother', $row['ten_me'])
                    ->get()
                    ->first();
                    
                    if(empty($hocsinh)){
                        $student = Student::create([
                            'did'                   => $decen->did,
                            'deid'                  => $decen->deid,
                            'pid'                   => $giaoxu,
                            'paid'                  => $row['giao_ho'],
                            'mahv'                  => $row['ma_hv'],
                            'magd'                  => $row['stt'],
                            'magdcg'                => 1,
                            'lop'                   => $lop->id,
                            'holy'                  => $row['ten_thanh'],
                            'last_name'             => $row['ho_ten_dem'],
                            'name'                  => $row['ten'],
                            'sex'                   => $row['gioi_tinh'],
                            'birthday'              => $row['ngay_sinh'],
                            'phone'                 => $row['dien_thoai'],
                            'origin'                => $row['dia_chi'],
                            'ward'                  => $row['xa_phuong'],
                            'province'              => $row['tinh_tp'],
                            'father'                => $row['ten_cha'],
                            'mother'                => $row['ten_me'],
                            'cccd'                  => $row['cmndcan_cuoc'],
                            'email'                 => $row['email'],
                            'baptism_date'          => $row['ngay_rua_toi'],
                            'baptism_number'        => $row['so_rua_toi'],
                            'baptism_giver'         => $row['nguoi_ban_bi_tich_rua_toi'],
                            'baptism_sponsor'       => $row['nguoi_do_dau_rua_toi'],
                            'baptism_dioceses'      => $row['noi_rua_toi'],
                            'baptism_deanerys'      => $row['noi_rua_toi'],
                            'baptism_parish'        => $row['noi_rua_toi'],
                            'more_power_date'       => $row['ngay_them_suc'],
                            'more_power_number'     => $row['so_them_suc'],
                            'more_power_giver'      => $row['nguoi_ban_bi_tich_them_suc'],
                            'more_power_sponsor'    => $row['nguoi_do_dau_them_suc'],
                            'more_power_address'    => $row['noi_them_suc'],
                            'more_power_dioceses'   => $row['noi_them_suc'],
                            'more_power_deanerys'   => $row['noi_them_suc'],
                            'more_power_parish'     => $row['noi_them_suc'],
                            'promise_day'           => $row['ngay_tuyen_hua'],
                            'note'                  => $row['ghi_chu'],
                            'status'                => 1,
                        ]);
                        $id = $student->id;
                        
                        if(empty($row['ma_hv'])){
                            
                            $row['ma_hv'] = $symbol . $student->id;
                            Student::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $giaoxu)
                            ->where('paid', $row['giao_ho'])
                            ->where('lop', $lop->id)
                            ->where('holy', $row['ten_thanh'])
                            ->where('name', $row['ten'])
                            ->update([
                                'mahv'  => $row['ma_hv'],
                            ]);
                        }
                    }else{
                        if(empty($row['ma_hv'])){
                            $row['ma_hv'] = $symbol . $hocsinh->id;
                        }
                        $id = $hocsinh->id;
                        Student::where('id', $hocsinh->id)
                        ->where('did', $decen->did)
                        ->where('deid', $decen->deid)
                        ->where('pid', $giaoxu)
                        ->where('lop', $lop->id)
                        ->where('holy', $row['ten_thanh'])
                        ->where('name', $row['ten'])
                        ->update([
                            'mahv'                  => $row['ma_hv'],
                            'magd'                  => $row['stt'],
                            'magdcg'                => '0',
                            'paid'                  => $row['giao_ho'],
                            'last_name'             => $row['ho_ten_dem'],
                            'sex'                   => $row['gioi_tinh'],
                            'birthday'              => $row['ngay_sinh'],
                            'phone'                 => $row['dien_thoai'],
                            'origin'                => $row['dia_chi'],
                            'ward'                  => $row['xa_phuong'],
                            'province'              => $row['tinh_tp'],
                            'father'                => $row['ten_cha'],
                            'mother'                => $row['ten_me'],
                            'cccd'                  => $row['cmndcan_cuoc'],
                            'email'                 => $row['email'],
                            'baptism_date'          => $row['ngay_rua_toi'],
                            'baptism_number'        => $row['so_rua_toi'],
                            'baptism_giver'         => $row['nguoi_ban_bi_tich_rua_toi'],
                            'baptism_sponsor'       => $row['nguoi_do_dau_rua_toi'],
                            'baptism_dioceses'      => $row['noi_rua_toi'],
                            'baptism_deanerys'      => $row['noi_rua_toi'],
                            'baptism_parish'        => $row['noi_rua_toi'],
                            'more_power_date'       => $row['ngay_them_suc'],
                            'more_power_number'     => $row['so_them_suc'],
                            'more_power_giver'      => $row['nguoi_ban_bi_tich_them_suc'],
                            'more_power_sponsor'    => $row['nguoi_do_dau_them_suc'],
                            'more_power_address'    => $row['noi_them_suc'],
                            'more_power_dioceses'   => $row['noi_them_suc'],
                            'more_power_deanerys'   => $row['noi_them_suc'],
                            'more_power_parish'     => $row['noi_them_suc'],
                            'promise_day'           => $row['ngay_tuyen_hua'],
                            'note'                  => $row['ghi_chu'],
                            'status'                => 1,
                        ]);
                    }
                    
                    $sluglink = $this->slugify->slugify(request()->slug ?? $row['ten']);
                    $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
                    if(!empty($slug)){
                        if($slug->sluggable_id != $id){
                            $slugmoi = $sluglink . '-' . $id;
                            $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $id)->get()->first();
                            if(empty($checkslug)){
                                Slug::create([
                                    'keyword' => $slugmoi,
                                    'controller' => StudentController::class,
                                    'model' => Student::class,
                                    'sluggable_id' => $id
                                ]);
                            }
                        }
                    }else{
                        Slug::create([
                            'keyword' => $sluglink,
                            'controller' => StudentController::class,
                            'model' => Student::class,
                            'sluggable_id' => $id
                        ]);
                    }
                }
            }else{
                return back()->withErrors('Lỗi, Bạn chọn sai xứ');
            }
        }
    }
    
    public function collection(Collection $rows)
    {
        /*
        foreach ($rows as $row) {
            
        }
        */
    }
    
}
