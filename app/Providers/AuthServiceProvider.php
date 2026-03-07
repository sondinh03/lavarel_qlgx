<?php

namespace App\Providers;

use App\Models\CatechismClass;
use App\Models\Lop;
use App\Models\Parishioner;
use App\Models\Parishioners;
use App\Models\StudentNew;
use App\Policies\CatechismClassPolicy;
use App\Policies\LopPolicy;
use App\Policies\ParishionerPolicy;
use App\Policies\ParishionersPolicy;
use App\Policies\StudentPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Lop::class => LopPolicy::class,
        CatechismClass::class => CatechismClassPolicy::class,
        StudentNew::class => StudentPolicy::class,
        Parishioner::class => ParishionerPolicy::class,
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
