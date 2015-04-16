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
        $files = $this->filesystem->files($this->tmpFileDir);

        foreach ($files as $file)
        {
            try
            {
                $this->image->imageMagick($file);
            }
            catch (\Exception $e)
            {
                $fileName = $this->image->getFileName();
                echo "Caught Exception: $fileName " . $e->getMessage() . "/n";

                continue;
            }

        }

    }
}
