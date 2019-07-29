<?php
/**
 * Company: InfyOm Technologies, Copyright 2019, All Rights Reserved.
 * Author: Vishal Ribdiya
 * Email: vishal.ribdiya@infyom.com
 * Date: 29-07-2019
 * Time: 11:55 AM.
 */

namespace Tests\Providers;

use Illuminate\Support\ServiceProvider;
/**
 * Class RouteServiceProvider
 * @package Tests\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
    }
}