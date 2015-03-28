<?php

use Illuminate\Console\Command;
use Biospex\Services\Image\Image;
use Illuminate\Filesystem\Filesystem;
use Biospex\Services\Curl\Curl;

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
     //   convert /data/web/staging.biospex.org/app/storage/data/4-17f9a20a23/5512ba2500cf791f438b4ffe.jpg  -resize 64x64  /data/web/staging.biospex.org/app/storage/testsm.jpg
        $files = $this->filesystem->files("/data/web/staging.biospex.org/app/storage/data/4-aa4ef932d6");

        foreach ($files as $file)
        {
            $this->image->imageMagick($file);
            $origWidth = $this->image->getImageWidth();
            $origHeight = $this->image->getImageHeight();
            $baseName = $this->image->getBaseName();
            $fileName = $this->image->getFileName();
            $extension = $this->image->getExtension();

            echo $baseName . PHP_EOL;
            return;
        }

        return;
    }

    public function save($image, $info)
    {
        $info = $this->image->getImageInfoFromString($image);
        print_r($info);

        $this->filesystem->put(storage_path() . "/test.jpg", $image);
        $this->image->imageMagick(storage_path() . "/data/4-17f9a20a23/5512ba2500cf791f438b4ffe.jpg");
        $this->image->resize(storage_path() . "/testsm.jpg", 50, 50);
        $this->image->destroy();

        return;
    }
}