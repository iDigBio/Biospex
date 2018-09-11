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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->resPath = base_path('resources');
        $this->appPath = base_path();
        $this->supPath = storage_path('app/supervisord');
        $this->setAppsConfigs();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // copy needed files to locations
        $appFiles = \File::files($this->resPath.'/apps');
        collect($appFiles)->reject(function ($file) {
            return \File::name($file) === 'laravel-echo-server.json' && \App::environment() === 'dev';
        })->each(function ($file) {
            $target = $this->appPath.'/'.\File::name($file);
            $this->copyFile($file, $target);
            $this->searchAndReplace($target);
        });

        $supFiles = \File::files($this->resPath.'/supervisord');
        collect($supFiles)->reject(function ($file) {
            return \File::name($file) === 'echoserver.conf' && \App::environment() === 'dev';
        })->each(function ($file) {
            $target = $this->supPath.'/'.\File::name($file);
            $this->copyFile($file, $target);
            $this->searchAndReplace($target);
        });
    }

    private function copyFile($source, $target)
    {
        \File::copy($source, $target);
    }

    /**
     * Search and replace strings for apps
     *
     * @param $file
     */
    private function searchAndReplace($file)
    {
        $this->apps->each(function ($search) use ($file) {
            if ($search === 'APP_URL' || $search === 'APP_ENV') {
                $replace = config(str_replace('_', '.', strtolower($search)));
                $this->command($search, $replace, $file);

                return;
            }

            if (strpos($search, 'QUEUE_') === 0) {
                $replace = strtolower(str_replace('QUEUE_', '', $search));
                $replace = config('config.beanstalkd.'.$replace);
                $this->command($search, $replace, $file);

                return;
            }

            if ($search === 'MAP_PRIVATE_KEY') {
                $replace = json_encode(base64_decode(config('config.'.strtolower($search))));
                $this->command($search, $replace, $file);

                return;
            }

            $replace = config('config.'.strtolower($search));
            $this->command($search, $replace, $file);

            return;
        });
    }

    private function command($search, $replace, $file)
    {
        exec("sed -i 's*$search*$replace*g' $file");

        return;
    }

    /**
     * Set search and replace arrays.
     */
    private function setAppsConfigs()
    {
        $this->apps = collect([
            'APP_URL',
            'APP_ENV',

            'SERVER_USER',
            'CURRENT_PATH',

            'ECHO_ID',
            'ECHO_KEY',
            'ECHO_SSL_CRT',
            'ECHO_SSL_KEY',

            'API_URL',
            'API_VERSION',
            'API_CLIENT_ID',
            'API_CLIENT_SECRET',

            'MAP_PROJECT_ID',
            'MAP_PRIVATE_KEY_ID',
            'MAP_PRIVATE_KEY',
            'MAP_CLIENT_EMAIL',
            'MAP_CLIENT_ID',
            'MAP_CLIENT_CERT_URL',

            'QUEUE_CHART_TUBE',
            'QUEUE_CLASSIFICATION_TUBE',
            'QUEUE_DEFAULT_TUBE',
            'QUEUE_EVENT_TUBE',
            'QUEUE_FUSION_TUBE',
            'QUEUE_IMPORT_TUBE',
            'QUEUE_EXPORT_TUBE',
            'QUEUE_STAT_TUBE',
            'QUEUE_WORKFLOW_TUBE',
            'QUEUE_OCR_TUBE',
            'QUEUE_PUSHER_TUBE',
        ]);
    }
}
