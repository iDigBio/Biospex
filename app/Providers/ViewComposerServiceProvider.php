<?php namespace Biospex\Providers;
/**
 * Created by PhpStorm.
 * User: Robert
 * Date: 4/12/2015
 * Time: 2:54 PM
 */

use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider {

    public function register()
    {
        $this->app->make('view')->composer('layouts.default', 'Biospex\Http\ViewComposers\TopMenuComposer');
    }

}