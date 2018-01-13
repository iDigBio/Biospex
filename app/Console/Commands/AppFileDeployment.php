<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class AppFileDeployment extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handles moving, renaming, and replacing files needed per environment settings';

    /**
     * @var string
     */
    private $resPath;

    /**
     * @var string
     */
    private $appPath;

    /**
     * @var string
     */
    private $supPath;

    /**
     * @var Collection
     */
    private $apps;

    /**
     * @var Collection
     */
    private $configs;

    /**
     * @var array
     */
    private $lookUp;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->resPath = base_path('resources');
        $this->appPath = storage_path('app/apps');
        $this->supPath = storage_path('app/supervisord');
        $this->setAppsConfigs();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // copy needed files to locations
        $appFiles = \File::files($this->resPath . '/apps');
        collect($appFiles)->reject(function($file){
            return \File::name($file) === 'laravel-echo-server.json' && \App::environment() === 'dev';
        })->each(function ($file) {
            $target = $this->appPath . '/' . \File::name($file);
            $this->copyFile($file, $target);
            $this->searchAndReplaceApps($target);
        });

        $supFiles = \File::files($this->resPath . '/supervisord');
        collect($supFiles)->reject(function($file){
            return \File::name($file) === 'echoserver.conf' && \App::environment() === 'dev';
        })->each(function ($file) {
            $target = $this->supPath . '/' . \File::name($file);
            $this->copyFile($file, $target);
            $this->searchAndReplaceConfigs($target);
        });
    }

    private function copyFile($source, $target)
    {
        \File::copy($source, $target);
    }

    /**
     * Search and replace strings for apps
     * @param $file
     */
    private function searchAndReplaceApps($file)
    {
        $this->apps->each(function ($search) use ($file) {
            $replace = $search === 'MAP_PRIVATE_KEY' ? json_encode(env($search)) : env($search);
            exec("sed -i 's*$search*$replace*g' " . $file);
        });
    }

    /**
     * Search and replace strings for supervisord configs
     * @param $file
     */
    private function searchAndReplaceConfigs($file)
    {
        $this->configs->each(function ($search) use ($file) {
            $replace = isset($this->lookUp[$search]) ? $this->lookUp[$search] : env($search);
            exec("sed -i 's*$search*$replace*g' " . $file);
        });
    }

    /**
     * Set search and replace arrays.
     */
    private function setAppsConfigs()
    {
        $this->apps = collect([
            'APP_URL',
            'APP_USER',

            'APP_ECHO_ID',
            'APP_ECHO_KEY',
            'APP_ECHO_SSL_CRT',
            'APP_ECHO_SSL_KEY',

            'API_URL',
            'API_VERSION',
            'API_CLIENT_ID',
            'API_CLIENT_SECRET',

            'MAP_PROJECT_ID',
            'MAP_PRIVATE_KEY_ID',
            'MAP_PRIVATE_KEY',
            'MAP_CLIENT_EMAIL',
            'MAP_CLIENT_ID',
            'MAP_CLIENT_CERT_URL'
        ]);

        $this->configs = collect([
            'APP_ENV',
            'APPS_PATH',
            'APP_CURRENT_PATH',
            'APP_USER',

            'QUEUE_CHART_TUBE',
            'QUEUE_CLASSIFICATION_TUBE',
            'QUEUE_DEFAULT_TUBE',
            'QUEUE_EVENT_TUBE',
            'QUEUE_IMPORT_TUBE',
            'QUEUE_EXPORT_TUBE',
            'QUEUE_STAT_TUBE',
            'QUEUE_WORKFLOW_TUBE',
            'QUEUE_OCR_TUBE',
            'QUEUE_NFN_PUSHER'
        ]);

        $this->lookUp = [
            'APPS_PATH' => env('APP_CURRENT_PATH') . '/storage/app/apps'
        ];
    }
}
