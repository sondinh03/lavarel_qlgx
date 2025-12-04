<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LopImport;
//use App\Imports\DynamicSheetsImport;


class LopImportController extends Controller
{    
    public function show()
    {
        return view('page.import');
    }
    
    public function store(Request $request)
    {
        $file = $request->file('file')->store('import');
        
        if(empty($file)){
            return back()->withStatus('Bạn cần tiến hành chọn file trước khi import');
        }
        
        $file = $request->file('file');
        $path = $file->getRealPath();
        
        Excel::import(new LopImport($path), $file);
        
        return back()->with('success', 'Import thành công!');
    }
}
