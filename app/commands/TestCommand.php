<?php

use Illuminate\Console\Command;
use Biospex\Services\Image\Image;
use Illuminate\Filesystem\Filesystem;

class TestCommand extends Command {

    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Constructor
     */
    public function __construct(Image $image, Filesystem $filesystem)
    {
        parent::__construct();
        $this->image = $image;
        $this->filesystem = $filesystem;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $time_start = microtime(true);

        $this->setPaths();
        echo "Paths set." . PHP_EOL;

        $files = $this->filesystem->files($this->tmpFileDir);
        echo "Retrieved files." . PHP_EOL;

        foreach ($files as $file)
        {
            $this->image->setImagePathInfo($file);
            echo "Set pathinfo on file." . PHP_EOL;

            if ($this->image->getMimeType() === false)
                continue;

            $fileName = $this->image->getFileName();
            echo "Get File name." . PHP_EOL;

            $extension = $this->image->getFileExtension();
            echo "Get Extension." . PHP_EOL;

            try
            {

                echo "Reading file" . PHP_EOL;
                $this->image->readImageMagickFile($file);
            }
            catch (\Exception $e)
            {
                echo "Could not read file: " . $e->getMessage();
                die();
            }

            $tmpLrgFilePath = "{$this->wrkPath}/$fileName.large.$extension";
            $tmpSmFilePath = "{$this->wrkPath}/$fileName.small.$extension";

            echo "Resizing Images" . PHP_EOL;
            $this->image->resizeMagick($tmpLrgFilePath, $this->largeWidth, 0);
            $this->image->resizeMagick($tmpSmFilePath, $this->smallWidth, 0);

            $this->image->destroyImageMagick();

            $lrgFilePath = "{$this->lrgFilePath}/$fileName.large.$extension";
            $smFilePath = "{$this->smFilePath}/$fileName.small.$extension";

            $this->filesystem->move($tmpLrgFilePath, $lrgFilePath);
            $this->filesystem->move($tmpSmFilePath, $smFilePath);

            $this->imgCount++;
        }

        $time_end = microtime(true);

        //dividing with 60 will give the execution time in minutes other wise seconds
        $execution_time = ($time_end - $time_start)/60;

        //execution time of the script
        echo "Total Execution Time: ". $execution_time . " Mins" . PHP_EOL;

    }

    public function setPaths()
    {
        $this->wrkPath = storage_path('working');
        $this->image->createDir($this->wrkPath);

        $this->tmpFileDir = storage_path('data/4-e33c305f9cf2e45dcf300c46faa8a87f');
        $this->image->createDir($this->tmpFileDir);

        $this->lrgFilePath = $this->tmpFileDir . '/large';
        $this->image->createDir($this->lrgFilePath);

        $this->smFilePath = $this->tmpFileDir . '/small';
        $this->image->createDir($this->smFilePath);
    }
}
