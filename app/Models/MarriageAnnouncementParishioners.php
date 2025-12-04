<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MarriageAnnouncementParishioners extends Model
{
    use HasFactory;
    
    protected $table = 'marriage_announcements_parishioners';
    // protected $primaryKey = 'id';
    // public $timestamps = false;
    protected $guarded = ['id'];
    // protected $fillable = [];
    // protected $hidden = [];
    // protected $dates = [];
    /*protected $appends = [
        'idannouncement',
        'idgiaodan',
        'sex',
        'diocesesold',
        'deanerysold',
        'parishmanagementsold',
        'parishsold',
        'dioceses',
        'deanerys',
        'parishmanagements',
        'parishs',
        'diocesesbefore',
        'deanerysbefore',
        'parishmanagementsbefore',
        'parishsbefore',
        'status',
    ];*/
}
