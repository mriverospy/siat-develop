<?php

namespace App\Providers;

use App\Settings\GeneralSettings;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\ServiceProvider;
use Lang;
use Settings;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set Carbon Language to Spanish
        $localeCode = Lang::getLocale();
        Carbon::setLocale($localeCode);
        setlocale(LC_ALL, $localeCode.'_'.strtoupper($localeCode).'.UTF-8');

        // Parámetros de configuración general
        View::composer('*', function ($view) {
            $view->with('logo_login', Settings::get('logo_login'));
            $view->with('logo_principal', Settings::get('logo_principal'));
            $view->with('logo_cabecera', Settings::get('logo_cabecera'));
            $view->with('isLoggedIn', Auth::check());
            $view->with('currentUser', Auth::user());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('settings',function(){
            return app(GeneralSettings::class);
        });
    }
}
