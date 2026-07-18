<?php

namespace App\Observers;

use App\Models\Student;
use App\Http\Controllers\StudentController;
use App\Models\Slug;
use Cocur\Slugify\Slugify;
use App\Models\CatechismClass;
use App\Models\Parishioners;

class StudentObserver
{
    private Slugify $slugify;
    
    public function __construct()
    {
        $this->slugify = new Slugify();
    }
    
    /**
     * Handle the Student "created" event.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function created(Student $student)
    {
        //
    }
    
    /**
     * Handle the Project "saved" event.
     *
     * @param  Association  $student
     * @return void
     */
    public function saved(Student $student)
    {
        if(!empty($_POST['chon_student'])){
            $lop = CatechismClass::where('id', $student->lop)->where('status', 1)->orderBy('id', 'ASC')->first();
            $hocsinh = Parishioners::where('id', $_POST['chon_student'])->orderBy('id', 'ASC')->first();
            Student::where('id', $student->id)->update([
                'mahv'      => $lop->symbol . $student->id,
                'holy'      => $hocsinh->holy,
                'name'      => $hocsinh->name,
                'birthday'  => $hocsinh->birthday,
                'phone'     => $hocsinh->phone,
                'origin'    => $hocsinh->origin,
                'ward'      => $hocsinh->ward,
                'province'  => $hocsinh->province,
                'father'    => $hocsinh->father,
                'mother'    => $hocsinh->mother,
                'cccd'      => $hocsinh->cccd,
                'email'     => $hocsinh->email,
                'baptism_date'          => $hocsinh->baptism_date,
                'baptism_number'        => $hocsinh->baptism_number,
                'baptism_giver'         => $hocsinh->baptism_giver,
                'baptism_sponsor'       => $hocsinh->baptism_sponsor,
                'baptism_dioceses'      => $hocsinh->baptism_dioceses,
                'baptism_deanerys'      => $hocsinh->baptism_deanerys,
                'baptism_parish'        => $hocsinh->baptism_parish,
                'more_power_number'     => $hocsinh->more_power_number,
                'more_power_giver'      => $hocsinh->more_power_giver,
                'more_power_sponsor'    => $hocsinh->more_power_sponsor,
                'more_power_address'    => $hocsinh->more_power_address,
                'more_power_dioceses'   => $hocsinh->more_power_dioceses,
                'more_power_deanerys'   => $hocsinh->more_power_deanerys,
                'more_power_parish'     => $hocsinh->more_power_parish,
                'promise_day'           => $hocsinh->promise_day,
                'note'                  => $hocsinh->note,                
            ]);
        }else{
            if(!empty($student->id)){
                $lop = CatechismClass::where('id', $student->lop)->where('status', 1)->orderBy('id', 'ASC')->first();
                if(!empty($lop->symbol)){
                    Student::where('id', $student->id)->update([
                        'mahv'      => $lop->symbol . $student->id,
                    ]);
                }
            }
        }
        
        if(!empty($_POST['slug'])){
            $sluglink = $_POST['slug'];
        }else{
            if(!empty($_POST['chon_student'])){
                $sluglink = $this->slugify->slugify(request()->slug ?? $hocsinh->name);
            }else{
                $sluglink = $this->slugify->slugify(request()->slug ?? $student->name);
            }
        }
        
        $slug = Slug::where('keyword', '=' , $sluglink)->get()->first();
        if(!empty($slug)){
            if($slug->sluggable_id != $student->id){
                $slugmoi = $sluglink . '-' . $student->id;
                $checkslug = Slug::where('keyword', '=' , $slugmoi)->where('sluggable_id', $student->id)->get()->first();
                if(empty($checkslug)){
                    Slug::create([
                        'keyword' => $slugmoi,
                        'controller' => StudentController::class,
                        'model' => Student::class,
                        'sluggable_id' => $student->id
                    ]);
                }
            }
        }else{
            Slug::create([
                'keyword' => $sluglink,
                'controller' => StudentController::class,
                'model' => Student::class,
                'sluggable_id' => $student->id
            ]);
        }
    }

    /**
     * Handle the Student "updated" event.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function updated(Student $student)
    {
        /*
        Slug::updateOrCreate(
            ['controller' => StudentController::class, 'model' => Student::class, 'sluggable_id' => $student->id],
            ['keyword' => $this->slugify->slugify(request()->slug ?? $student->name)]
        );
        */
    }

    /**
     * Handle the Student "deleted" event.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function deleted(Student $student)
    {
        Slug::where([
            'controller' => StudentController::class,
            'model' => Student::class,
            'sluggable_id' => $student->id,
        ])->forceDelete();
    }

    /**
     * Handle the Student "restored" event.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function restored(Student $student)
    {
        //
    }

    /**
     * Handle the Student "force deleted" event.
     *
     * @param  \App\Models\Student  $student
     * @return void
     */
    public function forceDeleted(Student $student)
    {
        Slug::where([
            'controller' => StudentController::class,
            'model' => Student::class,
            'sluggable_id' => $student->id,
        ])->forceDelete();
    }
}
