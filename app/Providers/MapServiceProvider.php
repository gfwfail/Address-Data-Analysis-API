<?php
/**
 * Created by PhpStorm.
 * User: luye
 * Date: 16/9/10
 * Time: 上午1:17
 */

namespace App\Providers;


use Illuminate\Support\ServiceProvider;

class MapServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('MapService', function($app)
        {
            return new \App\Service\MapService;
        });


    }

}