<?php
namespace Boyo\Mobica;

use Illuminate\Support\ServiceProvider;

class MobicaServiceProvider extends ServiceProvider
{
	
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
	        $this->commands([
	            \Boyo\Mobica\Commands\Test::class,
	        ]);
	    }
    }
    
    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(\Boyo\Mobica\MobicaSender::class, function () {
            return new \Boyo\Mobica\MobicaSender();
        });
    }
    
}