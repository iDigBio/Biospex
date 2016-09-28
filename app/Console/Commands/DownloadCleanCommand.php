<?php 

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use App\Repositories\Contracts\Download;
use App\Exceptions\Handler;

class DownloadCleanCommand extends Command
{
    public $filesystem;
    public $download;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'download:clean';

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
     * @var Handler
     */
    private $handler;

    /**
     * DownloadCleanCommand constructor.
     *
     * @param Filesystem $filesystem
     * @param Download $download
     * @param Handler $handler
     */
    public function __construct(
        Filesystem $filesystem,
        Download $download,
        Handler $handler
    ) {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->download = $download;
        $this->handler = $handler;

        $this->nfnExportDir = Config::get('config.nfnExportDir');
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $downloads = $this->download->skipCache()->where([['count', '>', 5]])->orWhere([['created_at', '<', Carbon::now()->subDays(7)]])->get();

        foreach ($downloads as $download) {
            try {
                $file = $this->nfnExportDir . '/' . $download->file;
                if ($this->filesystem->isFile($file)) {
                    $this->filesystem->delete($file);
                }

                $this->download->delete($download->id);
            } catch (BiospexException $e) {
                $this->handler->report($e);

                continue;
            }
        }

    }
}
