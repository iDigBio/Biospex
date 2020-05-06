<?php

namespace App\Console\Commands;

use App\Jobs\BingoJob;
use App\Repositories\Interfaces\BingoMap;
use Illuminate\Console\Command;

class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Repositories\Interfaces\BingoMap
     */
    private $bingoMapContract;

    /**
     * AppCommand constructor.
     */
    public function __construct(BingoMap $bingoMapContract) {
        parent::__construct();

        $this->bingoMapContract = $bingoMapContract;
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        dd(long2ip( mt_rand(0, 65537) * mt_rand(0, 65535) ));
        BingoJob::dispatch(1);
        return;

        $locations = $this->bingoMapContract->findBy('bingo_id', 1);
        dd($locations);
        $data = $locations->mapWithKeys(function($location) {
            return [$location->id => view('common.scoreboard-content', ['event' => $location])->render()];
        });
    }
}