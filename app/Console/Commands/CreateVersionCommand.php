<?php

namespace App\Console\Commands;

use App\Jobs\RapidVersionJob;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Class CreateVersionCommand
 *
 * @package App\Console\Commands
 */
class CreateVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a version export';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * mongoexport --db=rapid --collection=rapid_records --type=csv --fieldFile=/home/vagrant/sites/rapid/storage/app/exports/rapid/version/header.txt --out=/home/vagrant/sites/rapid/storage/app/exports/rapid/version/1608076095.csv
     *
     */
    public function handle()
    {
        $user = User::find(1);

        RapidVersionJob::dispatch($user, Carbon::now('UTC')->timestamp);
    }
}
