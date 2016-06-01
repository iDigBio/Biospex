<?php 

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\Download;
use App\Services\Report\Report;

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
    protected $signature = 'download:clean {debug?}';

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
     * DownloadCleanCommand constructor.
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

        $this->nfnExportDir = Config::get('config.nfnExportDir');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->report->setDebug($this->argument('debug'));

        $downloads = $this->download->skipCache()->where([['count', '>', 5]])->orWhere([['created_at', '<', Carbon::now()->subDays(7)]])->get();

        foreach ($downloads as $download) {
            try {
                $file = $this->nfnExportDir . "/" . $download->file;
                if ($this->filesystem->isFile($file)) {
                    $this->filesystem->delete($file);
                }

                $this->download->delete($download->id);
            } catch (Exception $e) {
                $this->report->addError("Unable to process download id: {$download->id}. " . $e->getMessage() . " " . $e->getTraceAsString());
                $this->report->reportSimpleError();
                continue;
            }
        }

    }
}
