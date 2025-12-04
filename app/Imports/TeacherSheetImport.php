<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\HasReferencesToOtherSheets;
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
use App\Models\Teacher;
use App\Models\Lop;
use Illuminate\Support\Facades\Auth;
use App\Models\Decen;

class TeacherSheetImport implements ToModel, ToArray, HasReferencesToOtherSheets, WithHeadingRow
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    public function headingRow(): int
    {
        return 4;
    }
    
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $rows)
    {
        //print_r($rows);die;
        /*
        return new User([
            //
        ]);
        */
    }
    
    public function array(array $rows)
    {
        if(!empty($_POST)){
            $userId = Auth::id();
            $decen = Decen::where('use', $userId)->where('pid', $_POST['giaoxu'])->where('status', '1')->get()->first();
            if(!empty($decen) AND $decen->student == 1){
                foreach($rows as $row){
                    if(!empty($row['nam_sinh'])){
                        $dateParts = explode('/', $row['nam_sinh']);
                        $day = $dateParts[0];   // 26
                        $month = $dateParts[1]; // 02
                        $year = $dateParts[2];  // 1997
                        
                        $row['nam_sinh'] = $year . '-' . $month . '-' . $day;
                        //$row['nam_sinh'] = date('Y-m-d H:i:s', strtotime($row['nam_sinh']));
                    }
                    
                    $id_giaoho = '0';
                    if(!empty($row['khu_ho'])){
                        $giaoho = Parish::where('name', $row['khu_ho'])->first();
                        if(empty($giaoho)){
                            $giaoho = Parish::create([
                                'did'           => $_POST['giaophan'],
                                'deid'          => $_POST['giaohat'],
                                'pid'           => $_POST['giaoxu'],
                                'name'          => $row['khu_ho'],
                                'status'        => 1,
                            ]);
                        }
                        if(!empty($giaoho->id)){
                            $id_giaoho = $giaoho->id;
                        }
                    }
                    
                    if(!empty($row['nam_vao'])){
                        //$row['nam_vao'] = date('Y-m-d', strtotime($row['nam_vao']));
                        $row['nam_vao'] = $row['nam_vao'] . '-01-01 00:00:00';
                    }
                    
                    $teacher = Teacher::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('paid', $id_giaoho)->where('name', $row['ho_va_ten'])->first();
                    
                    if(!empty($teacher)){
                        $tea = Teacher::where('did', $_POST['giaophan'])
                        ->where('deid', $_POST['giaohat'])
                        ->where('pid', $_POST['giaoxu'])
                        ->where('paid', $id_giaoho)
                        ->where('name', $row['ho_va_ten'])
                        ->update([
                            'name'      => $row['ho_va_ten'],
                            'birthday'  => $row['nam_sinh'],
                            'year'      => $row['nam_vao'],
                            'phone'     => $row['so_dien_thoai'],
                            'note'      => $row['ghi_chu'],
                            'namhoc'    => $_POST['schoolyear'],
                            'status'    => 1,
                        ]);
                        
                        $lop = Lop::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('name', $row['lop'])->where('status', 1)->first();
                        if(!empty($lop->id)){
                            $array_teacher = array();
                            if(!empty($lop->teacher)){
                                $array_teacher = $lop->teacher;
                                $array_teacher[] = $teacher->id;
                            }else{
                                $array_teacher[] = $teacher->id;
                            }
                            
                            Lop::where('id', $lop->id)
                            ->update([
                                'teacher'   => array_unique($array_teacher),
                            ]);
                        }
                    }else{
                        $tea = Teacher::create([
                            'did'       => $_POST['giaophan'],
                            'deid'      => $_POST['giaohat'],
                            'pid'       => $_POST['giaoxu'],
                            'paid'      => $id_giaoho,
                            'name'      => $row['ho_va_ten'],
                            'birthday'  => $row['nam_sinh'],
                            'year'      => $row['nam_vao'],
                            'phone'     => $row['so_dien_thoai'],
                            'note'      => $row['ghi_chu'],
                            'namhoc'    => $_POST['schoolyear'],
                            'status'    => 1
                        ]);
                        if($tea->id){
                            if(!empty($row['lop'])){
                                $lop = Lop::where('did', $_POST['giaophan'])->where('deid', $_POST['giaohat'])->where('pid', $_POST['giaoxu'])->where('name', $row['lop'])->where('status', 1)->first();
                                if(!empty($lop->id)){
                                    $array_teacher = array();
                                    if(!empty($lop->teacher)){
                                        $array_teacher = $lop->teacher;
                                        $array_teacher[] = $tea->id;
                                    }else{
                                        $array_teacher[] = $tea->id;
                                    }
                                    $array_teacher[] = $tea->id;
                                    Lop::where('id', $lop->id)
                                    ->update([
                                        'teacher'   => array_unique($array_teacher),
                                    ]);
                                }
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
