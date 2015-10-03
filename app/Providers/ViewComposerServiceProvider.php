<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ViewComposerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->make('view')->composer('front.layouts.default', 'App\Http\ViewComposers\TopMenuComposer');
    }
}
