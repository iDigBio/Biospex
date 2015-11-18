<?php

use Illuminate\Console\Command;

class TestAppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $name = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';
    /**
     * @var \Biospex\Repo\Header\HeaderInterface
     */
    private $headerInterface;

    public function __construct(\Biospex\Repo\Header\HeaderInterface $headerInterface)
    {
        parent::__construct();


        $this->headerInterface = $headerInterface;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $results = $this->headerInterface->all();
        foreach($results as $result) {
            $header = [];
            $header['image'] = array_unique($result->header['image']);
            $header['occurrence'] = array_unique($result->header['occurrence']);
            $result->header = $header;
            $result->save();
        }

        return;
    }
}
