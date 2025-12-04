<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;


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
        //        
        /*try {
            View::share([
                'url_prefix' => config('settings.url_prefix'),
            ]);
        } catch (Throwable $e) {
            report($e);
        }*/
        $this->overrideConfigValues();
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
            $prefixed_key = !empty($config_prefix) ? $config_prefix.'.'.$setting->key : $setting->key;
            config([$prefixed_key => $setting->value]);
        }
    }
}
