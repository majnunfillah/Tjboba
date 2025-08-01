<?php

namespace App\Providers;

use App\Models\DBDEVISI;
use App\Models\DBPERIODE;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $models = array(
            'Base',
            'Global',
            'DBFLPASS',
            'DBPERUSAHAAN',
            'DBPERKIRAAN',
            'DBAKTIVA',
            'Group',
            'BankOrKas',
            'Memorial',
            'DbMenu',
        );
        foreach ($models as $model) {
            $this->app->bind("App\Http\Repository\Task\\{$model}Interface", "App\Http\Repository\\{$model}Repository");
        }
        // $this->app->bind("App\Http\Repository\Task\BaseInterface", "App\Http\Repository\BaseRepository");
        // $this->app->bind("App\Http\Repository\Task\GlobalInterface", "App\Http\Repository\GlobalRepository");
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*', function (View $view) {
            if (auth()->check()) {
                $periode = DBPERIODE::where('USERID', auth()->user()->USERID)->first();
                $devisi = DBDEVISI::first();
                $view->with('periode', $periode)
                    ->with('devisi', $devisi);
            }
        });
    }
}
