<?php

use Illuminate\Console\Command;
use Biospex\Services\Image\Image;
use Illuminate\Filesystem\Filesystem;
use Biospex\Services\Actor\NotesFromNature;

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
    public function __construct(Image $image, Filesystem $filesystem, NotesFromNature $notes)
    {
        parent::__construct();
        $this->image = $image;
        $this->filesystem = $filesystem;
        $this->notes = $notes;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
     //   convert /data/web/staging.biospex.org/app/storage/data/4-17f9a20a23/5512ba2500cf791f438b4ffe.jpg  -resize 64x64  /data/web/staging.biospex.org/app/storage/testsm.jpg
        $this->notes->setTitle('4-03f4526548');
        $this->notes->setPaths();
        $this->notes->convert();
        //$this->notes->buildDetails();
        return;
    }
}
