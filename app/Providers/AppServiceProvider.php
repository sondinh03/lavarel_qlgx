<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Lop;
use App\Models\ClassTeacher;
use App\Models\Parish;
use App\Models\Parishioner;
use App\Observers\LopObserver;
use App\Observers\ClassTeacherObserver;
use App\Observers\ParishionerObserver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

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
