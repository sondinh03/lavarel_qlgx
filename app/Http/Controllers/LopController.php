<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\Lop;
use App\Models\Block;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Holymanagement;
use App\Models\Slug;
use Validator;
use App\Models\DiemThi;
use Carbon\Carbon;
use Faker\Core\DateTime;
use BaconQrCode\Encoder\QrCode;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use App\Models\Parish;
use App\Models\NamHoc;

class LopController extends Controller
{
    public function __invoke(Request $request)
    {
        //
    }

    protected array $data = [];

    protected mixed $url_prefix = null;

    protected mixed $cache_time = 0;

    protected mixed $per_page = 10;

    private $assets;

    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }

    public function show($id)
    {
        return view('frontend.lop', ['lopId' => $id]);
    }

    public function GetTinhThanhQuan($id)
    {
        @include(resource_path() . '/cities/tinh_thanhpho.php');

        $tinhthanh_child = '';
        foreach ($tinh_thanhpho as $key => $tinhthanh) {
            if ($key == $id) {
                $tinhthanh_child = $tinhthanh;
            }
        }

        return $tinhthanh_child;
    }

    public function GetXaTruQuan($id)
    {
        @include(resource_path() . '/cities/xa_phuong_thitran.php');

        $xaphuong_child = '';
        foreach ($xa_phuong_thitran as $key => $xaphuong) {

            if ($xaphuong['xaid'] == $id) {
                $xaphuong_child = $xaphuong;
            }
        }

        return $xaphuong_child;
    }

    /**
     * Display a listing of the submitInfo.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lop' => 'required',
        ]);

        if ($validator->passes()) {
            $id = $request->id;
            $lop = $request->lop;

            $data = DB::table('student')
                ->where('id', $id)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->first();

            if ($data->holy) {
                $holy = Holymanagement::where('id', $data->holy)->first();
                if (!empty($holy->name)) {
                    $data->holy = $holy->name;
                } else {
                    $data->holy = '';
                }
            } else {
                $data->holy = '';
            }

            $data->birthday = date("d-m-Y", strtotime($data->birthday));

            $data->diem = DB::table('diemthi')
                ->where('ihv', $id)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->first();

            if (empty($data->diem)) {
                $diem = '{
                    "ihv": ' . $id . ',
                    "lop": ' . $lop . ',
                    "tuan1": " ",
                    "k1": " ",
                    "kinh1": " ",
                    "kq1": " ",
                    "tuan2": " ",
                    "k2"        : " ",
                    "kinh2"     : " ",
                    "kq2"       : " ",
                    "canam"     : " ",
                    "seploai"   : " ",
                    "nghile"    : " ",
                    "bohoc"     : " ",
                    "hanhkiem"  : " ",
                    "ghichu"    : " "
                }';
                $data->diem = json_decode($diem);
            }

            $datelop = DB::table('lop')
                ->where('id', $data->lop)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->first();

            $weeks_one = $this->week_tow($datelop->start_date_one, $datelop->end_date_one);

            $weeks_two = $this->week_tow($datelop->start_date_two, $datelop->end_date_two);

            $weeks = $weeks_one + $weeks_two;

            $dihoc = DB::table('dihoc')
                ->where('idh', $id)
                ->where('lophoc', $data->lop)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->count();

            $bohoc = $weeks - $dihoc;

            $data->bohoc = $bohoc;
            $data->hocdu = $weeks;

            $day_one = $this->days_tow($datelop->start_date_one, $datelop->end_date_one);

            $day_two = $this->days_tow($datelop->start_date_two, $datelop->end_date_two);

            $day = $day_one + $day_two;

            $dile = DB::table('dile')
                ->where('idh', $id)
                ->where('lophoc', $data->lop)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->count();

            $nghile = $day - $dile;

            $data->nghile = $nghile;
            $data->ledu = $day;

            $nghile_tile = ($nghile + $bohoc) / ($weeks + $day) * 100;

            if ($nghile_tile <= 5) {
                $tile = 'Tốt';
            } elseif ($nghile_tile > 5 and $nghile_tile <= 10) {
                $tile = 'Khá';
            } elseif ($nghile_tile > 10 and $nghile_tile <= 15) {
                $tile = 'Trung bình';
            } elseif ($nghile_tile > 15) {
                $tile = 'Yếu';
            } else {
                $tile = '';
            }

            $data->hanhkiem = $tile;

            return response()->json($data);
        }
        return response()->json(['error' => $validator->errors()->all()]);
    }

    /**
     * Display a listing of the submitInfo.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfoUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ihv' => 'required',
            'lop' => 'required',
            'tuan1' => 'nullable',
            'k1' => 'nullable',
            'kinh1' => 'nullable',
            'kq1' => 'nullable',
            'tuan2' => 'nullable',
            'k2' => 'nullable',
            'kinh2' => 'nullable',
            'kq2' => 'nullable',
            'ghichu' => 'nullable',
            'canam' => 'nullable',
            'seploai' => 'nullable',
            'nghile' => 'nullable',
            'bohoc' => 'nullable',
            'hanhkiem' => 'nullable',
        ]);

        if ($validator->passes()) {
            $ihv        = $request->ihv;
            $lop        = $request->lop;
            $tuan1      = $request->tuan1;
            $k1         = $request->k1;
            $kinh1      = $request->kinh1;
            $kq1        = $request->kq1;
            $tuan2      = $request->tuan2;
            $k2         = $request->k2;
            $kinh2      = $request->kinh2;
            $kq2        = $request->kq2;
            $ghichu     = $request->ghichu;
            //$canam      = $request->canam;
            //$seploai    = $request->seploai;
            $nghile     = $request->nghile;
            $bohoc      = $request->bohoc;
            $hanhkiem   = $request->hanhkiem;

            $data = DB::table('diemthi')
                ->where('ihv', $ihv)
                ->where('lop', $lop)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->first();

            $weight = DB::table('diemthi')
                ->where('lop', $lop)
                ->where('status', 1)
                ->orderBy('id', 'ASC')
                ->get()->max('weight');

            if (!empty($kq1) and !empty($kq2)) {
                $canam      = ($tuan1 + $tuan2 + $k1 + $k2 + $kinh1 + $kinh2) / 6;
                if ($canam >= 9.5) {
                    $seploai = 'Xuất sắc';
                } else if ($canam >= 8 and $canam < 9.5) {
                    $seploai = 'Giỏi';
                } else if ($canam >= 6.5 and $canam < 8) {
                    $seploai = 'Khá';
                } else if ($canam >= 5 and $canam < 6.5) {
                    $seploai = 'Trung bình';
                } else if ($canam >= 3.5 and $canam < 5) {
                    $seploai = 'Yếu';
                } else if ($canam >= 0 and $canam < 3.5) {
                    $seploai = 'Kém';
                } else {
                    $seploai    = ' ';
                }
            } else {
                $canam      = '0';
                $seploai    = ' ';
                $nghile     = '0';
                $bohoc      = '0';
                $hanhkiem   = '0';
            }

            if (empty($weight)) {
                $weight = 1;
            } else {
                $weight = intval($weight) + 1;
            }
            if (!empty($data->id)) {
                //update
                DiemThi::where('id', $data->id)->where('ihv', $data->ihv)->where('lop', $data->lop)->where('status', $data->status)->update([
                    'tuan1' => $tuan1,
                    'k1'        => $k1,
                    'kinh1'     => $kinh1,
                    'kq1'       => $kq1,
                    'tuan2'     => $tuan2,
                    'k2'        => $k2,
                    'kinh2'     => $kinh2,
                    'kq2'       => $kq2,
                    'canam'     => $canam,
                    'seploai'   => $seploai,
                    'nghile'    => $nghile,
                    'bohoc'     => $bohoc,
                    'hanhkiem'  => $hanhkiem,
                    'ghichu'    => $ghichu,
                ]);
            } else {
                if (!empty($tuan1) and empty($k1) and empty($kinh1) and empty($kq1)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan1'     => $tuan1,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } elseif (!empty($tuan1) and !empty($k1) and empty($kinh1) and empty($kq1)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan1'     => $tuan1,
                        'k1'        => $k1,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } elseif (!empty($tuan1) and !empty($k1) and !empty($kinh1) and !empty($kq1)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan1'     => $tuan1,
                        'k1'        => $k1,
                        'kinh1'     => $kinh1,
                        'kq1'       => $kq1,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } elseif (!empty($tuan2) and empty($k2) and empty($kinh2) and empty($kq2)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan2'     => $tuan2,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } elseif (!empty($tuan2) and !empty($k2) and empty($kinh2) and empty($kq2)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan2'     => $tuan2,
                        'k2'        => $k2,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } elseif (!empty($tuan1) and !empty($k1) and !empty($kinh1) and !empty($kq1) and !empty($tuan2) and !empty($k2) and !empty($kinh2) and !empty($kq2) and !empty($canam) and !empty($seploai)) {
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan1'     => $tuan1,
                        'k1'        => $k1,
                        'kinh1'     => $kinh1,
                        'kq1'       => $kq1,
                        'tuan2'     => $tuan2,
                        'k2'        => $k2,
                        'kinh2'     => $kinh2,
                        'kq2'       => $kq2,
                        'canam'     => $canam,
                        'seploai'   => $seploai,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                } else {
                }
                /*
                if(!empty($tuan1) AND !empty($k1) AND !empty($kinh1) AND !empty($kq1) AND !empty($tuan2) AND !empty($k2) AND !empty($kinh2) AND !empty($kq2) AND !empty($canam) AND !empty($seploai)){
                    //insert
                    $diemthi = DiemThi::create([
                        'ihv'       => $ihv,
                        'lop'       => $lop,
                        'tuan1'     => $tuan1,
                        'k1'        => $k1,
                        'kinh1'     => $kinh1,
                        'kq1'       => $kq1,
                        'tuan2'     => $tuan2,
                        'k2'        => $k2,
                        'kinh2'     => $kinh2,
                        'kq2'       => $kq2,
                        'canam'     => $canam,
                        'seploai'   => $seploai,
                        'nghile'    => $nghile,
                        'bohoc'     => $bohoc,
                        'hanhkiem'  => $hanhkiem,
                        'ghichu'    => $ghichu,
                        'weight'    => $weight,
                        'status'    => 1,
                    ]);
                }else{
                    return response()->json(['success'=>'Lỗi chưa nhập đầy đủ dữ liệu']);
                }*/
            }

            return response()->json(['success' => 'Cảm ơn bạn đã cập nhật điểm cho thiếu nhi.']);
        }

        return response()->json(['error' => $validator->errors()->all()]);
    }

    public function week_tow($strtDate, $endDate)
    {
        $startDateWeekCnt = round(floor(date('d', strtotime($strtDate)) / 7));

        $endDateWeekCnt = round(ceil(date('d', strtotime($endDate)) / 7));

        $datediff = strtotime(date('Y-m', strtotime($endDate)) . "-01") - strtotime(date('Y-m', strtotime($strtDate)) . "-01");
        $totalnoOfWeek = round(floor($datediff / (60 * 60 * 24)) / 7) + $endDateWeekCnt - $startDateWeekCnt;

        return $totalnoOfWeek;
    }

    public function days_tow($start_day, $end_day)
    {
        $begin = Carbon::parse($start_day);
        $end = Carbon::parse($end_day);

        $interval = \DateInterval::createFromDateString('1 day');

        $period = new \DatePeriod($begin, $interval, $end);

        $array_day = array();
        foreach ($period as $date_one) {
            $day = date('l', strtotime($date_one->format("Y-m-d")));
            if ($day == 'Thursday' or $day == 'Sunday') {
                $array_day[$date_one->format("Y-m-d")] = $day;
            }
        }

        return count($array_day);
    }

    /**
     * Display a listing of the submitInfoQr.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfoQr(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lop' => 'required',
        ]);

        if ($validator->passes()) {
            $id = $request->id;
            $lop = $request->lop;

            $data = Student::where('id', $id)->where('lop', $lop)->where('status', 1)->orderBy('id', 'ASC')->get()->first();

            if ($data->holy) {
                $holy = Holymanagement::where('id', $data->holy)->first();
                if (!empty($holy->name)) {
                    $data->holy = $holy->name;
                } else {
                    $data->holy = '';
                }
            } else {
                $data->holy = '';
            }

            $data->birthday = date("d-m-Y", strtotime($data->birthday));

            $data->slug = url(slug($data) . $this->url_prefix);

            $qr = \SimpleSoftwareIO\QrCode\Facades\QrCode::backgroundColor(255, 255, 255)->color(0, 0, 0)->size(300)->generate($data->slug);

            $qrCode = html_entity_decode($qr);

            //$qrCode = response($qr)->header('Content-Type', 'image/svg+xml');

            $data->qr = $qrCode;

            return response()->json($data);
            //return response()->json(['success'=>'Cảm ơn bạn đã cập nhật điểm cho thiếu nhi.']);
        }
        return response()->json(['error' => $validator->errors()->all()]);
    }
}
