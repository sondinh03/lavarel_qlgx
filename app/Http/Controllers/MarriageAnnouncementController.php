<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\MarriageAnnouncement;
use App\Models\Priest;
use App\Models\Marriage;
use App\Models\MarriageAnnouncementParishioners;
use App\Models\Parishioners;
use App\Models\Association;
use App\Models\Parish;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Holymanagement;
use Illuminate\Support\Facades\Auth;

class MarriageAnnouncementController extends Controller
{
    public function __invoke(Request $request)
    {
        //
    }
    
    protected array $data = [];
    
    protected mixed $url_prefix = null;
    
    protected mixed $cache_time = 0;
    
    private $assets;
    
    public function __construct()
    {
        $this->url_prefix = config('settings.url_prefix');
        $this->cache_time = config('settings.cache_time');
    }
    
    public function show($id): View
    {
        \Assets::add('fontawesome');
        
        if (Auth::check()) {        
        
            /*
            $raohonphoi = Cache::remember("marriage_announcements_id_$id", $this->cache_time, function () use ($id) {
                return MarriageAnnouncement::findOrFail($id);
            });
            */
            
            $raohonphoi = MarriageAnnouncement::where('id', $id)->where('status', 1)->get()->first();
            
            if(!empty($raohonphoi->priest)){
                $priest = Priest::where('id', $raohonphoi->priest)->first();
                $raohonphoi->priest = $priest->name;
            }
            if(!empty($raohonphoi->announcements_one)){
                $raohonphoi->announcements_one = date("d-m-Y", strtotime($raohonphoi->announcements_one));
            }
            if(!empty($raohonphoi->announcements_two)){
                $raohonphoi->announcements_two = date("d-m-Y", strtotime($raohonphoi->announcements_two));
            }
            if(!empty($raohonphoi->announcements_three)){
                $raohonphoi->announcements_three = date("d-m-Y", strtotime($raohonphoi->announcements_three));
            }
            if(!empty($raohonphoi->pid)){
                $giaoxu = ParishManagement::where('id', $raohonphoi->pid)->where('status', 1)->get()->first();
                $raohonphoi->pid = $giaoxu->name . ', ';
            }
            if(!empty($raohonphoi->deid)){
                $giaohat = Deanery::where('id', $raohonphoi->deid)->where('status', 1)->get()->first();
                $raohonphoi->deid = $giaohat->name . ', ';
            }
            if(!empty($raohonphoi->did)){
                $giaophan = Diocese::where('id', $raohonphoi->did)->where('status', 1)->get()->first();
                $raohonphoi->did = $giaophan->name;
            }
            
            if(!empty($raohonphoi->id)){
                $nu = MarriageAnnouncementParishioners::where('idannouncement', $raohonphoi->id)->where('sex', 0)->where('status', 1)->orderBy('id', 'ASC')->first();
                $nu['raohonphoi_nu'] = url(slug($raohonphoi).$this->url_prefix . '/raohonphoinu=' . $id);
                $nu['kqraohonphoi_nu'] = url(slug($raohonphoi).$this->url_prefix . '/kqraohonphoinu=' . $id);
                if(!empty($nu->idgiaodan)){
                    
                    $girl = Parishioners::where('id', $nu->idgiaodan)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $nu['slug'] = url(slug($girl).$this->url_prefix);
                    if(empty($nu['slug'])){
                        $nu['slug'] = '';
                    }
                    if(!empty($girl->holy)){
                        $holy = Holymanagement::where('id', $girl['holy'])->first();
                        if(!empty($holy->name)){
                            $nu['holy'] = $holy->name;
                        }else{
                            $nu['holy'] = '';
                        }
                    }
                    if(!empty($girl->last_name)){
                        $nu['last_name'] = $girl->last_name;
                    }
                    if(!empty($girl->name)){
                        $nu['name'] = $girl->name;
                    }
                    if($nu->parishsold != ''){
                        $parish = Parish::where('id', $nu['parishsold'])->first();
                        $nu['parishsold'] = $parish->name . ', ';
                    }else{
                        $nu['parishsold'] = '';
                    }                
                    if($nu->parishmanagementsold != ''){
                        $parish_management = ParishManagement::where('id', $nu['parishmanagementsold'])->first();
                        $nu['parishmanagementsold'] = $parish_management->name . ', ';
                    }else{
                        $nu['parishmanagementsold'] = '';
                    }                
                    if($nu->deanerysold != ''){
                        $deanery = Deanery::where('id', $nu['deanerysold'])->first();
                        $nu['deanerysold'] = $deanery->name . ', ';
                    }else{
                        $nu['deanerysold'] = '';
                    }                
                    if($nu->diocesesold != ''){
                        $diocese = Diocese::where('id', $nu['diocesesold'])->first();
                        $nu['diocesesold'] = $diocese->name;
                    }else{
                        $nu['diocesesold'] = '';
                    }
                    
                    if($nu->parishs != ''){
                        $parish = Parish::where('id', $nu['parishs'])->first();
                        $nu['parishs'] = $parish->name . ', ';
                    }else{
                        $nu['parishs'] = '';
                    }                
                    if($nu->parishmanagements != ''){
                        $parish_management = ParishManagement::where('id', $nu['parishmanagements'])->first();
                        $nu['parishmanagements'] = $parish_management->name . ', ';
                    }else{
                        $nu['parishmanagements'] = '';
                    }                
                    if($nu->deanerys != ''){
                        $deanery = Deanery::where('id', $nu['deanerys'])->first();
                        $nu['deanerys'] = $deanery->name . ', ';
                    }else{
                        $nu['deanerys'] = '';
                    }                
                    if($nu->dioceses != ''){
                        $diocese = Diocese::where('id', $nu['dioceses'])->first();
                        $nu['dioceses'] = $diocese->name;
                    }else{
                        $nu['dioceses'] = '';
                    }
                    
                    if($nu->parishsbefore != ''){
                        $parish = Parish::where('id', $nu['parishsbefore'])->first();
                        $nu['parishsbefore'] = $parish->name . ', ';
                    }else{
                        $nu['parishsbefore'] = '';
                    }
                    if($nu->parishmanagementsbefore != ''){
                        $parish_management = ParishManagement::where('id', $nu['parishmanagementsbefore'])->first();
                        $nu['parishmanagementsbefore'] = $parish_management->name . ', ';
                    }else{
                        $nu['parishmanagementsbefore'] = '';
                    }
                    if($nu->deanerysbefore != ''){
                        $deanery = Deanery::where('id', $nu['deanerysbefore'])->first();
                        $nu['deanerysbefore'] = $deanery->name . ', ';
                    }else{
                        $nu['deanerysbefore'] = '';
                    }
                    if($nu->diocesesbefore != ''){
                        $diocese = Diocese::where('id', $nu['diocesesbefore'])->first();
                        $nu['diocesesbefore'] = $diocese->name;
                    }else{
                        $nu['diocesesbefore'] = '';
                    }
                }
                
                $nam = MarriageAnnouncementParishioners::where('idannouncement', $raohonphoi->id)->where('sex', 1)->where('status', 1)->orderBy('id', 'ASC')->first();
                $nam['raohonphoi_nam'] = url(slug($raohonphoi).$this->url_prefix . '/raohonphoinam=' . $id);
                $nam['kqraohonphoi_nam'] = url(slug($raohonphoi).$this->url_prefix . '/kqraohonphoinam=' . $id);
                
                if(!empty($nam->idgiaodan)){
                    $boy = Parishioners::where('id', $nam->idgiaodan)->where('status', 1)->orderBy('id', 'ASC')->first();
                    $nam['slug'] = url(slug($boy).$this->url_prefix);
                    if(empty($nam['slug'])){
                        $nam['slug'] = '';
                    }
                    if(!empty($boy->holy)){
                        $holy = Holymanagement::where('id', $boy['holy'])->first();                    
                        if(!empty($holy->name)){
                            $nam['holy'] = $holy->name;
                        }else{
                            $nam['holy'] = '';
                        }
                    }
                    
                    if(!empty($boy->last_name)){
                        $nam['last_name'] = $boy->last_name;
                    }
                    if(!empty($boy->name)){
                        $nam['name'] = $boy->name;
                    }else{
                        $nam['name'] = '';
                    }
                    if($nam->parishsold != ''){
                        $parish = Parish::where('id', $nam['parishsold'])->first();
                        $nam['parishsold'] = $parish->name . ', ';
                    }else{
                        $nu['parishsold'] = '';
                    }
                    if($nam->parishmanagementsold != ''){
                        $parish_management = ParishManagement::where('id', $nam['parishmanagementsold'])->first();
                        $nam['parishmanagementsold'] = $parish_management->name . ', ';
                    }else{
                        $nam['parishmanagementsold'] = '';
                    }
                    if($nam->deanerysold != ''){
                        $deanery = Deanery::where('id', $nam['deanerysold'])->first();
                        $nam['deanerysold'] = $deanery->name . ', ';
                    }else{
                        $nam['deanerysold'] = '';
                    }
                    if($nam->diocesesold != ''){
                        $diocese = Diocese::where('id', $nam['diocesesold'])->first();
                        $nam['diocesesold'] = $diocese->name;
                    }else{
                        $nam['diocesesold'] = '';
                    }
                    
                    if($nam->parishs != ''){
                        $parish = Parish::where('id', $nam['parishs'])->first();
                        $nam['parishs'] = $parish->name . ', ';
                    }else{
                        $nam['parishs'] = '';
                    }
                    if($nam->parishmanagements != ''){
                        $parish_management = ParishManagement::where('id', $nam['parishmanagements'])->first();
                        $nam['parishmanagements'] = $parish_management->name . ', ';
                    }else{
                        $nam['parishmanagements'] = '';
                    }
                    if($nam->deanerys != ''){
                        $deanery = Deanery::where('id', $nam['deanerys'])->first();
                        $nam['deanerys'] = $deanery->name . ', ';
                    }else{
                        $nam['deanerys'] = '';
                    }
                    if($nam->dioceses != ''){
                        $diocese = Diocese::where('id', $nam['dioceses'])->first();
                        $nam['dioceses'] = $diocese->name;
                    }else{
                        $nam['dioceses'] = '';
                    }
                    
                    if($nam->parishsbefore != ''){
                        $parish = Parish::where('id', $nam['parishsbefore'])->first();
                        $nam['parishsbefore'] = $parish->name . ', ';
                    }else{
                        $nam['parishsbefore'] = '';
                    }
                    if($nam->parishmanagementsbefore != ''){
                        $parish_management = ParishManagement::where('id', $nam['parishmanagementsbefore'])->first();
                        $nam['parishmanagementsbefore'] = $parish_management->name . ', ';
                    }else{
                        $nam['parishmanagementsbefore'] = '';
                    }
                    if($nam->deanerysbefore != ''){
                        $deanery = Deanery::where('id', $nam['deanerysbefore'])->first();
                        $nam['deanerysbefore'] = $deanery->name . ', ';
                    }else{
                        $nam['deanerysbefore'] = '';
                    }
                    if($nam->diocesesbefore != ''){
                        $diocese = Diocese::where('id', $nam['diocesesbefore'])->first();
                        $nam['diocesesbefore'] = $diocese->name;
                    }else{
                        $nam['diocesesbefore'] = '';
                    }
                }            
            }
            
            $this->data['nam'] = $nam;
            
            $this->data['nu'] = $nu;
            
            $this->data['raohonphoi'] = $raohonphoi;
            
            return view()->first([
                'frontend.raohonphoi',
                'frontend.layout.main',
            ], $this->data);
        }else{
            return view('home');
        }
    }
}
