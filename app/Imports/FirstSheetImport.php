<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
use App\Models\Diocese;
use App\Models\Deanery;
use App\Models\ParishManagement;
use App\Models\ParishGroup;
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
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FirstSheetImport implements ToModel, ToArray, HasReferencesToOtherSheets, WithHeadingRow
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
        return new User([
            //
        ]);
        */
    }
    
    public function headingRow(): int
    {
        return 1;
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
                    if(!empty($item['giao_phan'])){
                        $diocese = Diocese::where('name', $item['giao_phan'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first();
                        if(!empty($diocese)){
                            if($diocese->id == $_POST['giaophan']){
                                $item['giao_phan'] = $diocese->id;
                            }
                        }
                    }
                    if(!empty($item['giao_hat'])){
                        $deanery = Deanery::where('name', $item['giao_hat'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first();
                        if(!empty($deanery)){
                            if($deanery->id == $_POST['giaohat']){
                                $item['giao_hat'] = $deanery->id;
                            }
                        }
                    }
                    if(!empty($item['giao_xu'])){
                        $parishmanagement = ParishManagement::where('name', $item['giao_xu'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first();
                        if(!empty($parishmanagement)){
                            if($parishmanagement->id == $_POST['giaoxu']){
                                $item['giao_xu'] = $parishmanagement->id;
                            }
                        }
                    }
                    if(!empty($item['giao_ho'])){
                        $parish = ParishGroup::where('name', $item['giao_ho'])
                        ->where('parish_id', $_POST['giaoxu'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first();
                        if(empty($parish)){
                            $giaoho = ParishGroup::create([
                                'name'      => $item['giao_ho'],
                                'parish_id' => $_POST['giaoxu'],
                                'status'    => true,
                            ]);
                            $item['giao_ho'] = $giaoho->id;
                        }else{
                            $item['giao_ho'] = $parish->id;
                        }
                    }
                    
                    if(!empty($item['hoi_doan'])){
                        $association = Association::where('name', $item['hoi_doan'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->first();
                        
                        if(!empty($association->id)){
                            $item['hoi_doan'] = $association->id;
                        }else{
                            $data['pid'] = $_POST['giaoxu'];
                            $data['deid'] = $_POST['giaohat'];
                            $data['did'] = $_POST['giaophan'];
                            $data['name'] = $item['hoi_doan'];
                            $data['status'] = 1;
                            
                            $hoidoan = Association::create($data);
                            
                            $item['hoi_doan'] = $hoidoan->id;
                        }
                    }
                    
                    if(!empty($item['ten_thanh'])){
                        $holy = Holymanagement::where('name', $item['ten_thanh'])->orderBy('created_at', 'desc')->get()->first();
                        if(!empty($holy->id)){
                            $item['ten_thanh'] = $holy->id;
                        }else{
                            $data['name'] = $$item['ten_thanh'];
                            $holym = Holymanagement::create($data);
                            $item['ten_thanh'] = $holym->id;
                        }
                    }
                    
                    if($item['phai'] == 'Nữ'){
                        $item['phai'] = 0;
                    }else{
                        $item['phai'] = 1;
                    }
                    
                    if(!empty($item['ngay_sinh'])){
                        if(strlen($item['ngay_sinh']) == 4){
                            $item['ngay_sinh'] = $item['ngay_sinh'] . '-01-01';
                        }elseif(strlen($item['ngay_sinh']) == 5){
                            $excel_date = $item['ngay_sinh']; //here is that value 41621 or 41631
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['ngay_sinh'] = gmdate("Y-m-d", $unix_date);
                            //$item['ngay_sinh'] = str_replace("/","-", $item['ngay_sinh']);
                        }else{
                            $item['ngay_sinh'] = date("Y-m-d", strtotime($item['ngay_sinh']));
                        }
                    }else{
                        $item['ngay_sinh'] = NULL;
                    }
                    
                    if(!empty($item['tinh_tp'])){
                        $item['tinh_tp'] = array_search($item['tinh_tp'], $tinh_thanhpho);
                    }
                    if(!empty($item['xa_phuong'])){
                        
                        $ten_can_tim = $item['xa_phuong'];
                        $matp_can_tim = $item['tinh_tp'];
                        
                        $ket_qua = array_filter($xa_phuong_thitran, function($item) use ($ten_can_tim, $matp_can_tim) {
                            return $item['name'] === $ten_can_tim && $item['matp'] === $matp_can_tim;
                        });
                        
                        foreach($ket_qua as $xaphuong){
                            $item['xa_phuong'] = $xaphuong['xaid'];
                        }
                    }
                    
                    /*
                    print_r($item['xa_phuong']);die;
                    $item['xa_phuong'] = array_search('Xã ' . $item['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                    if(empty($item['xa_phuong'])){
                        $item['xa_phuong'] = array_search('Phường ' . $item['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                    }*/
                    
                    
                    if(!empty($item['tinh_tp_tru_quan'])){
                        $item['tinh_tp_tru_quan'] = array_search($item['tinh_tp_tru_quan'], $tinh_thanhpho);
                    }
                    
                    if(!empty($item['xa_phuong_tru_quan'])){                        
                        $ten_can_tim = $item['xa_phuong_tru_quan'];
                        $matp_can_tim = $item['tinh_tp_tru_quan'];                        
                        $ket_qua = array_filter($xa_phuong_thitran, function($item) use ($ten_can_tim, $matp_can_tim) {
                            return $item['name'] === $ten_can_tim && $item['matp'] === $matp_can_tim;
                        });                            
                        foreach($ket_qua as $xaphuong){
                            $item['xa_phuong_tru_quan'] = $xaphuong['xaid'];
                        }
                    }
                    
                    /*
                    $item['xa_phuong_tru_quan'] = array_search('Xã ' . $item['xa_phuong_tru_quan'], array_column($xa_phuong_thitran, 'name'));
                    if(empty($item['xa_phuong_tru_quan'])){
                        $item['xa_phuong_tru_quan'] = array_search('Phường ' . $item['xa_phuong_tru_quan'], array_column($xa_phuong_thitran, 'name'));
                    }
                    */
                    
                    if(!empty($item['dan_toc'])){
                        $ethic = Ethnicmanagement::where('name', $item['dan_toc'])->orderBy('created_at', 'desc')->first();
                        if(!empty($ethic->id)){
                            $item['dan_toc'] = $ethic->id;
                        }else{
                            $data['name'] = $item['dan_toc'];
                            $dantoc = Ethnicmanagement::create($data);
                            $item['dan_toc'] = $dantoc->id;
                        }
                    }
                    
                    if(!empty($item['ngon_ngu'])){
                        $ngonngu = Languagemanagement::where('name', $item['ngon_ngu'])->orderBy('created_at', 'desc')->first();
                        if(!empty($ngonngu->id)){
                            $item['ngon_ngu'] = $ngonngu->id;
                        }else{
                            $data['name'] = $item['ngon_ngu'];
                            $ngonngu = Languagemanagement::create($data);
                            $item['dan_toc'] = $ngonngu->id;
                        }
                    }
                    
                    if(!empty($item['ngon_ngu'])){
                        $level = Levelmanagement::where('name', $item['ngon_ngu'] )->orderBy('created_at', 'desc')->first();
                        if(!empty($level->id)){
                            $item['ngon_ngu'] = $level->id;
                        }else{
                            $data['name'] = $item['ngon_ngu'];
                            $level = Levelmanagement::create($data);
                            $item['ngon_ngu'] = $level->id;
                        }
                    }
                    
                    if(!empty($item['nghe_nghiep'])){
                        $career = Careermanagement::where('name', $item['nghe_nghiep'])->orderBy('created_at', 'desc')->first();
                        if(!empty($career->id)){
                            $item['nghe_nghiep'] = $career->id;
                        }else{
                            $data['name'] = $item['nghe_nghiep'];
                            $career = Careermanagement::create($data);
                            $item['nghe_nghiep'] = $career->id;
                        }
                    }
                    
                    if(!empty($item['chuc_vu'])){
                        $position = Positionmanagement::where('name', $item['chuc_vu'])->orderBy('created_at', 'desc')->first();
                        if(!empty($position->id)){
                            $item['chuc_vu'] = $position->id;
                        }else{
                            $data['name'] = $item['chuc_vu'];
                            $position = Positionmanagement::create($data);
                            $item['chuc_vu'] = $position->id;
                        }
                    }
                    
                    if($item['giao_duc'] == 'Đang học'){
                        $item['giao_duc'] = 1;
                    }elseif($item['giao_duc'] == 'Đã học xong'){
                        $item['giao_duc'] = 2;
                    }else{
                        $item['giao_duc'] = 3;
                    }
                    
                    if(!empty($item['tan_tong'])){
                        $item['tan_tong'] = 1;
                    }
                    
                    if(!empty($item['co_gia_dinh'])){
                        $item['co_gia_dinh'] = 1;
                    }
                    
                    if(!empty($item['thong_ke'])){
                        $item['thong_ke'] = 1;
                    }
                    
                    if(!empty($item['ngay_rua_toi'])){
                        if(strlen($item['ngay_rua_toi']) == 4){
                            $item['ngay_rua_toi'] = $item['ngay_rua_toi'] . '-01-01';
                        }elseif(strlen($item['ngay_rua_toi']) == 5){
                            $excel_date = $item['ngay_rua_toi'];
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['ngay_rua_toi'] = gmdate("Y-m-d", $unix_date);
                        }else{
                            $item['ngay_rua_toi'] = date("Y-m-d", strtotime($item['ngay_rua_toi']));
                        }
                    }else{
                        $item['ngay_rua_toi'] = NULL;
                    }
                    
                    if(!empty($item['nguoi_ban_bi_tich_rua_toi'])){
                        $sacrament = SacramentGiver::where('name', $item['nguoi_ban_bi_tich_rua_toi'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $item['nguoi_ban_bi_tich_rua_toi'] = $sacrament->id;
                        }else{
                            $data['name'] = $item['nguoi_ban_bi_tich_rua_toi'];
                            $sacrament = SacramentGiver::create($data);
                            $item['nguoi_ban_bi_tich_rua_toi'] = $sacrament->id;
                        }
                    }
                    
                    if(!empty($item['nguoi_ban_bi_tich_rua_toi'])){
                        $sponsor = Sponsor::where('name', $item['nguoi_ban_bi_tich_rua_toi'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sponsor)){
                            if(!empty($sponsor->id)){
                                $item['nguoi_do_dau_rua_toi'] = $sponsor->id;
                            }else{
                                $data['name'] = $item['nguoi_do_dau_rua_toi'];
                                $sponsor = Sponsor::create($data);
                                $item['nguoi_do_dau_rua_toi'] = $sponsor->id;
                            }
                        }else{
                            $item['nguoi_do_dau_rua_toi'] = '0';
                        }
                    }
                    
                    if(!empty($item['giao_phan_rua_toi'])){
                        $diocese = Diocese::where('name', $item['giao_phan_rua_toi'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                        $item['giao_phan_rua_toi'] = $diocese->id;
                        if(!empty($item['giao_hat_rua_toi'])){
                            $deanery = Deanery::where('name', $item['giao_hat_rua_toi'])->where('did', $item['giao_phan_rua_toi'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $item['giao_hat_rua_toi'] = $deanery->id;
                            if(!empty($item['giao_xu_rua_toi'])){
                                $parishManagement = ParishManagement::where('name', $item['giao_xu_rua_toi'])->where('diocese', $item['giao_phan_rua_toi'])->where('deanerys', $item['giao_hat_rua_toi'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                if(!empty($parishManagement->id)){
                                    $item['giao_xu_rua_toi'] = $parishManagement->id;
                                }else{
                                    $item['giao_xu_rua_toi'] = NULL;
                                }
                            }else{
                                $item['giao_xu_rua_toi'] = NULL;
                            }
                        }else{
                            $item['giao_xu_rua_toi'] = NULL;
                        }
                    }else{
                        $item['giao_xu_rua_toi'] = NULL;
                    }
                    
                    if(!empty($item['ngay_them_suc'])){
                        if(strlen($item['ngay_them_suc']) == 4){
                            $item['ngay_them_suc'] = $item['ngay_them_suc'] . '-01-01';
                        }elseif(strlen($item['ngay_them_suc']) == 5){
                            $excel_date = $item['ngay_them_suc'];
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['ngay_them_suc'] = gmdate("Y-m-d", $unix_date);
                        }else{
                            $item['ngay_them_suc'] = date("Y-m-d", strtotime($item['ngay_them_suc']));
                        }
                    }else{
                        $item['ngay_them_suc'] = NULL;
                    }
                    
                    if(!empty($item['nguoi_ban_bi_tich_them_suc'])){
                        $sacrament = SacramentGiver::where('name', $item['nguoi_ban_bi_tich_them_suc'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $item['nguoi_ban_bi_tich_them_suc'] = $sacrament->id;
                        }else{
                            $data['name'] = $item['nguoi_ban_bi_tich_them_suc'];
                            $sacrament = SacramentGiver::create($data);
                            $item['nguoi_ban_bi_tich_them_suc'] = $sacrament->id;
                        }
                    }
                    
                    if(!empty($item['nguoi_do_dau_them_suc'])){
                        $sponsor = Sponsor::where('name', $item['nguoi_do_dau_them_suc'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sponsor)){
                            if(!empty($sponsor->id)){
                                $item['nguoi_do_dau_them_suc'] = $sponsor->id;
                            }else{
                                $data['name'] = $item['nguoi_do_dau_them_suc'];
                                $sponsor = Sponsor::create($data);
                                $item['nguoi_do_dau_them_suc'] = $sponsor->id;
                            }
                        }
                    }
                    
                    if(!empty($item['giao_phan_them_suc'])){
                        $diocese = Diocese::where('name', $item['giao_phan_them_suc'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                        $item['giao_phan_them_suc'] = $diocese->id;
                        if(!empty($item['giao_hat_them_suc'])){
                            $deanery = Deanery::where('name', $item['giao_hat_them_suc'])->where('did', $item['giao_phan_them_suc'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $item['giao_hat_them_suc'] = $deanery->id;
                            if(!empty($item['giao_xu_them_suc'])){
                                $parishManagement = ParishManagement::where('name', $item['giao_xu_them_suc'])->where('diocese', $item['giao_phan_them_suc'])->where('deanerys', $item['giao_hat_them_suc'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                $item['giao_xu_them_suc'] = $parishManagement->id;
                            }else{
                                $item['giao_xu_them_suc'] = NULL;
                            }
                        }else{
                            $item['giao_xu_them_suc'] = NULL;
                        }
                    }else{
                        $item['giao_xu_them_suc'] = NULL;
                    }
                    
                    if(!empty($item['ngay_ruoc_le'])){
                        if(strlen($item['ngay_ruoc_le']) == 4){
                            $item['ngay_ruoc_le'] = $item['ngay_ruoc_le'] . '-01-01';
                        }elseif(strlen($item['ngay_ruoc_le']) == 5){
                            $excel_date = $item['ngay_ruoc_le']; //here is that value 41621 or 41631
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['ngay_ruoc_le'] = gmdate("Y-m-d", $unix_date);
                            //$item['ngay_sinh'] = str_replace("/","-", $item['ngay_sinh']);
                        }else{
                            $item['ngay_ruoc_le'] = date("Y-m-d", strtotime($item['ngay_ruoc_le']));
                        }
                    }else{
                        $item['ngay_ruoc_le'] = NULL;
                    }
                    
                    if(!empty($item['nguoi_ban_bi_tich_ruoc_le'])){
                        $sacrament = SacramentGiver::where('name', $item['nguoi_ban_bi_tich_ruoc_le'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $item['nguoi_ban_bi_tich_ruoc_le'] = $sacrament->id;
                        }else{
                            $data['name'] = $item['nguoi_ban_bi_tich_ruoc_le'];
                            $sacrament = SacramentGiver::create($data);
                            $item['nguoi_ban_bi_tich_ruoc_le'] = $sacrament->id;
                        }
                    }
                    
                    if(!empty($item['giao_phan_ruoc_le'])){
                        $diocese = Diocese::where('name', $item['giao_phan_ruoc_le'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                        $item['giao_phan_ruoc_le'] = $diocese->id;
                        if(!empty($item['giao_hat_ruoc_le'])){
                            $deanery = Deanery::where('name', $item['giao_hat_ruoc_le'])->where('did', $item['giao_phan_ruoc_le'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $item['giao_hat_ruoc_le'] = $deanery->id;
                            if(!empty($item['giao_xu_ruoc_le'])){
                                $parishManagement = ParishManagement::where('name', $item['giao_xu_ruoc_le'])->where('diocese', $item['giao_phan_ruoc_le'])->where('deanerys', $item['giao_hat_ruoc_le'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                $item['giao_xu_ruoc_le'] = $parishManagement->id;
                            }
                        }
                    }
                    
                    if(!empty($item['ngay_xuc_dau'])){
                        if(strlen($item['ngay_xuc_dau']) == 4){
                            $item['ngay_xuc_dau'] = $item['ngay_xuc_dau'] . '-01-01';
                        }elseif(strlen($item['ngay_xuc_dau']) == 5){
                            $excel_date = $item['ngay_xuc_dau']; //here is that value 41621 or 41631
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['ngay_xuc_dau'] = gmdate("Y-m-d", $unix_date);
                        }else{
                            $item['ngay_xuc_dau'] = date("Y-m-d", strtotime($item['ngay_xuc_dau']));
                        }
                    }else{
                        $item['ngay_xuc_dau'] = NULL;
                    }
                    
                    if($item['tinh_trang_xuc_dau'] == 'Nguy tử'){
                        $item['tinh_trang_xuc_dau'] = 1;
                    }else{
                        $item['tinh_trang_xuc_dau'] = 2;
                    }
                    
                    if(!empty($item['nguoi_ban_bi_tich_xuc_dau'])){
                        $sacrament = SacramentGiver::where('name', $item['nguoi_ban_bi_tich_xuc_dau'])->orderBy('created_at', 'desc')->first();
                        if(!empty($sacrament->id)){
                            $item['nguoi_ban_bi_tich_xuc_dau'] = $sacrament->id;
                        }else{
                            $data['name'] = $item['nguoi_ban_bi_tich_xuc_dau'];
                            $sacrament = SacramentGiver::create($data);
                            $item['nguoi_ban_bi_tich_xuc_dau'] = $sacrament->id;
                        }
                    }
                    
                    if(!empty($item['trang_thai_song_con_x_la_mat'])){
                        $item['trang_thai_song_con_x_la_mat'] = 1;
                    }else{
                        $item['trang_thai_song_con_x_la_mat'] = 0;
                    }
                    
                    if(!empty($item['thoi_gian_mat'])){
                        if(strlen($item['thoi_gian_mat']) == 4){
                            $item['thoi_gian_mat'] = $item['thoi_gian_mat'] . '-01-01';
                        }elseif(strlen($item['thoi_gian_mat']) == 5){
                            $excel_date = $item['thoi_gian_mat'];
                            $unix_date = ($excel_date - 25569) * 86400;
                            $excel_date = 25569 + ($unix_date / 86400);
                            $unix_date = ($excel_date - 25569) * 86400;
                            $item['thoi_gian_mat'] = gmdate("Y-m-d", $unix_date);
                        }else{
                            $item['thoi_gian_mat'] = date("Y-m-d", strtotime($item['thoi_gian_mat']));
                        }
                    }else{
                        $item['thoi_gian_mat'] = NULL;
                    }
                    
                    $giaodan = Parishioners::where('name', $item['ten'])
                    ->where('pid', $item['giao_xu'])
                    ->where('deid', $item['giao_hat'])
                    ->where('did', $item['giao_phan'])
                    ->where('status', 1)
                    ->get()
                    ->first();
                    
                    if(!empty($giaodan)){
                        $giaodan->update([
                            'magd'                  => $item['ma_gd'],
                            'last_name'             => $item['ho_ten_dem'],
                            'name'                  => $item['ten'],
                            'pid'                   => $item['giao_xu'],
                            'deid'                  => $item['giao_hat'],
                            'did'                   => $item['giao_phan'],
                            'paid'                  => $item['giao_ho'],
                            'assid'                 => $item['hoi_doan'],
                            'origin'                => $item['nguyen_quan'],
                            'ward'                  => $item['xa_phuong'],
                            'province'              => $item['tinh_tp'],
                            'residence'             => $item['tru_quan'],
                            'resi_ward'             => $item['xa_phuong_tru_quan'],
                            'resi_province'         => $item['tinh_tp_tru_quan'],
                            'professional_level'    => $item['trinh_do_chuyen_mon'],
                            'study'                 => $item['giao_duc'],
                            'new_convert'           => $item['tan_tong'],
                            'married'               => $item['co_gia_dinh'],
                            'statistical'           => $item['thong_ke'],
                            'note'                  => $item['mo_ta_them'],
                            'baptism_date'          => $item['ngay_rua_toi'],
                            'baptism_number'        => $item['so_rua_toi'],
                            'baptism_giver'         => $item['nguoi_ban_bi_tich_rua_toi'],
                            'baptism_sponsor'       => $item['nguoi_do_dau_rua_toi'],
                            'baptism_dioceses'      => $item['giao_phan_rua_toi'],
                            'baptism_deanerys'      => $item['giao_hat_rua_toi'],
                            'baptism_parish'        => $item['giao_xu_rua_toi'],
                            'more_power_date'       => $item['ngay_them_suc'],
                            'more_power_number'     => $item['so_them_suc'],
                            'more_power_giver'      => $item['nguoi_ban_bi_tich_them_suc'],
                            'more_power_sponsor'    => $item['nguoi_do_dau_them_suc'],
                            'more_power_dioceses'   => $item['giao_phan_them_suc'],
                            'more_power_deanerys'   => $item['giao_hat_them_suc'],
                            'more_power_parish'     => $item['giao_xu_them_suc'],
                            'communion_date'        => $item['ngay_ruoc_le'],
                            'communion_number'      => $item['so_ruoc_le'],
                            'communion_giver'       => $item['nguoi_ban_bi_tich_ruoc_le'],
                            'communion_dioceses'    => $item['giao_phan_ruoc_le'],
                            'communion_deanerys'    => $item['giao_hat_ruoc_le'],
                            'communion_parish'      => $item['giao_xu_ruoc_le'],
                            'anoint_date'           => $item['ngay_xuc_dau'],
                            'anoint_status'         => $item['tinh_trang_xuc_dau'],
                            'anoint_giver'          => $item['nguoi_ban_bi_tich_xuc_dau'],
                            'anoint_note'           => $item['ghi_chu'],
                            'die_status'            => $item['trang_thai_song_con_x_la_mat'],
                            'die_time'              => $item['thoi_gian_mat'],
                            'die_lottery'           => $item['so_xo_mat'],
                            'die_death'             => $item['noi_qua_doi'],
                            'die_burial'            => $item['noi_an_tang'],
                            'phone'                 => $item['so_dien_thoai'],
                            'email'                 => $item['email'],
                            'father'                => $item['ten_cha'],
                            'mother'                => $item['ten_me'],
                            'sex'                   => $item['phai'],
                            'birthday'              => $item['ngay_sinh'],
                            'cccd'                  => $item['ma_nhan_dang_cmndcccd'],
                            'holy'                  => $item['ten_thanh'],
                            'ethnic'                => $item['dan_toc'],
                            'career'                => $item['nghe_nghiep'],
                            'level'                 => $item['trinh_do'],
                            'position'              => $item['chuc_vu'],
                            'language'              => $item['ngon_ngu'],
                            'status'                => 1,
                        ]);
                    }else{
                        $giaodan = Parishioners::create([
                            'magd'                  => $item['ma_gd'],
                            'last_name'             => $item['ho_ten_dem'],
                            'name'                  => $item['ten'],
                            'pid'                   => $item['giao_xu'],
                            'deid'                  => $item['giao_hat'],
                            'did'                   => $item['giao_phan'],
                            'paid'                  => $item['giao_ho'],
                            'assid'                 => $item['hoi_doan'],
                            'origin'                => $item['nguyen_quan'],
                            'ward'                  => $item['xa_phuong'],
                            'province'              => $item['tinh_tp'],
                            'residence'             => $item['tru_quan'],
                            'resi_ward'             => $item['xa_phuong_tru_quan'],
                            'resi_province'         => $item['tinh_tp_tru_quan'],
                            'professional_level'    => $item['trinh_do_chuyen_mon'],
                            'study'                 => $item['giao_duc'],
                            'new_convert'           => $item['tan_tong'],
                            'married'               => $item['co_gia_dinh'],
                            'statistical'           => $item['thong_ke'],
                            'note'                  => $item['mo_ta_them'],
                            'baptism_date'          => $item['ngay_rua_toi'],
                            'baptism_number'        => $item['so_rua_toi'],
                            'baptism_giver'         => $item['nguoi_ban_bi_tich_rua_toi'],
                            'baptism_sponsor'       => $item['nguoi_do_dau_rua_toi'],
                            'baptism_dioceses'      => $item['giao_phan_rua_toi'],
                            'baptism_deanerys'      => $item['giao_hat_rua_toi'],
                            'baptism_parish'        => $item['giao_xu_rua_toi'],
                            'more_power_date'       => $item['ngay_them_suc'],
                            'more_power_number'     => $item['so_them_suc'],
                            'more_power_giver'      => $item['nguoi_ban_bi_tich_them_suc'],
                            'more_power_sponsor'    => $item['nguoi_do_dau_them_suc'],
                            'more_power_dioceses'   => $item['giao_phan_them_suc'],
                            'more_power_deanerys'   => $item['giao_hat_them_suc'],
                            'more_power_parish'     => $item['giao_xu_them_suc'],
                            'communion_date'        => $item['ngay_ruoc_le'],
                            'communion_number'      => $item['so_ruoc_le'],
                            'communion_giver'       => $item['nguoi_ban_bi_tich_ruoc_le'],
                            'communion_dioceses'    => $item['giao_phan_ruoc_le'],
                            'communion_deanerys'    => $item['giao_hat_ruoc_le'],
                            'communion_parish'      => $item['giao_xu_ruoc_le'],
                            'anoint_date'           => $item['ngay_xuc_dau'],
                            'anoint_status'         => $item['tinh_trang_xuc_dau'],
                            'anoint_giver'          => $item['nguoi_ban_bi_tich_xuc_dau'],
                            'anoint_note'           => $item['ghi_chu'],
                            'die_status'            => $item['trang_thai_song_con_x_la_mat'],
                            'die_time'              => $item['thoi_gian_mat'],
                            'die_lottery'           => $item['so_xo_mat'],
                            'die_death'             => $item['noi_qua_doi'],
                            'die_burial'            => $item['noi_an_tang'],
                            'phone'                 => $item['so_dien_thoai'],
                            'email'                 => $item['email'],
                            'father'                => $item['ten_cha'],
                            'mother'                => $item['ten_me'],
                            'sex'                   => $item['phai'],
                            'birthday'              => $item['ngay_sinh'],
                            'cccd'                  => $item['ma_nhan_dang_cmndcccd'],
                            'holy'                  => $item['ten_thanh'],
                            'ethnic'                => $item['dan_toc'],
                            'career'                => $item['nghe_nghiep'],
                            'level'                 => $item['trinh_do'],
                            'position'              => $item['chuc_vu'],
                            'language'              => $item['ngon_ngu'],
                            'status'                => 1,
                        ]);
                    }
                    $sluglink = $this->slugify->slugify(request()->slug ?? $item['ten']);
                    $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
                    if(!empty($slug)){
                        if($slug->sluggable_id != $giaodan->id){
                            $slugmoi = $sluglink . '-' . $giaodan->id;
                            $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $giaodan->id)->get()->first();
                            if(empty($checkslug)){
                                Slug::create([
                                    'keyword' => $slugmoi,
                                    'controller' => ParishionersController::class,
                                    'model' => Parishioners::class,
                                    'sluggable_id' => $giaodan->id
                                ]);
                            }
                        }
                    }else{
                        Slug::create([
                            'keyword' => $sluglink,
                            'controller' => ParishionersController::class,
                            'model' => Parishioners::class,
                            'sluggable_id' => $giaodan->id
                        ]);
                    }                
                }
            }else{
                return back()->withErrors('Lỗi, Bạn chọn sai giáo xứ, giáo hạt, giáo phận');
            }
        }
        
        /*
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->parish == 1){
                
                foreach ($rows as $key => $item){
                    if($key > 0 AND !empty($item[8])){
                        $row['id_csdl']                         = $item[0];
                        $row['ma_gd']                           = $item[1];
                        $row['giao_ho']                         = $item[2];
                        $row['giao_xu']                         = $item[3];
                        $row['giao_hat']                        = $item[4];
                        $row['giao_phan']                       = $item[5];
                        $row['hoi_doan']                        = $item[6];
                        $row['ten_thanh']                       = $item[7];
                        $row['ho_ten_dem']                      = $item[8];
                        $row['ten']                             = $item[9];
                        $row['phai']                            = $item[10];
                        $row['ngay_sinh']                       = $item[11];
                        $row['ma_nhan_dang_cmndcccd']           = $item[12];
                        $row['ten_cha']                         = $item[13];
                        $row['ten_me']                          = $item[14];
                        $row['so_dien_thoai']                   = $item[15];
                        $row['email']                           = $item[16];
                        $row['nguyen_quan']                     = $item[17];
                        $row['xa_phuong']                       = $item[18];
                        $row['tinh_tp']                         = $item[19];
                        $row['tru_quan']                        = $item[20];
                        $row['xa_phuong_tru_quan']              = $item[21];
                        $row['tinh_tp_tru_quan']                = $item[22];
                        $row['dan_toc']                         = $item[23];
                        $row['ngon_ngu']                        = $item[24];
                        $row['trinh_do']                        = $item[25];
                        $row['nghe_nghiep']                     = $item[26];
                        $row['chuc_vu']                         = $item[27];
                        $row['trinh_do_chuyen_mon']             = $item[28];
                        $row['giao_duc']                        = $item[29];
                        $row['tan_tong']                        = $item[30];
                        $row['co_gia_dinh']                     = $item[31];
                        $row['thong_ke']                        = $item[32];
                        $row['mo_ta_them']                      = $item[33];
                        $row['ngay_rua_toi']                    = $item[34];
                        $row['so_rua_toi']                      = $item[35];
                        $row['nguoi_ban_bi_tich_rua_toi']       = $item[36];
                        $row['nguoi_do_dau_rua_toi']            = $item[37];
                        $row['giao_xu_rua_toi']                 = $item[38];
                        $row['giao_hat_rua_toi']                = $item[39];
                        $row['giao_phan_rua_toi']               = $item[40];
                        $row['ngay_them_suc']                   = $item[41];
                        $row['so_them_suc']                     = $item[42];
                        $row['nguoi_ban_bi_tich_them_suc']      = $item[43];
                        $row['nguoi_do_dau_them_suc']           = $item[44];
                        $row['giao_xu_them_suc']                = $item[45];
                        $row['giao_hat_them_suc']               = $item[46];
                        $row['giao_phan_them_suc']              = $item[47];
                        $row['ngay_ruoc_le']                    = $item[48];
                        $row['so_ruoc_le']                      = $item[49];
                        $row['nguoi_ban_bi_tich_ruoc_le']       = $item[50];
                        $row['giao_xu_ruoc_le']                 = $item[51];
                        $row['giao_hat_ruoc_le']                = $item[52];
                        $row['giao_phan_ruoc_le']               = $item[53];
                        $row['ngay_xuc_dau']                    = $item[54];
                        $row['tinh_trang_xuc_dau']              = $item[55];
                        $row['nguoi_ban_bi_tich_xuc_dau']       = $item[56];
                        $row['ghi_chu']                         = $item[57];
                        $row['trang_thai_song_con_x_la_mat']    = $item[58];
                        $row['thoi_gian_mat']                   = $item[59];
                        $row['so_xo_mat']                       = $item[60];
                        $row['noi_qua_doi']                     = $item[61];
                        $row['noi_an_tang']                     = $item[62];
                        $row['ngay_them_du_lieu']               = $item[63];
                        $row['ngay_cap_nhat']                   = $item[64];   
                        
                        if(!empty($row['giao_phan'])){
                            $diocese = Diocese::where('name', 'like', '%' . $row['giao_phan'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            if($diocese->id == $_POST['giaophan']){
                                $row['giao_phan'] = $diocese->id;
                                
                                if(!empty($row['giao_hat'])){
                                    $deanery = Deanery::where('name', 'like', '%' . $row['giao_hat'] . '%')->where('did', $row['giao_phan'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                    if($deanery->id == $_POST['giaohat']){
                                        $row['giao_hat'] = $deanery->id;
                                        
                                        if(!empty($row['giao_xu'])){
                                            $parishManagement = ParishManagement::where('name', 'like', '%' . $row['giao_xu'] . '%')
                                                ->where('diocese', $row['giao_phan'])
                                                ->where('deanerys', $row['giao_hat'])
                                                ->where('status', 1)
                                                ->orderBy('created_at', 'desc')
                                                ->first();
                                            
                                            if(!empty($parishManagement)){
                                                if($parishManagement->id == $_POST['giaoxu']){
                                                    $row['giao_xu'] = $parishManagement->id;
                                                }else{
                                                    //return back()->withErrors('Lỗi, File excel và chọn giáo xứ không khớp');
                                                }
                                            }else{
                                                $parishManagement = ParishManagement::create([
                                                    'name'              => $row['giao_xu'],
                                                    'deanerys'          => $row['giao_hat'],
                                                    'diocese'           => $row['giao_phan'],
                                                    'status'            => 1,
                                                ]);
                                                $row['giao_xu'] = $parishManagement->id;
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
                            if(is_numeric($row['giao_xu'])){
                                $parishs = ParishGroup::where('name', 'like', '%' . $row['giao_ho'] . '%')->where('parish_id', $row['giao_xu'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                            }else{
                                $parishs = ParishGroup::where('name', 'like', '%' . $row['giao_ho'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            }
                            if(!empty($parishs)){
                                if($parishs->id){
                                    $row['giao_ho'] = $parishs->id;
                                }else{
                                    $row['giao_ho'] = '0';
                                }
                            }else{
                                $row['giao_ho'] = '0';
                            }
                        }
                        
                        if(!empty($row['hoi_doan'])){
                            $association = Association::where('name', 'like', '%' . $row['hoi_doan'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            if(!empty($association->id)){
                                $row['hoi_doan'] = $association->id;
                            }else{
                                $data['pid'] = 0;
                                $data['deid'] = 0;
                                $data['did'] = 0;
                                $data['name'] = $row['hoi_doan'];
                                $data['status'] = 1;
                                $hoidoan = Association::create($data);
                                $row['hoi_doan'] = $hoidoan->id;
                            }
                        }
                        
                        if(!empty($row['ten_thanh'])){
                            $holy = Holymanagement::where('name', 'like', '%' . $row['ten_thanh'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($holy->id)){
                                $row['ten_thanh'] = $holy->id;
                            }else{
                                $data['name'] = $row['ten_thanh'];
                                $holym = Holymanagement::create($data);
                                $row['ten_thanh'] = $holym->id;
                            }
                        }
                        
                        if($row['phai'] == 'Nữ'){
                            $row['phai'] = 0;
                        }else{
                            $row['phai'] = 1;
                        }
                        
                        if(!empty($row['ngay_sinh'])){
                            if(strlen($row['ngay_sinh']) == 4){
                                $row['ngay_sinh'] = $row['ngay_sinh'] . '-01-01';
                            }elseif(strlen($row['ngay_sinh']) == 5){
                                $excel_date = $row['ngay_sinh']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_sinh'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_sinh'] = date("Y-m-d", strtotime($row['ngay_sinh']));
                            }
                        }else{
                            $row['ngay_sinh'] = NULL;
                        }
                        
                        if(!empty($row['tinh_tp'])){
                            $row['tinh_tp'] = array_search($row['tinh_tp'], $tinh_thanhpho);
                        }
                        
                        $row['xa_phuong'] = array_search('Xã ' . $row['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                        if(empty($row['xa_phuong'])){
                            $row['xa_phuong'] = array_search('Phường ' . $row['xa_phuong'], array_column($xa_phuong_thitran, 'name'));
                        }
        
                        if(!empty($row['tinh_tp_tru_quan'])){
                            $row['tinh_tp_tru_quan'] = array_search($row['tinh_tp_tru_quan'], $tinh_thanhpho);
                        }
                        
                        $row['xa_phuong_tru_quan'] = array_search('Xã ' . $row['xa_phuong_tru_quan'], array_column($xa_phuong_thitran, 'name'));
                        if(empty($row['xa_phuong_tru_quan'])){
                            $row['xa_phuong_tru_quan'] = array_search('Phường ' . $row['xa_phuong_tru_quan'], array_column($xa_phuong_thitran, 'name'));
                        }
                        
                        if(!empty($row['dan_toc'])){
                            $ethic = Ethnicmanagement::where('name', 'like', '%' . $row['dan_toc'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($ethic->id)){
                                $row['dan_toc'] = $ethic->id;
                            }else{
                                $data['name'] = $row['dan_toc'];
                                $dantoc = Ethnicmanagement::create($data);
                                $row['dan_toc'] = $dantoc->id;
                            }
                        }
                        
                        if(!empty($row['ngon_ngu'])){
                            $ngonngu = Languagemanagement::where('name', 'like', '%' . $row['ngon_ngu'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($ngonngu->id)){
                                $row['ngon_ngu'] = $ngonngu->id;
                            }else{
                                $data['name'] = $row['ngon_ngu'];
                                $ngonngu = Languagemanagement::create($data);
                                $row['dan_toc'] = $ngonngu->id;
                            }
                        }
                        
                        if(!empty($row['ngon_ngu'])){
                            $level = Levelmanagement::where('name', 'like', '%' . $row['ngon_ngu'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($level->id)){
                                $row['ngon_ngu'] = $level->id;
                            }else{
                                $data['name'] = $row['ngon_ngu'];
                                $level = Levelmanagement::create($data);
                                $row['ngon_ngu'] = $level->id;
                            }
                        }
                        
                        if(!empty($row['nghe_nghiep'])){
                            $career = Careermanagement::where('name', 'like', '%' . $row['nghe_nghiep'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($career->id)){
                                $row['nghe_nghiep'] = $career->id;
                            }else{
                                $data['name'] = $row['nghe_nghiep'];
                                $career = Careermanagement::create($data);
                                $row['nghe_nghiep'] = $career->id;
                            }
                        }
                        
                        if(!empty($row['chuc_vu'])){
                            $position = Positionmanagement::where('name', 'like', '%' . $row['chuc_vu'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($position->id)){
                                $row['chuc_vu'] = $position->id;
                            }else{
                                $data['name'] = $row['chuc_vu'];
                                $position = Positionmanagement::create($data);
                                $row['chuc_vu'] = $position->id;
                            }
                        }
                        
                        if($row['giao_duc'] == 'Đang học'){
                            $row['giao_duc'] = 1;
                        }elseif($row['giao_duc'] == 'Đã học xong'){
                            $row['giao_duc'] = 2;
                        }else{  
                            $row['giao_duc'] = 3;
                        }
                        
                        if(!empty($row['tan_tong'])){
                            $row['tan_tong'] = 1;
                        }
                        
                        if(!empty($row['co_gia_dinh'])){
                            $row['co_gia_dinh'] = 1;
                        }
                        
                        if(!empty($row['thong_ke'])){
                            $row['thong_ke'] = 1;
                        }
                        
                        if(!empty($row['ngay_rua_toi'])){
                            if(strlen($row['ngay_rua_toi']) == 4){
                                $row['ngay_rua_toi'] = $row['ngay_rua_toi'] . '-01-01';
                            }elseif(strlen($row['ngay_rua_toi']) == 5){
                                $excel_date = $row['ngay_rua_toi']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_rua_toi'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_rua_toi'] = date("Y-m-d", strtotime($row['ngay_rua_toi']));
                            }
                        }else{
                            $row['ngay_rua_toi'] = NULL;
                        }
                        
                        if(!empty($row['nguoi_ban_bi_tich_rua_toi'])){                    
                            $sacrament = SacramentGiver::where('name', 'like', '%' . $row['nguoi_ban_bi_tich_rua_toi'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($sacrament->id)){
                                $row['nguoi_ban_bi_tich_rua_toi'] = $sacrament->id;
                            }else{
                                $data['name'] = $row['nguoi_ban_bi_tich_rua_toi'];
                                $sacrament = SacramentGiver::create($data);
                                $row['nguoi_ban_bi_tich_rua_toi'] = $sacrament->id;
                            }
                        }
                        
                        if(!empty($row['nguoi_ban_bi_tich_rua_toi'])){
                            $sponsor = Sponsor::where('name', $row['nguoi_ban_bi_tich_rua_toi'])->orderBy('created_at', 'desc')->first();
                            if(!empty($sponsor)){
                                if(!empty($sponsor->id)){
                                    $row['nguoi_do_dau_rua_toi'] = $sponsor->id;
                                }else{
                                    $data['name'] = $row['nguoi_do_dau_rua_toi'];
                                    $sponsor = Sponsor::create($data);
                                    $row['nguoi_do_dau_rua_toi'] = $sponsor->id;
                                }
                            }else{
                                $row['nguoi_do_dau_rua_toi'] = '0';
                            }
                        }
                        
                        if(!empty($row['giao_phan_rua_toi'])){
                            $diocese = Diocese::where('name', 'like', '%' . $row['giao_phan_rua_toi'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $row['giao_phan_rua_toi'] = $diocese->id;                        
                            if(!empty($row['giao_hat_rua_toi'])){
                                $deanery = Deanery::where('name', 'like', '%' . $row['giao_hat_rua_toi'] . '%')->where('did', $row['giao_phan_rua_toi'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                $row['giao_hat_rua_toi'] = $deanery->id;
                                if(!empty($row['giao_xu_rua_toi'])){
                                    $parishManagement = ParishManagement::where('name', 'like', '%' . $row['giao_xu_rua_toi'] . '%')->where('diocese', $row['giao_phan_rua_toi'])->where('deanerys', $row['giao_hat_rua_toi'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                    if(!empty($parishManagement->id)){
                                        $row['giao_xu_rua_toi'] = $parishManagement->id;
                                    }else{
                                        $row['giao_xu_rua_toi'] = NULL;
                                    }
                                }else{
                                    $row['giao_xu_rua_toi'] = NULL;
                                }
                            }else{
                                $row['giao_xu_rua_toi'] = NULL;
                            }
                        }else{
                            $row['giao_xu_rua_toi'] = NULL;
                        }
                        
                        if(!empty($row['ngay_them_suc'])){
                            if(strlen($row['ngay_them_suc']) == 4){
                                $row['ngay_them_suc'] = $row['ngay_them_suc'] . '-01-01';
                            }elseif(strlen($row['ngay_them_suc']) == 5){
                                $excel_date = $row['ngay_them_suc']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_them_suc'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_them_suc'] = date("Y-m-d", strtotime($row['ngay_them_suc']));
                            }
                        }else{
                            $row['ngay_them_suc'] = NULL;
                        }
                        
                        if(!empty($row['nguoi_ban_bi_tich_them_suc'])){
                            $sacrament = SacramentGiver::where('name', 'like', '%' . $row['nguoi_ban_bi_tich_them_suc'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($sacrament->id)){
                                $row['nguoi_ban_bi_tich_them_suc'] = $sacrament->id;
                            }else{
                                $data['name'] = $row['nguoi_ban_bi_tich_them_suc'];
                                $sacrament = SacramentGiver::create($data);
                                $row['nguoi_ban_bi_tich_them_suc'] = $sacrament->id;
                            }
                        }
                        
                        if(!empty($row['nguoi_do_dau_them_suc'])){
                            $sponsor = Sponsor::where('name', $row['nguoi_do_dau_them_suc'])->orderBy('created_at', 'desc')->first();
                            if(!empty($sponsor)){
                                if(!empty($sponsor->id)){
                                    $row['nguoi_do_dau_them_suc'] = $sponsor->id;
                                }else{
                                    $data['name'] = $row['nguoi_do_dau_them_suc'];
                                    $sponsor = Sponsor::create($data);
                                    $row['nguoi_do_dau_them_suc'] = $sponsor->id;
                                }
                            }
                        }
                        
                        if(!empty($row['giao_phan_them_suc'])){
                            $diocese = Diocese::where('name', 'like', '%' . $row['giao_phan_them_suc'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $row['giao_phan_them_suc'] = $diocese->id;
                            if(!empty($row['giao_hat_them_suc'])){
                                $deanery = Deanery::where('name', 'like', '%' . $row['giao_hat_them_suc'] . '%')->where('did', $row['giao_phan_them_suc'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                $row['giao_hat_them_suc'] = $deanery->id;
                                if(!empty($row['giao_xu_them_suc'])){
                                    $parishManagement = ParishManagement::where('name', 'like', '%' . $row['giao_xu_them_suc'] . '%')->where('diocese', $row['giao_phan_them_suc'])->where('deanerys', $row['giao_hat_them_suc'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                    $row['giao_xu_them_suc'] = $parishManagement->id;
                                }else{
                                    $row['giao_xu_them_suc'] = NULL;
                                }
                            }else{
                                $row['giao_xu_them_suc'] = NULL;
                            }
                        }else{
                            $row['giao_xu_them_suc'] = NULL;
                        }
                        
                        if(!empty($row['ngay_ruoc_le'])){
                            if(strlen($row['ngay_ruoc_le']) == 4){
                                $row['ngay_ruoc_le'] = $row['ngay_ruoc_le'] . '-01-01';
                            }elseif(strlen($row['ngay_ruoc_le']) == 5){
                                $excel_date = $row['ngay_ruoc_le']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_ruoc_le'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_ruoc_le'] = date("Y-m-d", strtotime($row['ngay_ruoc_le']));
                            }
                        }else{
                            $row['ngay_ruoc_le'] = NULL;
                        }
                        
                        if(!empty($row['nguoi_ban_bi_tich_ruoc_le'])){
                            $sacrament = SacramentGiver::where('name', 'like', '%' . $row['nguoi_ban_bi_tich_ruoc_le'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($sacrament->id)){
                                $row['nguoi_ban_bi_tich_ruoc_le'] = $sacrament->id;
                            }else{
                                $data['name'] = $row['nguoi_ban_bi_tich_ruoc_le'];
                                $sacrament = SacramentGiver::create($data);
                                $row['nguoi_ban_bi_tich_ruoc_le'] = $sacrament->id;
                            }
                        }
                        
                        if(!empty($row['giao_phan_ruoc_le'])){
                            $diocese = Diocese::where('name', 'like', '%' . $row['giao_phan_ruoc_le'] . '%')->where('status', 1)->orderBy('created_at', 'desc')->first();
                            $row['giao_phan_ruoc_le'] = $diocese->id;
                            if(!empty($row['giao_hat_ruoc_le'])){
                                $deanery = Deanery::where('name', 'like', '%' . $row['giao_hat_ruoc_le'] . '%')->where('did', $row['giao_phan_ruoc_le'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                $row['giao_hat_ruoc_le'] = $deanery->id;
                                if(!empty($row['giao_xu_ruoc_le'])){
                                    $parishManagement = ParishManagement::where('name', 'like', '%' . $row['giao_xu_ruoc_le'] . '%')->where('diocese', $row['giao_phan_ruoc_le'])->where('deanerys', $row['giao_hat_ruoc_le'])->where('status', 1)->orderBy('created_at', 'desc')->first();
                                    $row['giao_xu_ruoc_le'] = $parishManagement->id;
                                }
                            }
                        }
                        
                        if(!empty($row['ngay_xuc_dau'])){
                            if(strlen($row['ngay_xuc_dau']) == 4){
                                $row['ngay_xuc_dau'] = $row['ngay_xuc_dau'] . '-01-01';
                            }elseif(strlen($row['ngay_xuc_dau']) == 5){
                                $excel_date = $row['ngay_xuc_dau']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['ngay_xuc_dau'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['ngay_xuc_dau'] = date("Y-m-d", strtotime($row['ngay_xuc_dau']));
                            }
                        }else{
                            $row['ngay_xuc_dau'] = NULL;
                        }
                        
                        if($row['tinh_trang_xuc_dau'] == 'Nguy tử'){
                            $row['tinh_trang_xuc_dau'] = 1;
                        }else{
                            $row['tinh_trang_xuc_dau'] = 2;
                        }
                        
                        if(!empty($row['nguoi_ban_bi_tich_xuc_dau'])){
                            $sacrament = SacramentGiver::where('name', 'like', '%' . $row['nguoi_ban_bi_tich_xuc_dau'] . '%')->orderBy('created_at', 'desc')->first();
                            if(!empty($sacrament->id)){
                                $row['nguoi_ban_bi_tich_xuc_dau'] = $sacrament->id;
                            }else{
                                $data['name'] = $row['nguoi_ban_bi_tich_xuc_dau'];
                                $sacrament = SacramentGiver::create($data);
                                $row['nguoi_ban_bi_tich_xuc_dau'] = $sacrament->id;
                            }
                        }
                        
                        if(!empty($row['trang_thai_song_con_x_la_mat'])){
                            $row['trang_thai_song_con_x_la_mat'] = 1;
                        }else{
                            $row['trang_thai_song_con_x_la_mat'] = 0;
                        }
                        
                        if(!empty($row['thoi_gian_mat'])){
                            if(strlen($row['thoi_gian_mat']) == 4){
                                $row['thoi_gian_mat'] = $row['thoi_gian_mat'] . '-01-01';
                            }elseif(strlen($row['thoi_gian_mat']) == 5){
                                $excel_date = $row['thoi_gian_mat']; //here is that value 41621 or 41631
                                $unix_date = ($excel_date - 25569) * 86400;
                                $excel_date = 25569 + ($unix_date / 86400);
                                $unix_date = ($excel_date - 25569) * 86400;
                                $row['thoi_gian_mat'] = gmdate("Y-m-d", $unix_date);
                                //$row['ngay_sinh'] = str_replace("/","-", $row['ngay_sinh']);
                            }else{
                                $row['thoi_gian_mat'] = date("Y-m-d", strtotime($row['thoi_gian_mat']));
                            }
                        }else{
                            $row['thoi_gian_mat'] = NULL;
                        }
                        
                        $giaodan = Parishioners::where('name', $row['ten'])
                            ->where('pid', $row['giao_xu'])
                            ->where('deid', $row['giao_hat'])
                            ->where('did', $row['giao_phan'])
                            ->where('status', 1)
                            ->get()
                            ->first();
                        if(!empty($giaodan)){
                            $giaodan->update([
                                'magd'                  => $row['ma_gd'],
                                'last_name'             => $row['ho_ten_dem'],
                                'name'                  => $row['ten'],
                                'pid'                   => $row['giao_xu'],
                                'deid'                  => $row['giao_hat'],
                                'did'                   => $row['giao_phan'],
                                'paid'                  => $row['giao_ho'],
                                'assid'                 => $row['hoi_doan'],
                                'origin'                => $row['nguyen_quan'],
                                'ward'                  => $row['xa_phuong'],
                                'province'              => $row['tinh_tp'],
                                'residence'             => $row['tru_quan'],
                                'resi_ward'             => $row['xa_phuong_tru_quan'],
                                'resi_province'         => $row['tinh_tp_tru_quan'],
                                'professional_level'    => $row['trinh_do_chuyen_mon'],
                                'study'                 => $row['giao_duc'],
                                'new_convert'           => $row['tan_tong'],
                                'married'               => $row['co_gia_dinh'],
                                'statistical'           => $row['thong_ke'],
                                'note'                  => $row['mo_ta_them'],
                                'baptism_date'          => $row['ngay_rua_toi'],
                                'baptism_number'        => $row['so_rua_toi'],
                                'baptism_giver'         => $row['nguoi_ban_bi_tich_rua_toi'],
                                'baptism_sponsor'       => $row['nguoi_do_dau_rua_toi'],
                                'baptism_dioceses'      => $row['giao_phan_rua_toi'],
                                'baptism_deanerys'      => $row['giao_hat_rua_toi'],
                                'baptism_parish'        => $row['giao_xu_rua_toi'],
                                'more_power_date'       => $row['ngay_them_suc'],
                                'more_power_number'     => $row['so_them_suc'],
                                'more_power_giver'      => $row['nguoi_ban_bi_tich_them_suc'],
                                'more_power_sponsor'    => $row['nguoi_do_dau_them_suc'],
                                'more_power_dioceses'   => $row['giao_phan_them_suc'],
                                'more_power_deanerys'   => $row['giao_hat_them_suc'],
                                'more_power_parish'     => $row['giao_xu_them_suc'],
                                'communion_date'        => $row['ngay_ruoc_le'],
                                'communion_number'      => $row['so_ruoc_le'],
                                'communion_giver'       => $row['nguoi_ban_bi_tich_ruoc_le'],
                                'communion_dioceses'    => $row['giao_phan_ruoc_le'],
                                'communion_deanerys'    => $row['giao_hat_ruoc_le'],
                                'communion_parish'      => $row['giao_xu_ruoc_le'],
                                'anoint_date'           => $row['ngay_xuc_dau'],
                                'anoint_status'         => $row['tinh_trang_xuc_dau'],
                                'anoint_giver'          => $row['nguoi_ban_bi_tich_xuc_dau'],
                                'anoint_note'           => $row['ghi_chu'],
                                'die_status'            => $row['trang_thai_song_con_x_la_mat'],
                                'die_time'              => $row['thoi_gian_mat'],
                                'die_lottery'           => $row['so_xo_mat'],
                                'die_death'             => $row['noi_qua_doi'],
                                'die_burial'            => $row['noi_an_tang'],
                                'phone'                 => $row['so_dien_thoai'],
                                'email'                 => $row['email'],
                                'father'                => $row['ten_cha'],
                                'mother'                => $row['ten_me'],
                                'sex'                   => $row['phai'],
                                'birthday'              => $row['ngay_sinh'],
                                'cccd'                  => $row['ma_nhan_dang_cmndcccd'],
                                'holy'                  => $row['ten_thanh'],
                                'ethnic'                => $row['dan_toc'],
                                'career'                => $row['nghe_nghiep'],
                                'level'                 => $row['trinh_do'],
                                'position'              => $row['chuc_vu'],
                                'language'              => $row['ngon_ngu'],
                                'status'                => 1,
                            ]);      
                        }else{
                            $giaodan = Parishioners::create([
                                'magd'                  => $row['ma_gd'],
                                'last_name'             => $row['ho_ten_dem'],
                                'name'                  => $row['ten'],
                                'pid'                   => $row['giao_xu'],
                                'deid'                  => $row['giao_hat'],
                                'did'                   => $row['giao_phan'],
                                'paid'                  => $row['giao_ho'],
                                'assid'                 => $row['hoi_doan'],
                                'origin'                => $row['nguyen_quan'],
                                'ward'                  => $row['xa_phuong'],
                                'province'              => $row['tinh_tp'],
                                'residence'             => $row['tru_quan'],
                                'resi_ward'             => $row['xa_phuong_tru_quan'],
                                'resi_province'         => $row['tinh_tp_tru_quan'],
                                'professional_level'    => $row['trinh_do_chuyen_mon'],
                                'study'                 => $row['giao_duc'],
                                'new_convert'           => $row['tan_tong'],
                                'married'               => $row['co_gia_dinh'],
                                'statistical'           => $row['thong_ke'],
                                'note'                  => $row['mo_ta_them'],
                                'baptism_date'          => $row['ngay_rua_toi'],
                                'baptism_number'        => $row['so_rua_toi'],
                                'baptism_giver'         => $row['nguoi_ban_bi_tich_rua_toi'],
                                'baptism_sponsor'       => $row['nguoi_do_dau_rua_toi'],
                                'baptism_dioceses'      => $row['giao_phan_rua_toi'],
                                'baptism_deanerys'      => $row['giao_hat_rua_toi'],
                                'baptism_parish'        => $row['giao_xu_rua_toi'],
                                'more_power_date'       => $row['ngay_them_suc'],
                                'more_power_number'     => $row['so_them_suc'],
                                'more_power_giver'      => $row['nguoi_ban_bi_tich_them_suc'],
                                'more_power_sponsor'    => $row['nguoi_do_dau_them_suc'],
                                'more_power_dioceses'   => $row['giao_phan_them_suc'],
                                'more_power_deanerys'   => $row['giao_hat_them_suc'],
                                'more_power_parish'     => $row['giao_xu_them_suc'],
                                'communion_date'        => $row['ngay_ruoc_le'],
                                'communion_number'      => $row['so_ruoc_le'],
                                'communion_giver'       => $row['nguoi_ban_bi_tich_ruoc_le'],
                                'communion_dioceses'    => $row['giao_phan_ruoc_le'],
                                'communion_deanerys'    => $row['giao_hat_ruoc_le'],
                                'communion_parish'      => $row['giao_xu_ruoc_le'],
                                'anoint_date'           => $row['ngay_xuc_dau'],
                                'anoint_status'         => $row['tinh_trang_xuc_dau'],
                                'anoint_giver'          => $row['nguoi_ban_bi_tich_xuc_dau'],
                                'anoint_note'           => $row['ghi_chu'],
                                'die_status'            => $row['trang_thai_song_con_x_la_mat'],
                                'die_time'              => $row['thoi_gian_mat'],
                                'die_lottery'           => $row['so_xo_mat'],
                                'die_death'             => $row['noi_qua_doi'],
                                'die_burial'            => $row['noi_an_tang'],
                                'phone'                 => $row['so_dien_thoai'],
                                'email'                 => $row['email'],
                                'father'                => $row['ten_cha'],
                                'mother'                => $row['ten_me'],
                                'sex'                   => $row['phai'],
                                'birthday'              => $row['ngay_sinh'],
                                'cccd'                  => $row['ma_nhan_dang_cmndcccd'],
                                'holy'                  => $row['ten_thanh'],
                                'ethnic'                => $row['dan_toc'],
                                'career'                => $row['nghe_nghiep'],
                                'level'                 => $row['trinh_do'],
                                'position'              => $row['chuc_vu'],
                                'language'              => $row['ngon_ngu'],
                                'status'                => 1,
                            ]);
                        }
                        $sluglink = $this->slugify->slugify(request()->slug ?? $row['ten']);
                        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
                        if(!empty($slug)){
                            if($slug->sluggable_id != $giaodan->id){
                                $slugmoi = $sluglink . '-' . $giaodan->id;
                                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $giaodan->id)->get()->first();
                                if(empty($checkslug)){
                                    Slug::create([
                                        'keyword' => $slugmoi,
                                        'controller' => ParishionersController::class,
                                        'model' => Parishioners::class,
                                        'sluggable_id' => $giaodan->id
                                    ]);
                                }
                            }
                        }else{
                            Slug::create([
                                'keyword' => $sluglink,
                                'controller' => ParishionersController::class,
                                'model' => Parishioners::class,
                                'sluggable_id' => $giaodan->id
                            ]);
                        }
                    }
                }
            }else{
                return back()->withErrors('Lỗi, Bạn chọn sai xứ');
            }
        }*/
    }
}
