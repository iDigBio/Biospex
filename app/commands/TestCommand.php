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
       $url = "http://herbarium.bio.fsu.edu/showimage.php?Image=images/herbarium/jpegs/000016295.jpg";

        $rc = new Curl([$this, "save"]);
        $rc->options = [CURLOPT_RETURNTRANSFER => 1, CURLOPT_FOLLOWLOCATION => 1, CURLINFO_HEADER_OUT => 1];
        $rc->window_size = 1;
        $rc->get($url);
        $rc->execute();

        return;
    }

    public function save($image, $info)
    {
        $info = $this->image->getImageInfoFromString($image);
        print_r($info);

        $this->filesystem->put(storage_path() . "/test.jpg", $image);
        $this->image->imageMagick(storage_path() . "/test.jpg");
        $this->image->resize(storage_path() . "/testsm.jpg", 50, 0);
        $this->image->destroy();

        return;
    }
}