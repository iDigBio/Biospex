<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use App\Repositories\Contracts\Download;
use App\Services\Report\Report;

class DownloadCleanCommand extends Command
{
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
     * @param Download $download
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

        $this->nfnExportDir = \Config::get('config.nfn_export_dir');
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

        if (! \File::isDirectory($this->nfnExportDir)) {
            \File::makeDirectory($this->nfnExportDir);
        }

        foreach ($downloads as $download) {
            try {
                $file = $this->nfnExportDir . "/" . $download->file;
                if ($this->filesystem->isFile($file)) {
                    $this->filesystem->delete($file);
                }

                $this->download->destroy($download->id);
            } catch (\Exception $e) {
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
        return array(
            array('debug', InputArgument::OPTIONAL, 'Debug option. Default false.', false),
        );
    }
}
