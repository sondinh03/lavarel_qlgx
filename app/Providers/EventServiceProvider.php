<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Models\ParishManagement;
use App\Observers\ParishManagementObserver;
use App\Models\Deanery;
use App\Observers\DeaneryObserver;
use App\Models\Diocese;
use App\Observers\DioceseObserver;
use App\Models\Parishioners;
use App\Observers\ParishionersObserver;
use App\Models\Family;
use App\Observers\FamilyObserver;
use App\Models\Children;
use App\Observers\ChildrenObserver;
use App\Models\Marriage;
use App\Observers\MarriageObserver;
use App\Models\MarriageAnnouncement;
use App\Observers\MarriageAnnouncementObserver;
use App\Models\Page;
use App\Observers\PageObserver;
use App\Http\Controllers\PageController;
use App\Models\Association;
use App\Observers\AssociationObserver;
use App\Models\Lop;
use App\Observers\LopObserver;
use App\Models\Student;
use App\Observers\StudentObserver;
use App\Models\Block;
use App\Observers\BlockObserver;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
        ParishManagement::observe(ParishManagementObserver::class);
        Deanery::observe(DeaneryObserver::class);
        Diocese::observe(DioceseObserver::class);
        Parishioners::observe(ParishionersObserver::class);
        Family::observe(FamilyObserver::class);
        Children::observe(ChildrenObserver::class);
        Marriage::observe(MarriageObserver::class);
        MarriageAnnouncement::observe(MarriageAnnouncementObserver::class);
        Page::observe(PageObserver::class);
        Association::observe(AssociationObserver::class);
        Lop::observe(LopObserver::class);
        Student::observe(StudentObserver::class);
        Block::observe(BlockObserver::class);
    }
}
