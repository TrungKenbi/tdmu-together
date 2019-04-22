<?php


namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach (glob(realpath(__DIR__.'/../') . '/Helpers/*.php') as $file) {
            require_once($file);

        }
    }
}
