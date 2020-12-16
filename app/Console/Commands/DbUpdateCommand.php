<?php

namespace App\Console\Commands;

use App\Jobs\RapidVersionJob;
use App\Models\User;
use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidUpdateModelService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class DbUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * @var \App\Services\Model\RapidHeaderModelService
     */
    private $rapidHeaderModelService;

    /**
     * @var \App\Services\Model\RapidUpdateModelService
     */
    private $rapidUpdateModelService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\Model\RapidHeaderModelService $rapidHeaderModelService
     * @param \App\Services\Model\RapidUpdateModelService $rapidUpdateModelService
     */
    public function __construct(
        RapidHeaderModelService $rapidHeaderModelService,
        RapidUpdateModelService $rapidUpdateModelService
    ) {
        parent::__construct();
        $this->rapidHeaderModelService = $rapidHeaderModelService;
        $this->rapidUpdateModelService = $rapidUpdateModelService;
    }

    /**
     * Execute the console command.
     *
     * @throws \Throwable
     */
    public function handle()
    {
        $user = User::find(1);

        \DB::transaction(function () use($user) {
            $header = json_decode(\Storage::get(config('config.rapid_import_dir') . '/header.json'), true);

            $rapidHeaderRecord = $this->rapidHeaderModelService->create(['data' => $header]);

            $this->rapidUpdateModelService->create([
                'header_id' => $rapidHeaderRecord->id,
                'user_id' => $user->id,
                'file_orig_name' => 'rapid-joined-records_country-cleanup_2020-09-23.csv',
                'file_name' => 'rapid-joined-records_country-cleanup_2020-09-23.csv',
                'fields_updated' => $header
            ]);
        });
    }
}
