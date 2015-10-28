<?php

use Illuminate\Console\Command;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Header\HeaderInterface;
use Biospex\Services\Actor\NotesFromNature\NotesFromNature;

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
     * @var ExpeditionInterface
     */
    private $expeditionInterface;
    /**
     * @var HeaderInterface
     */
    private $headerInterface;
    /**
     * @var NotesFromNature
     */
    private $fromNature;

    public function __construct(NotesFromNature $fromNature, ExpeditionInterface $expeditionInterface, HeaderInterface $headerInterface)
    {
        parent::__construct();

        $this->expeditionInterface = $expeditionInterface;
        $this->headerInterface = $headerInterface;
        $this->fromNature = $fromNature;
    }

    /**
     * Fire queue.
     */
    public function fire()
    {
        $this->fromNature->process(['test']);

        return;

        $this->expeditionInterface->setPass(true);
        $expedition = $this->expeditionInterface->findWith(1, ['actors']);

        $actor = $expedition->actors[0];

        try {
            $factoryClass = 'Biospex\Services\Actor\\' . $actor->class . '\\' . $actor->class . 'Factory';
            $factory = App::make($factoryClass);
            $class = $factory->create($actor);
            if ($class) {
                $class->process($actor);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
            echo $e->getFile() . PHP_EOL;
            echo $e->getLine() . PHP_EOL;
        }

        echo "Delete job" . PHP_EOL;
    }
}
