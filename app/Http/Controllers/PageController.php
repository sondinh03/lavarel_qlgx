<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\Parishioners;
use App\Models\Holymanagement;
use App\Models\Parish;
use App\Models\ParishManagement;
use App\Models\Deanery;
use App\Models\Diocese;
use App\Models\Family;
use App\Models\Association;
use App\Models\MarriageAnnouncement;
use App\Models\SacramentGiver;
use App\Models\Female;
use App\Models\Male;
use App\Models\MarriageParishioner;
use App\Exports\UsersExport;
use Illuminate\Support\Facades\Auth;
use App\Models\Block;
use App\Models\Student;
use App\Models\Decen;
use App\Models\Teacher;
use App\Models\CatechismClass;
use App\Models\MarriageAnnouncementParishioners;
use Illuminate\Database\Query\Builder;
use App\Models\SetAdmin;
use App\Models\NamHoc;


class PageController extends Controller
{
    protected array $data = [];

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
        $user = backpack_user();
        //if (Auth::check()) {   
        if(!empty($user)){
            $page = Page::findOrFail($id);
            
            $this->data['name'] = $page->name;
            $this->data['content'] = $page->content;
            $this->data['version'] = $page->id;
            
            \Assets::add('fontawesome');
            
            // SEO
            $this->data['meta_title'] = Str::title(optional($page->extras)->meta_title ?? $page->name);
            $this->data['meta_description'] = optional($page->extras)->meta_description;
            $this->data['meta_keywords'] = optional($page->extras)->meta_keywords;
            $this->data['no_index'] = optional($page->extras)->no_index == 1;
            
            $dioceses = Diocese::where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
            
            $this->data['giaophan'] = $dioceses;
            
            $this->data['giaohat'] = $this->data['giaoxu'] = $this->data['giaoho'] = array();
            
            $deanerys = array();
            if(!empty($_GET['giaophan'])){
                $deanerys = Deanery::where('status', 1)->where('did', $_GET['giaophan'])->orderBy('created_at', 'ASC')->get()->toArray();
                $this->data['giaohat'] = $deanerys;
                
                if(!empty($_GET['giaohat'])){
                    $parish_mana = ParishManagement::where('diocese', $_GET['giaophan'])->where('deanerys', $_GET['giaohat'])->where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                    $this->data['giaoxu'] = $parish_mana;
                    
                    if(!empty($_GET['giaoxu'])){
                        $parish = Parish::where('did', $_GET['giaophan'])->where('deid', $_GET['giaohat'])->where('pid', $_GET['giaoxu'])->where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                        $this->data['giaoho'] = $parish;
                    }
                }
            }
            
            if($page->template === 'default') {                    
            }
            
            if($page->template === 'giaodan') {
                $parishioners = array();
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1){
                    $this->data['form'] = 0;
                    $this->data['giaoho'] = Parish::where('pid', $decen->pid)->where('status', 1)->get()->toArray();
                    if(!empty($_GET['giaoho'])){
                        $giaoho = $_GET['giaoho'];
                    }else{
                        $giaoho = '';
                    }
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $parishioners = Parishioners::where('name', 'like', '%' . $keyword . '%')
                        ->where('did', $decen->did)
                        ->where('deid', $decen->deid)
                        ->where('pid', $decen->pid)
                        ->where('paid', $giaoho)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        if(!empty($giaoho)){
                            $parishioners = Parishioners::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('paid', $giaoho)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $parishioners = Parishioners::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                        
                    }
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;
                    $giaoho = array();
                    if(!empty($_GET['giaophan']) AND !empty($_GET['giaohat']) AND !empty($_GET['giaoxu'])){
                        $giaoho = Parish::where('did', $_GET['giaophan'])->where('deid', $_GET['giaohat'])->where('pid', $_GET['giaoxu'])->where('status', 1)->get()->toArray();
                    }
                    $this->data['giaoho'] = $giaoho;
                    if(!empty($_GET['giaoho'])){
                        $giaoho = $_GET['giaoho'];
                    }else{
                        $giaoho = '';
                    }
                    if(!empty($_GET['giaophan'])){
                        $giaophan = $_GET['giaophan'];
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = $_GET['giaohat'];
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = $_GET['giaoxu'];
                    }else{
                        $giaoxu = '';
                    }
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $parishioners = Parishioners::where('name', 'like', '%' . $keyword . '%')
                        ->where('did', $giaophan)
                        ->where('deid', $giaohat)
                        ->where('pid', $giaoxu)
                        ->orWhere('paid', $giaoho)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        if(!empty($giaoho) AND is_int($giaoho)){
                            $parishioners = Parishioners::where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('paid', $giaoho)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('deid', $giaohat)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $parishioners = Parishioners::where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }else{
                                $parishioners = Parishioners::where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }
                    }
                }
                    
                if(!empty($parishioners)){
                    $stt_start = $parishioners->firstItem();
                    foreach ($parishioners as $item){
                        $stt_start++;
                        $item['stt'] = $stt_start-1;
                        //$item['birthday'] = date("d-m-Y", strtotime($item['birthday']));                        
                        if(!empty($item->birthday) AND strlen($item->birthday) == 10){
                            $item['birthday'] = date('d-m-Y', strtotime($item->birthday));
                        }else{
                            $item['birthday'] = '';
                        }
                    }
                    $this->data['parishioners'] = $parishioners->getCollection()->transform(function ($parishioners) {
                        
                        $parishioners['slug'] = url(slug($parishioners).$this->url_prefix);
                        
                        $parishioners['edit'] = config('app.url') . '/admin/parishioners/'.$parishioners->id.'/edit';
                        
                        $holy = Holymanagement::where('id', $parishioners['holy'])->first();
                        
                        if(!empty($holy->name)){
                            $parishioners['holy'] = $holy->name;
                        }else{
                            $parishioners['holy'] = '';
                        }
                        
                        if($parishioners->sex == 0){
                            $parishioners['sex'] = 'Nữ';
                        }else{
                            $parishioners['sex'] = 'Nam';
                        }
                        
                        if(!empty($parishioners->resi_ward)){
                            $xaphuong = $this->GetXaTruQuan($parishioners->resi_ward);
                            if(!empty($xaphuong)){
                                $parishioners['resi_ward'] = $xaphuong['name'];
                            }else{
                                $parishioners['resi_ward'] = '';
                            }
                        }else{
                            $parishioners['resi_ward'] = '';
                        }
                        
                        if(!empty($parishioners->resi_province)){
                            $tinhthanh = $this->GetTinhThanhQuan($parishioners->resi_province);
                            $parishioners['resi_province'] = $tinhthanh;
                        }else{
                            $parishioners['resi_province'] = '';
                        }
                        
                        if(!empty($parishioners->paid)){
                            $parish = Parish::where('id', $parishioners['paid'])->first();
                            $parishioners['paid'] = $parish->name;
                        }else{
                            $parishioners['paid'] = '';
                        }
                        
                        if(!empty($parishioners->pid)){
                            $parish_management = ParishManagement::where('id', $parishioners['pid'])->first();
                            $parishioners['pid'] = $parish_management->name;
                        }else{
                            $parishioners['pid'] = '';
                        }
                        
                        if(!empty($parishioners->deid)){
                            $deanery = Deanery::where('id', $parishioners['deid'])->first();
                            $parishioners['deid'] = $deanery->name;
                        }else{
                            $parishioners['deid'] = '';
                        }
                        
                        if(!empty($parishioners->did)){
                            $diocese = Diocese::where('id', $parishioners['did'])->first();
                            $parishioners['did'] = $diocese->name;
                        }else{
                            $parishioners['did'] = '';
                        }
                        
                        return $parishioners;
                    });
                }else{
                    $this->data['parishioners'] = '';
                }
                
                if(!empty($parishioners)){
                    $this->data['pagination'] = $parishioners->links();
                }else{
                    $this->data['pagination'] = '';
                }      
            }
            
            if($page->template === 'giadinh') {
                $family = array();
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1 AND $userId > 2){
                    $this->data['form'] = 0;
                    $this->data['giaoho'] = Parish::where('pid', $decen->pid)->where('status', 1)->get()->toArray();
                    if(!empty($_GET['giaoho'])){
                        $giaoho = $_GET['giaoho'];
                    }else{
                        $giaoho = '';
                    }
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $family = Family::where('name', 'like', '%' . $_GET['keyword'] . '%')
                        ->where('did', $decen->did)
                        ->where('deid', $decen->deid)
                        ->where('pid', $decen->pid)
                        ->where('paid', $giaoho)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        if(!empty($giaoho)){
                            $family = Family::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('paid', $giaoho)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $family = Family::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;
                    $giaoho = array();
                    if(!empty($_GET['giaophan']) AND !empty($_GET['giaohat']) AND !empty($_GET['giaoxu'])){
                        $giaoho = Parish::where('did', $_GET['giaophan'])->where('deid', $_GET['giaohat'])->where('pid', $_GET['giaoxu'])->where('status', 1)->get()->toArray();
                    }
                    $this->data['giaoho'] = $giaoho;
                    if(!empty($_GET['giaoho'])){
                        $giaoho = $_GET['giaoho'];
                    }else{
                        $giaoho = '';
                    }
                    if(!empty($_GET['giaophan'])){
                        $giaophan = $_GET['giaophan'];
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = $_GET['giaohat'];
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = $_GET['giaoxu'];
                    }else{
                        $giaoxu = '';
                    }
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $family = Family::where('name', 'like', '%' . $_GET['keyword'] . '%')
                        ->where('did', $giaophan)
                        ->where('deid', $giaohat)
                        ->where('pid', $giaoxu)
                        ->where('paid', $giaoho)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        if(!empty($giaoho) AND is_int($giaoho)){
                            $family = Family::where('paid', $giaoho)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('deid', $giaohat)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                                $family = Family::where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }else{
                                $family = Family::where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }                         
                        }
                    }
                }
                
                if(!empty($family)){
                    $stt_start = $family->firstItem();
                    foreach ($family as $item){
                        $stt_start++;
                        $item['stt'] = $stt_start-1;
                    }
                    $this->data['family'] = $family->getCollection()->transform(function ($family) {
                        $family['slug'] = slug($family).$this->url_prefix;
                        
                        $family['edit'] = config('app.url') . '/admin/family/'.$family->id.'/edit';
                        
                        // giao họ
                        if(!empty($family->paid)){
                            $parish = Parish::where('id', $family['paid'])->first();
                            $family['paid'] = $parish->name;
                        }else{
                            $family['paid'] = '';
                        }
                        
                        if(!empty($family->pid)){
                            $parish_management = ParishManagement::where('id', $family['pid'])->first();
                            $family['pid'] = $parish_management->name;
                        }else{
                            $family['pid'] = '';
                        }
                        
                        if(!empty($family->deid)){
                            $deanery = Deanery::where('id', $family['deid'])->first();
                            $family['deid'] = $deanery->name;
                        }else{
                            $family['deid'] = '';
                        }
                        
                        if(!empty($family->did)){
                            $diocese = Diocese::where('id', $family['did'])->first();
                            $family['did'] = $diocese->name;
                        }else{
                            $family['did'] = '';
                        }
                        
                        // cha mẹ
                        if(!empty($family->father)){
                            $cha = Parishioners::where('id', $family['father'])->where('sex', 1)->first();
                            if(!empty($cha->name)){
                                $family['father'] = $cha->last_name . ' ' . $cha->name;
                            }else{
                                $family['father'] = '';
                            }
                            if(!empty($cha->holy)){
                                $holy = Holymanagement::where('id', $cha->holy)->get()->first();
                                if(!empty($holy->name)){
                                    $family['holy_cha'] = $holy->name;
                                }else{
                                    $family['holy_cha'] = '';
                                }
                            }else{
                                $family['holy_cha']= '';
                            }
                        }else{
                            $family['father'] = '';
                            $family['holy_cha']= '';
                        }
                        
                        if(!empty($family->mother)){
                            $me = Parishioners::where('id', $family['mother'])->where('sex', 0)->first();
                            if(!empty($me->name)){
                                $family['mother'] = $me->last_name . ' ' . $me->name;
                            }else{
                                $family['mother'] = '';
                            }
                            if(!empty($me->holy)){
                                $holy = Holymanagement::where('id', $me->holy)->first();
                                if(!empty($holy->name)){
                                    $family['holy_me'] = $holy->name;
                                }else{
                                    $family['holy_me'] = '';
                                }
                            }else{
                                $family['holy_me'] = '';
                            }
                        }else{
                            $family['mother'] = '';
                            $family['holy_me']= '';
                        }
                        
                        // địa chỉ
                        if(!empty($family->ward)){
                            $xaphuong = $this->GetXaTruQuan($family->ward);
                            if(!empty($xaphuong)){
                                $family['ward'] = $xaphuong['name'];
                            }else{
                                $family['ward'] = '';
                            }
                        }else{
                            $family['ward'] = '';
                        }
                        
                        if(!empty($family->province)){
                            $tinhthanh = $this->GetTinhThanhQuan($family->province);
                            $family['province'] = $tinhthanh;
                        }else{
                            $family['province'] = '';
                        }
                        
                        return $family;
                    });
                                            
                    $this->data['pagination'] = $family->links();
                }else{
                    $this->data['pagination'] = '';
                }  
            }
            
            if($page->template === 'hoidoan') {
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1 AND $userId > 2){
                    $this->data['form'] = 0;
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                        ->where('did', $decen->did)
                        ->where('deid', $decen->deid)
                        ->where('pid', $decen->pid)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        $association = Association::where('did', $decen->did)
                        ->where('deid', $decen->deid)
                        ->where('pid', $decen->pid)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }
                }elseif($userId < 3 OR !empty($setadmin)){
                    $this->data['form'] = 1;
                    
                    if(!empty($_GET['giaophan'])){
                        $giaophan = $_GET['giaophan'];
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = $_GET['giaohat'];
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = $_GET['giaoxu'];
                    }else{
                        $giaoxu = '';
                    }
                    
                    if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')                            
                            ->where('did', $giaophan)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('did', $giaophan)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('deid', $giaohat)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('deid', $giaohat)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }else{
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $association = Association::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $association = Association::where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                }
                
                if(!empty($association)){
                    $this->data['association'] = $association->getCollection()->transform(function ($association) {
                        $association['slug'] = slug($association).$this->url_prefix;
                        
                        // giao họ
                        if($association->pid != ''){
                            $parish_management = ParishManagement::where('id', $association['pid'])->first();
                            $association['pid'] = $parish_management->name;
                        }else{
                            $association['pid'] = '';
                        }
                        
                        if($association->deid != ''){
                            $deanery = Deanery::where('id', $association['deid'])->first();
                            $association['deid'] = $deanery->name;
                        }else{
                            $association['deid'] = '';
                        }
                        
                        if($association->did != ''){
                            $diocese = Diocese::where('id', $association['did'])->first();
                            $association['did'] = $diocese->name;
                        }else{
                            $association['did'] = '';
                        }
                        
                        return $association;
                    });
                    
                    $this->data['pagination'] = $association->links();
                }
            }
            
            if($page->template === 'raohonphoi') {                
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1 AND $userId > 2){
                    $this->data['form'] = 0;
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                    }else{
                        $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                    }
                }elseif($userId < 3 OR !empty($setadmin)){
                    $this->data['form'] = 1;
                    
                    if(!empty($_GET['giaophan'])){
                        $giaophan = $_GET['giaophan'];
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = $_GET['giaohat'];
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = $_GET['giaoxu'];
                    }else{
                        $giaoxu = '';
                    }
                    
                    if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('did', $giaophan)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('did', $giaophan)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('deid', $giaohat)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('deid', $giaohat)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }  
                    }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }else{
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $marriageannouncement = MarriageAnnouncement::where('name', 'like', '%' . $_GET['keyword'] . '%')
                            ->where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $marriageannouncement = MarriageAnnouncement::where('status', 1)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }                                      
                }
                if(!empty($marriageannouncement)){                    
                    $this->data['marriageannouncement'] = $marriageannouncement->getCollection()->transform(function ($marriageannouncement) {
                        $marriageannouncement['slug'] = slug($marriageannouncement).$this->url_prefix;
                        
                        // linh mục
                        if($marriageannouncement->priest != ''){
                            $sacramentgiver = SacramentGiver::where('id', $marriageannouncement['priest'])->first();
                            $marriageannouncement['priest'] = $sacramentgiver->name;
                        }else{
                            $marriageannouncement['priest'] = '';
                        }
                        
                        // Nam Male
                        $male_parish = MarriageParishioner::where('idannouncement', $marriageannouncement['id'])->where('sex', 1)->first();
                        if(!empty($male_parish->idgiaodan)){
                            $parishioners = Parishioners::where('id', $male_parish->idgiaodan)->where('sex', 1)->where('status', 1)->first();
                                if(!empty($parishioners->holy)){
                                $holy = Holymanagement::where('id', $parishioners->holy)->get()->first();
                                if(!empty($holy->name)){
                                    $marriageannouncement['holy_male'] = $holy->name;
                                }else{
                                    $marriageannouncement['holy_male'] = '';
                                }
                                
                                if(!empty($parishioners->name)){
                                    $marriageannouncement['nam'] = $marriageannouncement['holy_male'] . ' ' . $parishioners->last_name . ' ' .  $parishioners->name;
                                }
                            }
                        }
                        
                        // Nu Female
                        $female_parish = MarriageParishioner::where('idannouncement', $marriageannouncement['id'])->where('sex', 0)->first();
                        if(!empty($female_parish->idgiaodan)){
                            $parishioners = Parishioners::where('id', $female_parish->idgiaodan)->where('sex', 0)->where('status', 1)->first();
                            if(!empty($parishioners->holy)){
                                $holy = Holymanagement::where('id', $parishioners->holy)->get()->first();
                                if(!empty($holy->name)){
                                    $marriageannouncement['holy_female'] = $holy->name;
                                }else{
                                    $marriageannouncement['holy_female'] = '';
                                }
                                if(!empty($parishioners->name)){
                                    $marriageannouncement['nu'] = $marriageannouncement['holy_female'] . ' ' . $parishioners->last_name . ' ' . $parishioners->name;
                                }
                            }
                        }
                        
                        if(!empty($marriageannouncement['announcements_one'])){
                            $marriageannouncement['announcements_one'] = date('d-m-Y', strtotime($marriageannouncement['announcements_one']));
                        }
                        if(!empty($marriageannouncement['announcements_two'])){
                            $marriageannouncement['announcements_two'] = date('d-m-Y', strtotime($marriageannouncement['announcements_two']));
                        }
                        if(!empty($marriageannouncement['announcements_three'])){
                            $marriageannouncement['announcements_three'] = date('d-m-Y', strtotime($marriageannouncement['announcements_three']));
                        }
                        
                        return $marriageannouncement;
                    });
                    
                    $this->data['pagination'] = $marriageannouncement->links();
                }
            }
            
            if($page->template === 'bitich') {
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->parish == 1 AND $userId > 2){
                    $this->data['form'] = 0;
                    $dioceses = Diocese::where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                    
                    $this->data['giaophan'] = $dioceses;
                    
                    $this->data['giaohat'] = $this->data['giaoxu'] = $this->data['giaoho'] = array();
                    
                    $deanerys = array();
                    if(!empty($decen->did)){
                        $deanerys = Deanery::where('status', 1)->where('did', $decen->did)->orderBy('created_at', 'ASC')->get()->toArray();
                        $this->data['giaohat'] = $deanerys;
                        
                        if(!empty($deanerys)){
                            $parish_mana = ParishManagement::where('diocese', $decen->did)->where('deanerys', $decen->deid)->where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                            $this->data['giaoxu'] = $parish_mana;
                        }
                    }                    
                    $parishioners = array();                    
                    if(!empty($_GET['bitich'])){
                        $time_start = $_GET['time_start'];
                        $time_start = str_replace('/', '-', $time_start);
                        $time_start = date('Y-m-d', strtotime($time_start));
                        
                        $time_stop = $_GET['time_stop'];
                        $time_stop = str_replace('/', '-', $time_stop);
                        $time_stop = date('Y-m-d', strtotime($time_stop));
                        
                        if($_GET['bitich'] == 1){
                            $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)->where('baptism_date', '<=' , $time_stop)->where('did', $decen->did)->where('deid', $decen->deid)->where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
                        }
                        if($_GET['bitich'] == 2){
                            $parishioners = Parishioners::where('communion_date', '>=' , $time_start)->where('communion_date', '<=' , $time_stop)->where('did', $decen->did)->where('deid', $decen->deid)->where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
                        }
                        if($_GET['bitich'] == 3){
                            $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)->where('more_power_date', '<=' , $time_stop)->where('did', $decen->did)->where('deid', $decen->deid)->where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
                        }
                        if($_GET['bitich'] == 4){
                            $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)->where('baptism_date', '<=' , $time_stop)->where('did', $decen->did)->where('deid', $decen->deid)->where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'asc')->paginate($this->per_page)->withQueryString();
                        }
                    }else{
                        $parishioners = Parishioners::where('status', 0)->orderBy('name', 'asc')->paginate($this->per_page);
                    }
                }elseif($userId < 3 OR !empty($setadmin)){
                    $this->data['form'] = 1;
                    $parishioners = array();
                    if(!empty($_GET['bitich'])){
                        $time_start = $_GET['time_start'];
                        $time_start = str_replace('/', '-', $time_start);
                        $time_start = date('Y-m-d', strtotime($time_start));
                        
                        $time_stop = $_GET['time_stop'];
                        $time_stop = str_replace('/', '-', $time_stop);
                        $time_stop = date('Y-m-d', strtotime($time_stop));
                        
                        if(!empty($_GET['giaophan'])){
                            $giaophan = $_GET['giaophan'];
                        }else{
                            $giaophan = '';
                        }
                        if(!empty($_GET['giaohat'])){
                            $giaohat = $_GET['giaohat'];
                        }else{
                            $giaohat = '';
                        }
                        if(!empty($_GET['giaoxu'])){
                            $giaoxu = $_GET['giaoxu'];
                        }else{
                            $giaoxu = '';
                        }
                        if(!empty($_GET['giaoho'])){
                            $giaoho = $_GET['giaoho'];
                        }else{
                            $giaoho = '';
                        }
                        
                        if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu)) AND !empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('paid', $giaoho)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu)) AND empty(intval($giaoho))){
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }else{
                            if($_GET['bitich'] == 1){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 2){
                                $parishioners = Parishioners::where('communion_date', '>=' , $time_start)
                                ->where('communion_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 3){
                                $parishioners = Parishioners::where('more_power_date', '>=' , $time_start)
                                ->where('more_power_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                            if($_GET['bitich'] == 4){
                                $parishioners = Parishioners::where('baptism_date', '>=' , $time_start)
                                ->where('baptism_date', '<=' , $time_stop)
                                ->where('status', 1)
                                ->orderBy('name', 'asc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }
                    }else{
                        $parishioners = Parishioners::where('status', 0)->orderBy('name', 'asc')->paginate($this->per_page);
                    }
                }
                if(!empty($parishioners)){                    
                    $this->data['parishioners'] = $parishioners->getCollection()->transform(function ($parishioners) {
                        $parishioners['slug'] = slug($parishioners).$this->url_prefix;
                        
                        $holy = Holymanagement::where('id', $parishioners['holy'])->get()->first();
                        
                        if(!empty($holy->name)){
                            $parishioners['holy'] = $holy->name;
                        }else{
                            $parishioners['holy'] = '';
                        }
                        
                        if($parishioners->sex == 0){
                            $parishioners['sex'] = 'Nữ';
                        }else{
                            $parishioners['sex'] = 'Nam';
                        }
                        
                        if(!empty($parishioners->birthday) AND strlen($parishioners->birthday) == 10){
                            $parishioners['birthday'] = date('d-m-Y', strtotime($parishioners->birthday));
                        }else{
                            $parishioners['birthday'] = '';
                        }
                        
                        // giao họ
                        if($parishioners->paid != ''){
                            $parish_management = Parish::where('id', $parishioners['paid'])->get()->first();
                            $parishioners['paid'] = $parish_management->name;
                        }else{
                            $parishioners['paid'] = '';
                        }
                        
                        if($parishioners->pid != ''){
                            $parish_management = ParishManagement::where('id', $parishioners['pid'])->get()->first();
                            $parishioners['pid'] = $parish_management->name;
                        }else{
                            $parishioners['pid'] = '';
                        }
                        
                        if($parishioners->deid != ''){
                            $deanery = Deanery::where('id', $parishioners['deid'])->get()->first();
                            $parishioners['deid'] = $deanery->name;
                        }else{
                            $association['deid'] = '';
                        }
                        
                        if($parishioners->did != ''){
                            $diocese = Diocese::where('id', $parishioners['did'])->get()->first();
                            $parishioners['did'] = $diocese->name;
                        }else{
                            $parishioners['did'] = '';
                        }
                        
                        if($parishioners->ward != ''){
                            $xaphuong = $this->GetXaTruQuan($parishioners->ward);
                            $parishioners['ward'] = $xaphuong['name'];
                        }else{
                            $parishioners['ward'] = '';
                        }
                        
                        if($parishioners->province != ''){
                            $tinhthanh = $this->GetTinhThanhQuan($parishioners->province);
                            $parishioners['province'] = $tinhthanh;
                        }else{
                            $parishioners['province'] = '';
                        }
                        
                        return $parishioners;
                    });
                    
                    $this->data['pagination'] = $parishioners->links();
                }
                
                $array_bitich = array(
                    '1'     => 'Rửa tội',
                    '2'     => 'Xưng tội, rước lễ',
                    '3'     => 'Thêm sức',
                    '4'     => 'Hôn phối',
                );
                
                $this->data['bitich'] = $array_bitich;
                
            }
            
            if($page->template === 'khoihoc') {  
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'DESC')->get()->toArray();
                $this->data['array_year'] = $namhoc; 
                
                if(!empty($decen) AND $decen->student == 1){
                    $this->data['form'] = 0;    
                    if(!empty($_GET['namhoc'])){
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $block = Block::where('name', 'like', '%' . $keyword . '%')
                            ->where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('namhoc', $_GET['namhoc'])
                            ->where('status', 1)
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $block = Block::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('namhoc', $_GET['namhoc'])
                            ->where('status', 1)
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }else{
                        if(!empty($_GET['keyword'])){
                            $keyword = $_GET['keyword'];
                            $block = Block::where('name', 'like', '%' . $keyword . '%')
                            ->where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('status', 1)
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $block = Block::where('did', $decen->did)
                            ->where('deid', $decen->deid)
                            ->where('pid', $decen->pid)
                            ->where('status', 1)
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;
                    if(!empty($_GET['giaophan'])){
                        $giaophan = $_GET['giaophan'];
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = $_GET['giaohat'];
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = $_GET['giaoxu'];
                    }else{
                        $giaoxu = '';
                    }
                    
                    if(!empty($_GET['keyword'])){
                        $keyword = $_GET['keyword'];
                        $block = Block::where('name', 'like', '%' . $keyword . '%')
                        ->where('did', $giaophan)
                        ->where('deid', $giaohat)
                        ->where('pid', $giaoxu)
                        ->where('namhoc', $_GET['namhoc'])
                        ->where('status', 1)
                        ->orderBy('name', 'desc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                    }else{
                        if(!empty($_GET['namhoc'])){
                            $block = Block::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('namhoc', $_GET['namhoc'])
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $block = Block::where('status', 1)
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->orderBy('name', 'desc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                    
                }
                if(!empty($block)){                    
                    $this->data['block'] = $block->getCollection()->transform(function ($block) {
                        
                        $block['slug'] = url(slug($block).$this->url_prefix);
                        
                        if(!empty($block['namhoc'])){
                            $namhoc = NamHoc::where('id', $block['namhoc'])->where('status', 1)->get()->first();
                            $block['namhoc'] = $namhoc->name;
                        }
                        
                        if($block->paid != ''){
                            $parish = Parish::where('id', $block['paid'])->first();
                            $block['paid'] = $parish->name;
                        }else{
                            $block['paid'] = '';
                        }
                        
                        if($block->pid != ''){
                            $parish_management = ParishManagement::where('id', $block['pid'])->first();
                            $block['pid'] = $parish_management->name;
                        }else{
                            $block['pid'] = '';
                        }
                        
                        if($block->deid != ''){
                            $deanery = Deanery::where('id', $block['deid'])->first();
                            $block['deid'] = $deanery->name;
                        }else{
                            $block['deid'] = '';
                        }
                        
                        if($block->did != ''){
                            $diocese = Diocese::where('id', $block['did'])->first();
                            $block['did'] = $diocese->name;
                        }else{
                            $block['did'] = '';
                        }
                        
                        return $block;
                    });
                    
                    $this->data['pagination'] = $block->links();
                }
            }
            
            if($page->template === 'timkiem') {
                $student = array();
                if(!empty($_GET)){
                    $user = backpack_user();
                    $userId = $user->id;
                    $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                    $decen = Decen::where('use', $userId)->where('pid', $_GET['giaoxu'])->where('status', '1')->get()->first();
                    if(!empty($decen) AND $decen->student == 1 AND $userId > 2){
                        $this->data['form'] = 0;
                        if(empty($_GET['keyword'])){
                            $keyword = '';
                        }else{
                            $keyword = $_GET['keyword'];
                        }
                        
                        if(!empty($keyword)){
                            $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('pid', $decen->pid)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                        }
                    }elseif($userId < 3 OR !empty($setadmin)){
                        $this->data['form'] = 1;
                        if(empty($_GET['keyword'])){
                            $keyword = '';
                        }else{
                            $keyword = $_GET['keyword'];
                        }
                        if(!empty($_GET['giaophan'])){
                            $giaophan = $_GET['giaophan'];
                        }else{
                            $giaophan = '';
                        }
                        if(!empty($_GET['giaohat'])){
                            $giaohat = $_GET['giaohat'];
                        }else{
                            $giaohat = '';
                        }
                        if(!empty($_GET['giaoxu'])){
                            $giaoxu = $_GET['giaoxu'];
                        }else{
                            $giaoxu = '';
                        }
                        
                        if(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('did', $giaophan)
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('did', $giaophan)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(!empty(intval($giaophan)) AND empty(intval($giaohat)) AND empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('did', $giaophan)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('deid', $giaohat)
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND !empty(intval($giaohat)) AND empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('deid', $giaohat)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }elseif(empty(intval($giaophan)) AND empty(intval($giaohat)) AND !empty(intval($giaoxu))){
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('pid', $giaoxu)
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }else{
                            if(!empty($keyword)){
                                $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                                ->Orwhere('phone', 'like', '%' . $keyword . '%')
                                ->where('status', 1)
                                ->orderBy('name', 'desc')
                                ->paginate($this->per_page)
                                ->withQueryString();
                            }
                        }
                    }
                    
                    if(!empty($student)){
                        foreach($student as $item){
                            $item['slug'] = url(slug($item).$this->url_prefix);
                            
                            $item['thugioithieu'] = $item['slug'] . '/thugioithieu=' . $item->id;
                            
                            $holy = Holymanagement::where('id', $item['holy'])->first();
                            
                            if(!empty($holy->name)){
                                $item['holy'] = $holy->name;
                            }else{
                                $item['holy'] = '';
                            }
                            
                            if($item->sex == 0){
                                $item['sex'] = 'Nữ';
                            }else{
                                $item['sex'] = 'Nam';
                            }
                            
                            if(!empty($item->birthday) AND strlen($item->birthday) == 10){
                                $item['birthday'] = date('d-m-Y', strtotime($item->birthday));
                            }else{
                                $item['birthday'] = '';
                            }
                            
                            // giao họ
                            if(!empty($item->paid)){
                                $parish_management = Parish::where('id', $item['paid'])->first();
                                $item['paid'] = $parish_management->name;
                            }else{
                                $item['paid'] = '';
                            }
                            
                            if(!empty($item->pid)){
                                $parish_management = ParishManagement::where('id', $item['pid'])->first();
                                $item['pid'] = $parish_management->name;
                            }else{
                                $item['pid'] = '';
                            }
                            
                            if(!empty($item->deid)){
                                $deanery = Deanery::where('id', $item['deid'])->first();
                                $item['deid'] = $deanery->name;
                            }else{
                                $association['deid'] = '';
                            }
                            
                            if(!empty($item->did)){
                                $diocese = Diocese::where('id', $item['did'])->first();
                                $item['did'] = $diocese->name;
                            }else{
                                $item['did'] = '';
                            }
                        }
                        $this->data['student'] = $student;
                        //return $student;
                        
                        $this->data['pagination'] = $student->links(); 
                    }
                }
            }
            
            if($page->template === 'thongke') {
                $user = backpack_user();
                $userId = $user->id;
                $decen = Decen::where('use', $user->id)->where('status', '1')->get()->toArray();
                
                $array_thongke = array();
                foreach($decen as $key => $row){
                    
                    $giaoxu = ParishManagement::where('id', $row['pid'])->get()->first();
                    
                    $array_thongke[$row['id']]['giaoxu']['name'] = $giaoxu->name; 
                    $array_thongke[$row['id']]['giaoxu']['student'] = $row['student'];
                    $array_thongke[$row['id']]['giaoxu']['parish'] = $row['parish'];
                    
                    $array_thongke[$row['id']]['teacher']['teacher_count'] = $teacher_count = Teacher::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['teacher']['teacher_counttotal'] = $teacher_counttotal = Teacher::where('status', 1)->get()->count();
                    if(!empty($teacher_count) AND !empty($teacher_counttotal)){
                        $teacher_total = $teacher_count/$teacher_counttotal*100;
                    }else{
                        $teacher_total = 0;
                    }
                    $array_thongke[$row['id']]['teacher']['teacher_total'] = $teacher_total;
                    
                    $array_thongke[$row['id']]['hocsinh']['hocsinh_count'] = $hocsinh_count = Student::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['hocsinh']['hocsinh_counttotal'] = $hocsinh_counttotal = Student::where('status', 1)->get()->count();
                    if(!empty($hocsinh_count) AND !empty($hocsinh_counttotal)){
                        $hocsinh_total = $hocsinh_count/$hocsinh_counttotal*100;
                    }else{
                        $hocsinh_total = 0;
                    }
                    $array_thongke[$row['id']]['hocsinh']['hocsinh_total'] = $hocsinh_total;
                    
                    $array_thongke[$row['id']]['lop']['lop_count'] = $lop_count = CatechismClass::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['lop']['lop_counttotal'] = $lop_counttotal = CatechismClass::where('status', 1)->get()->count();
                    if(!empty($lop_count) AND !empty($lop_counttotal)){
                        $lop_total = $lop_count/$lop_counttotal*100;
                    }else{
                        $lop_total = 0;
                    }
                    $array_thongke[$row['id']]['lop']['lop_total'] = $lop_total;
                    
                    $array_thongke[$row['id']]['block']['block_count'] = $block_count = Block::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['block']['block_counttotal'] = $block_counttotal = Block::where('status', 1)->get()->count();
                    if(!empty($block_count) AND !empty($block_counttotal)){
                        $block_total = $block_count/$block_counttotal*100;
                    }else{
                        $block_total = 0;
                    }
                    $array_thongke[$row['id']]['block']['block_total'] = $block_total;
                    
                    $array_thongke[$row['id']]['association']['association_count'] = $association_count = Association::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['association']['association_counttotal'] = $association_counttotal = Association::where('status', 1)->get()->count();
                    if(!empty($association_count) AND !empty($association_counttotal)){
                        $association_total = $association_count/$association_counttotal*100;
                    }else{
                        $association_total = 0;
                    }
                    $array_thongke[$row['id']]['association']['association_total'] = $association_total;
                    
                    $array_thongke[$row['id']]['parishioners']['parishioners_count'] = $parishioners_count = Parishioners::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['parishioners']['parishioners_counttotal'] = $parishioners_counttotal = Parishioners::where('status', 1)->get()->count();
                    if(!empty($parishioners_count) AND !empty($parishioners_counttotal)){
                        $parishioners_total = $parishioners_count/$parishioners_counttotal*100;
                    }else{
                        $parishioners_total = 0;
                    }
                    $array_thongke[$row['id']]['parishioners']['parishioners_total'] = $parishioners_total;
                    
                    $array_thongke[$row['id']]['family']['family_count'] = $family_count = Family::where('status', 1)->where('pid', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['family']['family_counttotal'] = $family_counttotal = Family::where('status', 1)->get()->count();
                    if(!empty($family_count) AND !empty($family_counttotal)){
                        $family_total = $family_count/$family_counttotal*100;
                    }else{
                        $family_total = 0;
                    }
                    $array_thongke[$row['id']]['family']['family_total'] = $family_total;
                    
                    $array_thongke[$row['id']]['marriage']['marriage_count'] = $marriage_count = MarriageAnnouncementParishioners::where('status', 1)->where('parishmanagements', $row['pid'])->get()->count();
                    $array_thongke[$row['id']]['marriage']['marriage_counttotal'] = $marriage_counttotal = MarriageAnnouncementParishioners::where('status', 1)->get()->count();
                    if(!empty($marriage_count) AND !empty($marriage_counttotal)){
                        $marriage_total = $marriage_count/$marriage_counttotal*100;
                    }else{
                        $marriage_total = 0;
                    }
                        
                    $array_thongke[$row['id']]['marriage']['marriage_total'] = $marriage_total;
                }
                
                $this->data['thongke'] = $array_thongke;    
            }
            
            if($page->template === 'khoilop') {
                $student = array();
                $user = backpack_user();
                $userId = $user->id;
                
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'DESC')->get()->toArray();                
                $this->data['array_year'] = $namhoc; 
                
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();                
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->student == 1){
                    $giaoho = Parish::where('pid', $decen->pid)->where('status', 1)->orderBy('name', 'asc')->get()->toArray();
                    $this->data['giaohos'] = $giaoho;
                    
                    if(!empty($_GET['schoolyear'])){
                        $this->data['block'] = $block = Block::where('pid', $decen->pid)->where('namhoc', $_GET['schoolyear'])->where('status', 1)->orderBy('name', 'asc')->get()->toArray();
                        
                        $lop = CatechismClass::where('pid', $decen->pid)->where('block', $_GET['block'])->orderBy('name', 'asc')->where('status', 1)->get()->toArray();
                        
                        $this->data['lop'] = $lop;
                    }else{
                        $this->data['block'] = '';
                        $this->data['lop'] = '';
                    }
                }elseif(!empty($setadmin)){
                    if(!empty($_GET['giaoxu'])){
                        $giaho = Parish::where('pid', $_GET['giaoxu'])->get()->toArray();
                        $this->data['giaohos'] = $giaho;
                    }else{
                        $this->data['giaohos'] = '';
                    }
                    if(!empty($_GET['schoolyear'])){
                        $this->data['block'] = $block = Block::where('pid', $_GET['giaoxu'])->where('namhoc', $_GET['schoolyear'])->where('status', 1)->orderBy('name', 'asc')->get()->toArray();
                    }else{
                        $this->data['block'] = '';
                    }
                }
                
                if(!empty($_GET['keyword'])){
                    $keyword = $_GET['keyword'];
                }else{
                    $keyword = '';
                }
                if(!empty($_GET['schoolyear'])){
                    $schoolyear = $_GET['schoolyear'];
                }else{
                    $schoolyear = '';
                }
                if(!empty($_GET['giaophan'])){
                    $giaophanx = $_GET['giaophan'];
                }else{
                    $giaophanx = '';
                }
                if(!empty($_GET['giaohat'])){
                    $giaohatx = $_GET['giaohat'];
                }else{
                    $giaohatx = '';
                }
                if(!empty($_GET['giaoho'])){
                    $giaohox = $_GET['giaoho'];
                }else{
                    $giaohox = '';
                }
                
                if(!empty($decen) AND $decen->student == 1){
                    $this->data['form'] = 0;
                    
                    if(!empty($_GET['lop'])){
                        $lops = $_GET['lop'];
                    }else{
                        $lops = '';
                    }
                    
                    if(!empty($giaohox)){
                        if(!empty($keyword)){
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.name', $keyword)
                            ->where('student.pid', $decen->pid)
                            ->where('student.paid', $giaohox)
                            ->where('student.status', 1)
                            //->where('lop.schoolyear', $schoolyear)
                            //->where('lop.block', $block)
                            ->where('student.lop', $lops)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.pid', $decen->pid)
                            ->where('student.paid', $giaohox)
                            ->where('student.status', 1)
                            //->where('lop.schoolyear', $schoolyear)
                            //->where('lop.block', $block)
                            ->where('student.lop', $lops)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }else{
                        if(!empty($keyword)){
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.name', $keyword)
                            ->where('student.pid', $decen->pid)
                            ->where('student.status', 1)
                            //->where('lop.schoolyear', $schoolyear)
                            //->where('lop.block', $block)
                            ->where('student.lop', $lops)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.pid', $decen->pid)
                            ->where('student.status', 1)
                            //->where('lop.schoolyear', $schoolyear)
                            //->where('lop.block', $block)
                            ->where('student.lop', $lops)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;
                    
                    if(!empty($_GET['giaophan'])){
                        $giaophan = intval($_GET['giaophan']);
                    }else{
                        $giaophan = '';
                    }
                    if(!empty($_GET['giaohat'])){
                        $giaohat = intval($_GET['giaohat']);
                    }else{
                        $giaohat = '';
                    }
                    if(!empty($_GET['giaoxu'])){
                        $giaoxu = intval($_GET['giaoxu']);
                    }else{
                        $giaoxu = '';
                    }
                    
                    if(!empty($_GET['giaoho'])){
                        $giaoho = intval($_GET['giaoho']);
                    }else{
                        $giaoho = '';
                    }
                    
                    $lopmoi = CatechismClass::where('schoolyear', $schoolyear)->get()->toArray();
                    $array_lop = array();
                    foreach($lopmoi as $item){
                        $array_lop[] = $item['id'];
                    }
                    
                    if(!empty($keyword)){
                        if(!empty($giaoho)){
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.name', $keyword)
                            ->where('student.pid', $giaoxu)
                            ->where('student.paid', $giaoho)
                            ->where('student.status', 1)
                            ->whereIn('lop.schoolyear', $array_lop)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $student = Student::leftJoin('lop', 'student.lop', '=', 'lop.id')
                            ->select('student.*')
                            ->where('student.name', $keyword)
                            ->where('student.pid', $giaoxu)
                            ->where('student.status', 1)
                            ->whereIn('lop.schoolyear', $array_lop)
                            ->orderBy('student.name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }else{
                        if(!empty($giaoho)){
                            $student = Student::select('*')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('paid', $giaoho)
                            ->where('status', 1)
                            ->whereIn('lop', $array_lop)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }else{
                            $student = Student::select('*')
                            ->where('did', $giaophan)
                            ->where('deid', $giaohat)
                            ->where('pid', $giaoxu)
                            ->where('status', 1)
                            ->whereIn('lop', $array_lop)
                            ->orderBy('name', 'asc')
                            ->paginate($this->per_page)
                            ->withQueryString();
                        }
                    }
                }
                
                if(!empty($student)){
                    $stt_start = $student->firstItem();                        
                    foreach($student as $item){
                        $item['stt'] = $stt_start++;
                        
                        $item['slug'] = url(slug($item).$this->url_prefix);
                        
                        $item['thugioithieu'] = $item['slug'] . '/thugioithieu=' . $item->id;
                        
                        $item['edit'] = config('app.url') . '/admin/student/'.$item->id.'/edit';
                        
                        if(!empty($item->holy)){
                            $holy = Holymanagement::where('id', $item['holy'])->get()->first();
                            $item['holy'] = $holy['name'];
                        }else{
                            $item['holy'] = '';
                        }
                        
                        if(!empty($item->birthday) AND strlen($item->birthday) == 10){
                            $item['birthday'] = date('d-m-Y', strtotime($item->birthday));
                        }else{
                            $item['birthday'] = '';
                        }
                        
                        if(!empty($item->paid)){
                            $parish = Parish::where('id', $item['paid'])->first();
                            if(!empty($parish)){
                                $item['paid'] = $parish->name;
                            }else{
                                $item['paid'] = '';
                            }
                        }else{
                            $item['paid'] = '';
                        }
                        
                        if(!empty($item->pid)){
                            $parish_management = ParishManagement::where('id', $item['pid'])->first();
                            $item['pid'] = $parish_management->name;
                        }else{
                            $item['pid'] = '';
                        }
                        
                        if(!empty($item->deid)){
                            $deanery = Deanery::where('id', $item['deid'])->first();
                            $item['deid'] = $deanery->name;
                        }else{
                            $association['deid'] = '';
                        }
                        
                        if(!empty($item->did)){
                            $diocese = Diocese::where('id', $item['did'])->first();
                            $item['did'] = $diocese->name;
                        }else{
                            $item['did'] = '';
                        }
                    }
                    $this->data['student'] = $student;
                    
                    $this->data['pagination'] = $student->links();
                }
                
            }
            
            if($page->template === 'import_lop') {
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'asc')->get()->toArray();
                $this->data['array_year'] = $namhoc;
                
                $student = array();
                $user = backpack_user();
                $userId = $user->id;
                
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'DESC')->get()->toArray();
                $this->data['array_year'] = $namhoc;
                
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->student == 1){
                    $this->data['form'] = 0;
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;                    
                }
            }
            
            if($page->template === 'import_teacher') {
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'asc')->get()->toArray();
                $this->data['array_year'] = $namhoc;
                
                $student = array();
                $user = backpack_user();
                $userId = $user->id;
                
                $namhoc = NamHoc::where('status', 1)->orderBy('name', 'DESC')->get()->toArray();
                $this->data['array_year'] = $namhoc;
                
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($decen) AND $decen->student == 1){
                    $this->data['form'] = 0;
                }elseif(!empty($setadmin)){
                    $this->data['form'] = 1;
                }
            }
            
            if($page->template === 'export_honphoi'){
                $user = backpack_user();
                $userId = $user->id;
                $setadmin = SetAdmin::where('use', $userId)->where('status', 1)->get()->first();
                $decen = Decen::where('use', $userId)->where('status', '1')->get()->first();
                if(!empty($setadmin)){
                    $form = 1;
                }else{
                    $form = 0;
                    $giaoxu = ParishManagement::where('id', $decen->pid)->where('status', 1)->get()->first();
                    $this->data['giaoxu'] = $giaoxu;
                }
                
                $this->data['form'] = $form;
            }
            
            return view()->first([
                'frontend.page.'.$page->template,
                'frontend.page.default',
            ], $this->data);
        
        }else{
            $page = Page::findOrFail($id);
            
            $this->data['name'] = $page->name;
            $this->data['content'] = $page->content;
            $this->data['version'] = $page->id;
            
            \Assets::add('fontawesome');
            
            // SEO
            $this->data['meta_title'] = Str::title(optional($page->extras)->meta_title ?? $page->name);
            $this->data['meta_description'] = optional($page->extras)->meta_description;
            $this->data['meta_keywords'] = optional($page->extras)->meta_keywords;
            $this->data['no_index'] = optional($page->extras)->no_index == 1;
            
            $dioceses = Diocese::where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
            
            $this->data['giaophan'] = $dioceses;
            
            $this->data['giaohat'] = $this->data['giaoxu'] = $this->data['giaoho'] = array();
            
            $deanerys = array();
            if(!empty($_GET['giaophan'])){
                $deanerys = Deanery::where('status', 1)->where('did', $_GET['giaophan'])->orderBy('created_at', 'ASC')->get()->toArray();
                $this->data['giaohat'] = $deanerys;
                
                if(!empty($_GET['giaohat'])){
                    $parish_mana = ParishManagement::where('diocese', $_GET['giaophan'])->where('deanerys', $_GET['giaohat'])->where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                    $this->data['giaoxu'] = $parish_mana;
                    
                    if(!empty($_GET['giaoxu'])){
                        $parish = Parish::where('did', $_GET['giaophan'])->where('deid', $_GET['giaohat'])->where('pid', $_GET['giaoxu'])->where('status', 1)->orderBy('created_at', 'ASC')->get()->toArray();
                        $this->data['giaoho'] = $parish;
                    }
                }
            }
            
            if($page->template === 'timkiem') {
                $student = array();
                if(!empty($_GET)){
                    if(empty($_GET['keyword'])){
                        $keyword = '';
                    }else{
                        $keyword = $_GET['keyword'];
                    }
                    if(!empty($keyword)){
                        $student = Student::where('mahv', 'like', '%' . $keyword . '%')
                        ->Orwhere('phone', 'like', '%' . $keyword . '%')
                        ->where('did', $_GET['giaophan'])
                        ->where('deid', $_GET['giaohat'])
                        ->where('pid', $_GET['giaoxu'])
                        ->where('status', 1)
                        ->orderBy('created_at', 'desc')
                        ->paginate($this->per_page)
                        ->withQueryString();
                        
                        foreach($student as $item){
                            $item['slug'] = url(slug($item).$this->url_prefix);
                            
                            $item['thugioithieu'] = $item['slug'] . '/thugioithieu=' . $item->id;
                            
                            $holy = Holymanagement::where('id', $item['holy'])->first();
                            
                            if(!empty($holy->name)){
                                $item['holy'] = $holy->name;
                            }else{
                                $item['holy'] = '';
                            }
                            
                            if($item->sex == 0){
                                $item['sex'] = 'Nữ';
                            }else{
                                $item['sex'] = 'Nam';
                            }
                            
                            if(!empty($item->birthday) AND strlen($item->birthday) == 10){
                                $item['birthday'] = date('d-m-Y', strtotime($item->birthday));
                            }else{
                                $item['birthday'] = '';
                            }
                            
                            // giao họ
                            if(!empty($item->paid)){
                                $parish_management = Parish::where('id', $item['paid'])->first();
                                $item['paid'] = $parish_management->name;
                            }else{
                                $item['paid'] = '';
                            }
                            
                            if(!empty($item->pid)){
                                $parish_management = ParishManagement::where('id', $item['pid'])->first();
                                $item['pid'] = $parish_management->name;
                            }else{
                                $item['pid'] = '';
                            }
                            
                            if(!empty($item->deid)){
                                $deanery = Deanery::where('id', $item['deid'])->first();
                                $item['deid'] = $deanery->name;
                            }else{
                                $association['deid'] = '';
                            }
                            
                            if(!empty($item->did)){
                                $diocese = Diocese::where('id', $item['did'])->first();
                                $item['did'] = $diocese->name;
                            }else{
                                $item['did'] = '';
                            }
                        }
                        $this->data['student'] = $student;
                        //return $student;
                        
                        $this->data['pagination'] = $student->links();
                    }
                }
            }
            
            
            $url = url()->current();
            if($url == 'https://mvqlgiaoxu.org/tim-kiem'){
                return view()->first([
                    'frontend.page.'.$page->template,
                    'frontend.page.default',
                ], $this->data);
            }else{
                return view('home');
            }
        }
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
