<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\SearchEngine;
use App\Services\InputFilter;
use App\Services\Geocoder;
use App\Services\ImageService;
use App\Services\Notifier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('App\Services\InputFilter', function () {
        return new InputFilter();
      });
        
      $this->app->bind('App\Services\Geocoder', function () {
        return new Geocoder();
      });
      
      $this->app->bind('App\Services\SearchEngine', function() {
        return new SearchEngine();
      });
      
      $this->app->bind('App\Services\ImageService', function() {
        return new ImageService();
      });
      
      $this->app->bind('App\Services\Notifier', function() {
        return new Notifier();
      });
      
      $this->app->bind('App\Services\EmbedStyleProcessor', function() {
        return new \App\Services\EmbedStyleProcessor();
      });
      
      $this->app->bind('App\Services\UserWarningManager', function() {
        return new \App\Services\UserWarningManager();
      });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
