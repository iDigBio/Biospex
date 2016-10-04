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
    public function __construct()
    {
        parent::__construct();
        $this->darwinCore = $darwinCore;
    }

    public function fire()
    {

    }

}
