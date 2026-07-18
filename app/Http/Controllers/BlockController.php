<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;
use App\Models\CatechismClass;
use App\Models\Block;
use App\Models\ParishGroup;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\NamHoc;

class BlockController extends Controller
{
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
    
    public function show($id): View
    {
        \Assets::add('fontawesome');
        
        $this->data['block'] = $block = Cache::remember("block_$id", $this->cache_time, function () use ($id) {
            return Block::where('id', $id)->orderBy('created_at', 'desc')->first();
        });
        
        if(!empty($block)){
            if(!empty($block->paid)){
                $parish = ParishGroup::where('id', $block['paid'])->first();
                if(!empty($parish)){
                    $block['paid'] = $parish->name;
                }else{
                    $block['paid'] = '';
                }
            }else{
                $block['paid'] = '';
            }
            
            if($block->pid != ''){
                $parish_management = ParishManagement::where('id', $block['pid'])->first();
                $block['pid'] = ', ' . $parish_management->name;
            }else{
                $block['pid'] = '';
            }
            
            if($block->deid != ''){
                $deanery = Deanery::where('id', $block['deid'])->first();
                $block['deid'] = ', ' . $deanery->name;
            }else{
                $block['deid'] = '';
            }
            
            if($block->did != ''){
                $diocese = Diocese::where('id', $block['did'])->first();
                $block['did'] = ', ' . $diocese->name;
            }else{
                $block['did'] = '';
            }
        }
        
        $lop = CatechismClass::where('block', $id)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
        
        if(!empty($lop)){
            foreach($lop as $item){
                $item['start_date_one'] = date("d-m-Y", strtotime($item->start_date_one));
                $item['end_date_one'] = date("d-m-Y", strtotime($item->end_date_one));
                $item['start_date_two'] = date("d-m-Y", strtotime($item->start_date_two));
                $item['end_date_two'] = date("d-m-Y", strtotime($item->end_date_two));
                if($item->block != ''){
                    $blocks = Block::where('id', $item->block)->where('status', 1)->first();
                    $item['block'] = $blocks->name;
                }
                if(!empty($item['teacher'])){
                    $array_teacher = array();
                    foreach($item['teacher'] as $teacher){
                        $teachers = DB::table('teacher')
                            ->where('id', $teacher)
                            ->orderBy('id', 'ASC')
                            ->first();
                        if(!empty($teachers)){
                            //$item['teach'][$teacher] = $teachers->name;
                            $array_teacher[] = $teachers->name;
                        }
                    }
                    $item['teach'] = $array_teacher;
                }
                $item['slug'] = url(slug($item).$this->url_prefix);
                
                if(!empty($item['schoolyear'])){
                    $schoolyear = NamHoc::where('id', $item['schoolyear'])->where('status', 1)->get()->first();
                    $item['schoolyear'] = $schoolyear->name;
                }
                
                if($item->paid != ''){
                    $parish = ParishGroup::where('id', $item['paid'])->first();
                    $item['paid'] = $parish->name;
                }else{
                    $item['paid'] = '';
                }
                
                if($item->pid != ''){
                    $parish_management = ParishManagement::where('id', $item['pid'])->first();
                    $item['pid'] = ', ' . $parish_management->name;
                }else{
                    $item['pid'] = '';
                }
                
                if($item->deid != ''){
                    $deanery = Deanery::where('id', $item['deid'])->first();
                    $item['deid'] = ', ' . $deanery->name;
                }else{
                    $item['deid'] = '';
                }
                
                if($item->did != ''){
                    $diocese = Diocese::where('id', $item['did'])->first();
                    $item['did'] = ', ' . $diocese->name;
                }else{
                    $item['did'] = '';
                }
            }
        }
        
        $this->data['lop'] = $lop;
        
        $this->data['pagination'] = $lop->links();
        
        return view()->first([
            'frontend.block',
            'frontend.layout.main',
        ], $this->data);
    }
}
