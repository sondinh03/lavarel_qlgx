<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KetQua;
use App\Models\DiemThi;
use Validator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\Slug;
use Carbon\Carbon;
use App\Models\Holymanagement;
use App\Models\Student;
use App\Models\Lop;
use App\Models\DiHoc;
use App\Models\DiLe;

class KQController extends Controller
{
    //
    
    /**
     * Display a listing of the submitInfoQr.
     *
     * @return \Illuminate\Http\Response
     */
    public function submitInfoKQ(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lop' => 'required',
        ]);
        
        if ($validator->passes()) {
            $id = $request->id;
            $lop = $request->lop;
            
            $data = Student::where('id', $id)->where('lop', $lop)->where('status', 1)->orderBy('id', 'ASC')->get()->first();
            
            if($data->holy){
                $holy = Holymanagement::where('id', $data->holy)->first();
                if(!empty($holy->name)){
                    $data->holy = $holy->name;
                }else{
                    $data->holy = '';
                }
            }else{
                $data->holy = '';
            }
            
            $data->diem = DiemThi::where('ihv', $id)->where('lop', $lop)->where('status', 1)->orderBy('id', 'ASC')->get()->first();

            if(empty($data->diem)){
                $diem = '{
                    "ihv": '.$id.',
                    "lop": '.$lop.',
                    "tuan1": "",
                    "k1": "",
                    "kinh1": "",
                    "kq1": "",
                    "tuan2": "",
                    "k2"        : "",
                    "kinh2"     : "",
                    "kq2"       : "",
                    "canam"     : "",
                    "seploai"   : "",
                    "nghile"    : "",
                    "bohoc"     : "",
                    "hanhkiem"  : "",
                    "ghichu"    : ""
                }';
                $data->diem = json_decode($diem);
            }
            return response()->json($data);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    
    public function submitInfoKQdihoc(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lop' => 'required',
        ]);
        if ($validator->passes()) {
            $id = $request->id;
            $idlop = $request->lop;
            $data = array();
            $lop = Lop::where('id', $idlop)->where('status', 1)->first();
            
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
            
            $array_tuanhk1 = '';
            $dihochk1 = array();
            foreach($weeks as $key => $tuan){
                $array_tuanhk1 .= '<th class="text-nowrap">Tuần ' . $key . '</th>';
                $dihoc1 = DiHoc::where('idh', $id)->where('lophoc', $idlop)->where('hocky', 1)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->get();
                foreach($dihoc1 as $_key => $row){
                    if($row->dihoc == 1){
                        $dihochk1[] = '<td class="text-nowrap">Đi học</td>';
                    }elseif($row->dihoc == 2){
                        $dihochk1[] = '<td class="text-nowrap">Vắng có phép</td>';
                    }elseif($row->dihoc == 0){
                        $dihochk1[] = '<td class="text-nowrap">Vắng</td>';
                    }else{
                        $dihochk1[] = '<td class="text-nowrap">-</td>';
                    }
                }
            }
            
            $student = Student::where('id', $id)->where('status', 1)->orderBy('name', 'asc')->get()->first();
            
            if(!empty($student)){
                $holy = Holymanagement::where('id', $student->holy)->first();
                if(!empty($holy->name)){
                    $student->holy = $holy->name;
                }else{
                    $student->holy = '';
                }
                $student->birthday = date("d-m-Y", strtotime($student->birthday));
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
            
            $array_tuanhk2 = '';
            $dihochk2 = array();
            foreach($weeks as $key => $tuan){
                $array_tuanhk2 .= '<th class="text-nowrap">Tuần ' . $key . '</th>';
                $dihoc2 = DiHoc::where('idh', $id)->where('lophoc', $idlop)->where('hocky', 2)->where('tuan', $key)->where('status', 1)->orderby('tuan', 'asc')->get();
                foreach($dihoc2 as $_key => $row){
                    if($row->dihoc == 1){
                        $dihochk2[] = '<td class="text-nowrap">Đi học</td>';
                    }elseif($row->dihoc == 2){
                        $dihochk2[] = '<td class="text-nowrap">Vắng có phép</td>';
                    }elseif($row->dihoc == 0){
                        $dihochk2[] = '<td class="text-nowrap">Vắng</td>';
                    }else{
                        $dihochk2[] = '<td class="text-nowrap">-</td>';
                    }
                }
            }
            
            $data = array(
                'last_name' => $student->last_name,
                'holy'      => $student->holy,
                'name'      => $student->name,
                'mahv'      => $student->mahv,
                'birthday'  => $student->birthday,
                'tuanhk1'   => $array_tuanhk1,
                'dihochk1'  => $dihochk1,
                'tuanhk2'   => $array_tuanhk2,
                'dihochk2'  => $dihochk2,
            );
            
            return response()->json($data);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
    
    public function submitInfoKQdile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'lop' => 'required',
        ]);
        if ($validator->passes()) {
            $id = $request->id;
            $idlop = $request->lop;
            $data = array();
            $lop = Lop::where('id', $idlop)->where('status', 1)->first();
            
            $begin = Carbon::parse($lop->start_date_one);
            $end = Carbon::parse($lop->end_date_one);
            
            $interval = \DateInterval::createFromDateString('1 day');
            
            $period = New \DatePeriod($begin, $interval, $end);
            
            $dilehk1 = array();
            $tuanhk1 = '';
            foreach($period as $key => $dt){
                $date_one = $dt->format("l");
                $date_time = $dt->format("d/m/Y");
                if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                    $thang = $dt->format('n');
                    $ngay = $dt->format('j');
                    $dile = DiLe::where('idh', $id)->where('lophoc', $idlop)->where('hocky', 1)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                    if(!empty($dile->dile)){
                        if($dile->dile == 1){
                            $dilehk1[] = '<td class="text-nowrap">Đi lễ</td>';
                        }else{
                            $dilehk1[] = '<td class="text-nowrap">Vắng</td>';
                        }
                    }else{
                        $dilehk1[] = '<td class="text-nowrap">--</td>';
                    }
                    
                    if($date_one == 'Thursday'){
                        $date_one_vi = 'Thứ 5';
                    }else{
                        $date_one_vi = 'Chủ nhật';
                    }
                    $tuanhk1 .= '<th class="text-center">' . $date_one_vi . '<br>' . $date_time . '</th>';
                }
            }
            
            $begin_hk2 = Carbon::parse($lop->start_date_two);
            $end_hk2 = Carbon::parse($lop->end_date_two);
            
            $interval = \DateInterval::createFromDateString('1 day');
            
            $period_hk2 = New \DatePeriod($begin_hk2, $interval, $end_hk2);
            
            $dilehk2 = array();
            $tuanhk2 = '';
            foreach($period_hk2 as $key => $dt){
                $date_one = $dt->format("l");
                $date_time = $dt->format("d/m/Y");
                if($date_one == 'Thursday' OR $date_one == 'Sunday'){
                    $thang = $dt->format('n');
                    $ngay = $dt->format('j');
                    $dile = DiLe::where('idh', $id)->where('lophoc', $idlop)->where('hocky', 2)->where('thang', $thang)->where('ngay', $ngay)->where('status', 1)->orderby('ngay', 'asc')->get()->first();
                    if(!empty($dile->dile)){
                        if($dile->dile == 1){
                            $dilehk2[] = '<td class="text-nowrap">Đi lễ</td>';
                        }else{
                            $dilehk2[] = '<td class="text-nowrap">Vắng</td>';
                        }
                    }else{
                        $dilehk2[] = '<td class="text-nowrap">--</td>';
                    }
                    if($date_one == 'Thursday'){
                        $date_one_vi = 'Thứ 5';
                    }else{
                        $date_one_vi = 'Chủ nhật';
                    }
                    $tuanhk2 .= '<th class="text-center">' . $date_one_vi . '<br>' . $date_time . '</th>';
                }
            }
            
            $student = Student::where('id', $id)->where('status', 1)->orderBy('name', 'asc')->get()->first();
            
            if(!empty($student)){
                $holy = Holymanagement::where('id', $student->holy)->first();
                if(!empty($holy->name)){
                    $student->holy = $holy->name;
                }else{
                    $student->holy = '';
                }
                $student->birthday = date("d-m-Y", strtotime($student->birthday));
            }
            
            $data = array(
                'last_name'     => $student->last_name,
                'holy'          => $student->holy,
                'name'          => $student->name,
                'mahv'          => $student->mahv,
                'birthday'      => $student->birthday,
                'tuanhk1'       => $tuanhk1,
                'dilehk1'       => $dilehk1,
                'tuanhk2'       => $tuanhk2,
                'dilehk2'       => $dilehk2,
            );
            
            return response()->json($data);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
