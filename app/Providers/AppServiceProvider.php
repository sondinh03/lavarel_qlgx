<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lop;
use App\Models\ClassTeacher;
use App\Models\Holymanagement;
use App\Models\ParishGroup;
use App\Models\Parishioner;
use App\Models\ParishNew;
use App\Models\StudentNew;
use App\Models\Teacher;
use App\Observers\LopObserver;
use App\Observers\ClassTeacherObserver;
use App\Observers\HolymanagementObserver;
use App\Observers\ParishGroupObserver;
use App\Observers\ParishionerObserver;
use App\Observers\ParishNewObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->overrideConfigValues();
        require_once app_path('Helpers/AttendanceHelper.php');

        // register observers to keep cache versioning in sync
        Lop::observe(LopObserver::class);
        ClassTeacher::observe(ClassTeacherObserver::class);
        Parishioner::observe(ParishionerObserver::class);
        ParishNew::observe(ParishNewObserver::class);
        Holymanagement::observe(HolymanagementObserver::class);
        ParishGroup::observe(ParishGroupObserver::class);

        if (
            app()->environment('local') &&
            config('app.sql_debug', false)
        ) {
            DB::listen(function ($query) {
                logger()->debug(
                    $query->sql,
                    $query->bindings
                );
            });
        }

        /**
         * Morph map cho GroupMember
         * Giúp database lưu 'teacher'/'student' thay vì full class name
         * → Dễ đọc, không bị vỡ nếu đổi namespace
         */
        Relation::morphMap([
            'teacher' => Teacher::class,
            'student' => StudentNew::class,
        ]);
        // ==================== CÁCH DÙNG ====================

        // Thêm GLV vào nhóm
        // GroupMember::create([
        //     'group_id'        => $group->id,
        //     'memberable_type' => 'teacher',
        //     'memberable_id'   => $teacher->id,
        //     'role'            => 'Thành viên',
        //     'joined_at'       => today(),
        // ]);

        // Thêm học sinh vào ca đoàn
        // GroupMember::create([
        //     'group_id'        => $group->id,
        //     'memberable_type' => 'student',
        //     'memberable_id'   => $student->id,
        //     'joined_at'       => today(),
        // ]);

        // Load thành viên kèm hồ sơ
        // $group->activeMembers()->with('memberable')->get()
        //     ->each(fn($m) => dump($m->display_name));

        // Báo cáo chuyên cần theo nam_hoc
        // $namHoc = NamHoc::find($id);
        // GroupSession::forGroup($groupId)
        //     ->inDateRanges([
        //         [$namHoc->start_date_one, $namHoc->end_date_one],
        //         [$namHoc->start_date_two, $namHoc->end_date_two],
        //     ])
        //     ->with('attendanceRecords')
        //     ->get();
    }

    protected function overrideConfigValues()
    {
        $config = [];

        if (config('settings.show_powered_by')) {
            $config['backpack.ui.show_powered_by'] = config('settings.show_powered_by') == '1';
        }

        //config($config);

        $modelClass = config('backpack.settings.model', \Backpack\Settings\app\Models\Setting::class);

        // get all settings from the database
        $settings = $modelClass::all();

        $config_prefix = config('backpack.settings.config_prefix');

        // bind all settings to the Laravel config, so you can call them like
        // Config::get('settings.contact_email')
        foreach ($settings as $key => $setting) {
            $prefixed_key = !empty($config_prefix) ? $config_prefix . '.' . $setting->key : $setting->key;
            config([$prefixed_key => $setting->value]);
        }
    }
}
