<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;

use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\ParishManagement;
use App\Models\Parish;
use App\Models\Children;
use App\Models\Family;
use App\Models\FamilyArea;
use App\Models\SacramentGiver;
use App\Models\Marriage;
use App\Models\GiaDinh;
use App\Models\Child;

use App\Models\Slug;
use Cocur\Slugify\Slugify;

use App\Http\Controllers\FamilyController;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class SecondSheetImport implements ToModel, ToArray, HasReferencesToOtherSheets
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $rows)
    {
        /*
        print_r($rows);die;
        return new Family([
            'name' => $row[0],
        ]);*/
    }
    
    public function array(array $rows)
    {
        @include(resource_path().'/cities/tinh_thanhpho.php');
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->parish == 1){
                foreach ($rows as $key => $item){
                    if($key > 0){
                        $row['ma_gia_dinh']                     = $item[0];
                        $row['ten_gia_dinh']                    = $item[1];
                        $row['ma_gd_nam']                       = $item[2];
                        $row['nguoi_nam' ]                      = $item[3];
                        $row['ma_gd_nu']                        = $item[4];
                        $row['nguoi_nu']                        = $item[5];
                        $row['ma_gd_thanh_vien']                = $item[6];
                        $row['so_nguoi']                        = $item[7];
                        $row['dien_thoai']                      = $item[8];
                        $row['dien_thoai_chong']                = $item[9];
                        $row['dien_thoai_vo']                   = $item[10];
                        $row['dia_chi']                         = $item[11];
                        $row['xa_phuong']                       = $item[12];
                        $row['tinh_tp']                         = $item[13];
                        $row['giao_ho']                         = $item[14];
                        $row['giao_xu']                         = $item[15];
                        $row['giao_hat']                        = $item[16];
                        $row['giao_phan']                       = $item[17];
                        $row['noio']                            = $item[18];
                        $row['dien_gia_dinh']                   = $item[19];
                        $row['thongke']                         = $item[20];
                        $row['ghi_chu']                         = $item[21];
                        $row['so_hon_phoi']                     = $item[22];
                        $row['ngay_hon_phoi']                   = $item[23];
                        $row['noi_hon_phoi']                    = $item[24];
                        $row['xa_phuong_honphoi']               = $item[25];
                        $row['tinh_tp_honphoi']                 = $item[26];
                        $row['linh_muc_chung']                  = $item[27];
                        $row['nguoi_chung_1']                   = $item[28];
                        $row['nguoi_chung_2']                   = $item[29];
                        $row['tinh_trang_hon_phoi']             = $item[30];
                        $row['ghi_chu_hon_phoi']                = $item[31];                
                        
                        if(!empty($row['giao_phan'])){
                            $diocese = Diocese::where('name', 'like', '%' . $row['giao_phan'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            if($diocese->id == $_POST['giaophan']){
                                $row['giao_phan'] = $diocese->id;
                                
                                if(!empty($row['giao_hat'])){
                                    $deanery = Deanery::where('name', 'like', '%' . $row['giao_hat'] . '%')->where('did', $row['giao_phan'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                    if($deanery->id == $_POST['giaohat']){
                                        $row['giao_hat'] = $deanery->id;
                                        
                                        if(!empty($row['giao_xu'])){
                                            $parishManagement = ParishManagement::where('name', 'like', '%' . $row['giao_xu'] . '%')->where('diocese', $row['giao_phan'])->where('deanerys', $row['giao_hat'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                            if($parishManagement->id == $_POST['giaoxu']){
                                                $row['giao_xu'] = $parishManagement->id;
                                            }else{
                                                return back()->withErrors('Lỗi, File excel và chọn giáo xứ không khớp');
                                            }
                                        }
                                    }else{
                                        return back()->withErrors('Lỗi, File excel và chọn giáo hạt không khớp');
                                    }
                                }
                            }else{
                                return back()->withErrors('Lỗi, File excel và chọn giáo phận không khớp');
                            }
                        }
                        
                        if(!empty($row['giao_ho'])){
                            if(is_numeric($row['giao_phan']) AND is_numeric($row['giao_hat']) AND is_numeric($row['giao_xu'])){
                                $parishs = Parish::where('name', 'like', '%' . $row['giao_ho'] . '%')->where('did', $row['giao_phan'])->where('deid', $row['giao_hat'])->where('pid', $row['giao_xu'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                            }else{
                                $parishs = Parish::where('name', 'like', '%' . $row['giao_ho'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            }
                            if(!empty($parishs)){
                                $row['giao_ho'] = $parishs->id;
                            }else{
                                $row['giao_ho'] = '0';
                            }
                        }
                        
                        if(!empty($row['ngay_hon_phoi'])){
                            if(strlen($row['ngay_hon_phoi']) == 4){
                                $row['ngay_hon_phoi'] = $row['ngay_hon_phoi'] . '-01-01';
                            }elseif(strlen($row['ngay_hon_phoi']) == 5){
                                $excel_date = $row['ngay_hon_phoi']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_hon_phoi'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_hon_phoi'] = date("Y-m-d", strtotime($row['ngay_hon_phoi']));
                            }
                        }else{
                            $row['ngay_hon_phoi'] = NULL;
                        }
                        
                        if(!empty($row['father'])){
                            $row['idhouse'] = 1;
                        }else{
                            $row['idhouse'] = 0;
                        }
                        
                        if(!empty($row['dien_gia_dinh'])){
                            $FamilyArea = FamilyArea::where('name', 'like', '%' . $row['dien_gia_dinh'] . '%')->where('status', '=', 1)->orderBy('created_at', 'desc')->first();
                            if(!empty($FamilyArea->id)){
                                $row['dien_gia_dinh'] = $FamilyArea->id;
                            }else{
                                $data['name'] = $row['dien_gia_dinh'];
                                $familyareas = FamilyArea::create($data);
                                $row['dien_gia_dinh'] = $familyareas->id;
                            }
                        }
                        
                        if($row['noio'] == 'x'){
                            $row['noio'] = 1;
                        }else{
                            $row['noio'] = 0;
                        }
                        
                        if($row['thongke'] == 'x'){
                            $row['thongke'] = 1;
                        }else{
                            $row['thongke'] = NULL;
                        }
                        
                        if(empty($row['so_hon_phoi'])){
                            $row['so_hon_phoi'] = NULL;
                        }
                        
                        $sacrament = SacramentGiver::where('name', 'like', '%' . $row['linh_muc_chung'] . '%')->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $row['linh_muc_chung'] = $sacrament->id;
                        }else{
                            $data['name'] = $row['linh_muc_chung'];
                            $sacrament = SacramentGiver::create($data);
                            $row['linh_muc_chung'] = $sacrament->id;
                        }
                        
                        if(!empty($row['tinh_tp'])){
                            $row['tinh_tp'] = array_search($row['tinh_tp'], $tinh_thanhpho);
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
                        }
                        /*
                        foreach($xa_phuong_thitran as $xaphuong){
                            if($row['xa_phuong'] == $xaphuong['name']){
                                $row['xa_phuong'] = $xaphuong['xaid'];
                            }
                        }
                        */
                        
                        if(!empty($row['tinh_tp_honphoi'])){
                            $row['tinh_tp_honphoi'] = array_search($row['tinh_tp_honphoi'], $tinh_thanhpho);
                        }
                        if(!empty($row['xa_phuong_honphoi'])){
                            $ten_can_tim = $row['xa_phuong_honphoi'];
                            $matp_can_tim = $row['tinh_tp_honphoi'];
                            $ket_qua = array_filter($xa_phuong_thitran, function($item) use ($ten_can_tim, $matp_can_tim) {
                                return $item['name'] === $ten_can_tim && $item['matp'] === $matp_can_tim;
                            });
                                foreach($ket_qua as $xaphuong){
                                    $row['xa_phuong_honphoi'] = $xaphuong['xaid'];
                                }
                        }
                        
                        /*
                        foreach($xa_phuong_thitran as $xaphuong){
                            if($row['xa_phuong_honphoi'] == $xaphuong['name']){
                                $row['xa_phuong_honphoi'] = $xaphuong['xaid'];
                            }
                        }
                        */
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
                        $status = array_search($row['tinh_trang_hon_phoi'], $tinhtrang);
                        
                        $family = GiaDinh::updateOrCreate(
                            [
                                'household' => $row['giao_xu'],
                                'name'      => $row['ten_gia_dinh'],
                                'did'       => $row['giao_phan'],
                                'deid'      => $row['giao_hat'],
                                'pid'       => $row['giao_xu'],
                                'paid'      => $row['giao_ho'],
                                'idhouse'   => $row['idhouse'],
                                'dien'      => $row['dien_gia_dinh'],
                                'songuoi'   => $row['so_nguoi'],
                                'phone'     => $row['dien_thoai'],
                                'origin'    => $row['dia_chi'],
                                'ward'      => $row['xa_phuong'],
                                'province'  => $row['tinh_tp'],
                                'noio'      => $row['noio'],
                                'thongke'   => $row['thongke'],
                                'note'      => $row['ghi_chu'],
                                'status'    => 1,
                            ],
                            [
                                'id'        => $row['ma_gia_dinh'],
                                'mother'    => $row['ma_gd_nu'],
                                'father'    => $row['ma_gd_nam'],
                            ]
                        );
                        
                        if(!empty($family->id) AND !empty($row['ma_gd_thanh_vien'])){
                            $row['ma_gd_thanh_vien'] = explode('-', $row['ma_gd_thanh_vien']);
                            foreach ($row['ma_gd_thanh_vien'] as $item){
                                Child::updateOrCreate(
                                    [
                                        'children_id'           => $item,
                                        'childrengable_id'      => $family->id,
                                        'childrengable_type'    => 'App\Models\Family',
                                    ]
                                );
                            }
                        }
                        
                        if(!empty($family->id)){
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
                            $status = array_search($row['tinh_trang_hon_phoi'], $tinhtrang);
                            
                            $family_new = Marriage::where('idfamily', $family->id)->orderBy('created_at', 'desc')->first();
        
                            if(!empty($family_new->id)){
                                $family_new->update([
                                    'date'              => $row['ngay_hon_phoi'],
                                    'sohonphoi'         => $row['so_hon_phoi'],
                                    'marriage_address'  => $row['noi_hon_phoi'],
                                    'marriage_ward'     => $row['xa_phuong_honphoi'],
                                    'marriage_province' => $row['tinh_tp_honphoi'],
                                    'priest'            => $row['linh_muc_chung'],
                                    'peopleone'         => $row['nguoi_chung_1'],
                                    'peopletwo'         => $row['nguoi_chung_2'],
                                    'tinhtrang'         => $status,
                                    'marriage_note'     => $row['ghi_chu_hon_phoi'],
                                ]);
                            }else{
                                $family_new = Marriage::create([
                                    'idfamily'          => $family->id,
                                    'date'              => $row['ngay_hon_phoi'],
                                    'sohonphoi'         => $row['so_hon_phoi'],
                                    'marriage_address'  => $row['noi_hon_phoi'],
                                    'marriage_ward'     => $row['xa_phuong_honphoi'],
                                    'marriage_province' => $row['tinh_tp_honphoi'],
                                    'priest'            => $row['linh_muc_chung'],
                                    'peopleone'         => $row['nguoi_chung_1'],
                                    'peopletwo'         => $row['nguoi_chung_2'],
                                    'tinhtrang'         => $status,
                                    'marriage_note'     => $row['ghi_chu_hon_phoi'],
                                ]);
                            }  
                            
                            $sluglink = $this->slugify->slugify(request()->slug ?? $family->name);
                            $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
                            if(!empty($slug)){
                                if($slug->sluggable_id != $family->id){
                                    $slugmoi = $sluglink . '-' . $family->id;
                                    $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $family->id)->get()->first();
                                    if(empty($checkslug)){
                                        Slug::create([
                                            'keyword' => $slugmoi,
                                            'controller' => FamilyController::class,
                                            'model' => Family::class,
                                            'sluggable_id' => $family->id
                                        ]);
                                    }
                                }
                            }else{
                                Slug::create([
                                    'keyword' => $sluglink,
                                    'controller' => FamilyController::class,
                                    'model' => Family::class,
                                    'sluggable_id' => $family->id
                                ]);
                            }
                        }
                    }
                }
            }else{
                return back()->withErrors('Lỗi, Bạn chọn sai xứ');
            }
        }
    }
}
