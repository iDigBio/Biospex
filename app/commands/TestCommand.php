<?php

use Illuminate\Console\Command;
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
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, str_replace(" ", "%20", "http://herbarium.bio.fsu.edu/showimage.php?Image=images/herbarium/jpegs/000016295.jpg"));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($ch);
        curl_close($ch);

        $ext = $this->image->getExtension($image, true);

        $path = public_path() . '/test.' . $ext;
        $test = public_path() . '/resized.' . $ext;

        $this->saveFile($path, $image);

        shell_exec("gm convert -size 120x120 " . $path . " -resize 580X580 " . $test);

        return;
    }

    public function getExtension ($file, $string = false)
    {
        $info = ! $string ? getimagesize($file) : getimagesizefromstring($file);

        return isset($this->imageTypeExtension[$info['mime']]) ?
            $this->imageTypeExtension[$info['mime']] : false;
    }
}