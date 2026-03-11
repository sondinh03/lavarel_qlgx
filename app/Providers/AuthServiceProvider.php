<?php

namespace App\Providers;

use App\Models\CatechismClass;
use App\Models\Lop;
use App\Models\NamHoc;
use App\Models\Parish;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\Parishioners;
use App\Models\ScoreType;
use App\Models\Student;
use App\Models\StudentNew;
use App\Models\StudentScore;
use App\Policies\CatechismClassPolicy;
use App\Policies\LopPolicy;
use App\Policies\ParishGroupPolicy;
use App\Policies\ParishionerPolicy;
use App\Policies\ParishionersPolicy;
use App\Policies\SchoolYearPolicy;
use App\Policies\ScoreTypePolicy;
use App\Policies\StudentPolicy;
use App\Policies\StudentScorePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        NamHoc::class => SchoolYearPolicy::class,
        CatechismClass::class => CatechismClassPolicy::class,
        StudentNew::class => StudentPolicy::class,
        Parishioner::class => ParishionerPolicy::class,
        ParishGroup::class => ParishGroupPolicy::class,
        ScoreType::class => ScoreTypePolicy::class,
        StudentScore::class => StudentScorePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
