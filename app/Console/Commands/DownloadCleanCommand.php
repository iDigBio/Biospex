<?php namespace Biospex\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repositories\Contracts\Download;
use Biospex\Services\Report\Report;

class DownloadCleanCommand extends Command
{
    public $filesystem;
    public $download;
    public $report;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'download:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Remove expired download files.";

    /**
     * Directory where nfn downloads are stored.
     *
     * @var string
     */
    protected $nfnExportDir;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param DownloadInterface $download
     * @param Report $report
     */
    public function __construct(
        Filesystem $filesystem,
        Download $download,
        Report $report
    ) {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->download = $download;
        $this->report = $report;

        $this->nfnExportDir = Config::get('config.nfnExportDir');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->report->setDebug($this->argument('debug'));

        $downloads = $this->download->getExpired();

        foreach ($downloads as $download) {
            try {
                $file = $this->nfnExportDir . "/" . $download->file;
                if ($this->filesystem->isFile($file)) {
                    $this->filesystem->delete($file);
                }

                $this->download->destroy($download->id);
            } catch (Exception $e) {
                $this->report->addError("Unable to process download id: {$download->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
                $this->report->reportSimpleError();
                continue;
            }
        }

        return;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['debug', InputArgument::OPTIONAL, 'Debug option. Default false.', false],
        ];
    }
}
