<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Family;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\Parish;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use Illuminate\Support\Facades\DB;
use App\Models\Marriage;
use App\Models\Priest;
use App\Models\Child;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SoGiaDinhExport;

class SoGiaDinhCongGiaoExportController extends Controller
{
    public function store()
    {        
        return Excel::download(new SoGiaDinhExport(), 'SoGiaDinh.xlsx');
    }
    public function GetTinhThanhQuan($id){
        @include(resource_path().'/cities/tinh_thanhpho.php');
        
        $tinhthanh_child = '';
        foreach($tinh_thanhpho as $key => $tinhthanh){
            if($key == $id){
                $tinhthanh_child = $tinhthanh;
            }
        }
        
        return $tinhthanh_child;
    }
    public function GetXaTruQuan($id){
        @include(resource_path().'/cities/xa_phuong_thitran.php');
        
        $xaphuong_child = '';
        foreach($xa_phuong_thitran as $key => $xaphuong){
            if($xaphuong['xaid'] == $id){
                $xaphuong_child = $xaphuong;
            }
        }
        
        return $xaphuong_child;
    }
}
