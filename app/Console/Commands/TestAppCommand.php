<?php

namespace Biospex\Console\Commands;

use Illuminate\Console\Command;
use Biospex\Models\ExpeditionStat;
use Biospex\Repositories\Contracts\Expedition;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Factory as Validation;

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
     * @var Validation
     */
    private $factory;

    /**
     * Constructor
     */
    public function __construct(Validation $factory)
    {
        parent::__construct();
        $this->factory = $factory;
    }

    /**
     * Fire queue.
     *
     * @param Mailer $mailer
     * @param Config $config
     */
    public function fire()
    {

        $verifier = App::make('validation.presence');

        $verifier->setConnection('mongodb');

        $rules = ['id' => 'unique_with:transcriptions,id'];
        $values = ['id' => '562d9e34f3cdc400ac00f077'];

        $validator = Validator::make($values, $rules);

        $validator->setPresenceVerifier($verifier);

        dd($validator->fails());

        /*
        $rules = ['project_id' => 'unique_with:subjects,id'];
        $values = ['project_id' => 1, 'id' => '58697'];

        $validator = $this->factory->make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');
        */

        /*
        $rules = ['id' => 'unique_with:transcriptions,id'];
        $values = ['id' => '562d9e34f3cdc400ac00f077'];
        $validator = $this->factory->make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');
        */

        dd($validator->fails());

        //$this->runStatUpdate();

        return;
    }

    public function runStatUpdate(Expedition $expedition)
    {
        $expeditions = $expedition->all();
        foreach ($expeditions as $expedition) {
            $subjects = $expedition->subjects()->get();
            $count = count($subjects);

            $expeditionStat = new ExpeditionStat();
            $stat = $expeditionStat->firstOrCreate(['expedition_id' => $expedition->id]);
            $stat->subject_count = $count;
            $stat->transcriptions_total = transcriptions_total($count);
            $stat->transcriptions_completed = transcriptions_completed($expedition->id);
            $stat->percent_completed = transcriptions_percent_completed($stat->transcriptions_total, $stat->transcriptions_completed);
            $stat->save();
        }

        return;
    }
}
