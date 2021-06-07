<?php

namespace App\Console\Commands;

use App\Jobs\RapidExportDwcJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Class RapidDwcExportCommand
 *
 * @package App\Console\Commands
 */
class RapidDwcExportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dwc:export {keys?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Produces the dwc exports for rapid. Enter key id or blank for all exports.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $keys = empty($this->argument('keys')) ? $this->providers() : $this->argument('keys');

        foreach ($keys as $key) {
            RapidExportDwcJob::dispatch($key);
        }
    }

    /**
     * Return array of provider keys.
     *
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    private function providers()
    {
        return json_decode(File::get(config('config.dwc_fields_file')), true);
    }
}
