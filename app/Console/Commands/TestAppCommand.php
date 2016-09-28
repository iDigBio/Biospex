<?php

namespace App\Console\Commands;

use App\Exceptions\BiospexException;
use App\Exceptions\FileUnzipException;
use App\Services\Process\DarwinCore;
use App\Services\Process\MetaFile;
use DOMDocument;
use DOMXPath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;


class TestAppCommand extends Command
{

    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var DarwinCore
     */
    private $darwinCore;

    /**
     * TestAppCommand constructor.
     */
    public function __construct(DarwinCore $darwinCore)
    {
        parent::__construct();
        $this->darwinCore = $darwinCore;
    }

    public function fire()
    {

        //$file = storage_path('austin.xml');
        //$file = storage_path('idigbio.xml');
        //$file = storage_path('sedges.xml');
        $file = storage_path('scratch/34-04bbd7e9a2820392bc0352148fe02dc0');

        $this->darwinCore->process(21, $file);
    }

}
