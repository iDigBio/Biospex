<?php

namespace App\Console\Commands;

use App\Services\Actor\ActorImageService;
use App\Services\Image\ImagickService;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Imagick;

class TestAppCommand extends Command
{

    use DispatchesJobs;

    public $projectId;
    public $expeditionId;
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var ActorImageService
     */
    private $service;

    /**
     * Create a new job instance.
     * @param ImagickService $service
     */
    public function __construct(ImagickService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $files = \File::files('/home/vagrant/sites/biospex/production/storage/scratch/2-c8345a7a-a40f-4368-8baa-170ea623b80f');
        collect($files)->each(function ($file) {
            $fileName = \File::name($file);
            $destination = '/home/vagrant/sites/biospex/production/storage/scratch/2-c8345a7a-a40f-4368-8baa-170ea623b80f/tmp/';
            echo $file . PHP_EOL;

            $im = new Imagick((string) $file);
            $im->setImageFormat('jpg');
            $im->setOption('jpeg:extent', '600kb');
            $im->stripImage();
            $im->writeImage($destination . $fileName . '.jpg');

            //return;
            //return $this->service->writeImagickImageToFile($file, $fileName);
        });

        /*
        $file = '/home/vagrant/sites/biospex/production/storage/scratch/2-c8345a7a-a40f-4368-8baa-170ea623b80f/56042c9800cf79015b8b4569.jpeg';
        $this->service->createImagickObject($file);

        $destination = storage_path('/scratch/2-c8345a7a-a40f-4368-8baa-170ea623b80f/tmp/test.jpg');
        if ( ! $this->service->writeImagickImageToFile($destination))
        {
            $this->service->clearImagickObject();

            echo 'no' . PHP_EOL;
            exit;
        }

        $this->service->clearImagickObject();

        echo 'yes' . PHP_EOL;
        */
    }
}
