<?php namespace App\Providers;

use DirectoryIterator;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Local;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * This namespace is applied to the controller routes in your routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        $router->bind('locale', Local::setLocale());
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router $router
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router)
        {

            $router->group(
                [
                    'domain'     => env('APP_DOMAIN'),
                    'prefix'     => 'admin',
                    'middleware' => ['web', 'auth', 'admin'],
                    'namespace'  => 'Backend'
                ],
                function () use ($router)
                {
                    $dir = app_path('Http/Routes/Backend');
                    $this->require_files($dir, $router);
                });

            $router->group(
                [
                    'domain'     => env('APP_DOMAIN'),
                    'prefix'     => Local::setLocale(),
                    'middleware' => ['web'],
                    'namespace'  => 'Frontend',
                    'before'     => 'LocalRedirectFilter'
                ],
                function () use ($router)
                {
                    $router->get('projects/{projects}/expeditions/{expeditions}/downloads/{downloads}', [
                        'uses' => 'DownloadsController@show',
                        'as'   => 'projects.expeditions.downloads.get.show',
                    ]);

                    $dir = app_path('Http/Routes/Frontend/Web');
                    $this->require_files($dir, $router);

                });

            $router->group(
                [
                    'domain'     => env('APP_DOMAIN'),
                    'prefix'     => Local::setLocale(),
                    'middleware' => ['web', 'auth'],
                    'namespace'  => 'Frontend',
                    'before'     => 'LocalRedirectFilter'
                ],
                function () use ($router)
                {
                    $router->put('password/{id}/pass', [
                        'uses' => 'PasswordController@pass',
                        'as'   => 'password.put.pass'
                    ]);

                    $dir = app_path('Http/Routes/Frontend/WebAuth');
                    $this->require_files($dir, $router);
                }
            );
        });
    }

    /**
     * Load required files.
     * 
     * @param $dir
     * @param $router
     */
    protected function require_files($dir, $router)
    {
        foreach (new DirectoryIterator($dir) as $file)
        {
            if (!$file->isDot() && !$file->isDir())
            {
                require $dir . '/' . $file->getFilename();
            }
        }
    }
}
