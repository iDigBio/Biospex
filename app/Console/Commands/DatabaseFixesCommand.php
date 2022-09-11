<?php

namespace App\Console\Commands;

use App\Services\FixFields\FixFieldsStepEight;
use App\Services\FixFields\FixFieldsStepFive;
use App\Services\FixFields\FixFieldsStepFour;
use App\Services\FixFields\FixFieldsStepNine;
use App\Services\FixFields\FixFieldsStepOne;
use App\Services\FixFields\FixFieldsStepSeven;
use App\Services\FixFields\FixFieldsStepSix;
use App\Services\FixFields\FixFieldsStepThree;
use App\Services\FixFields\FixFieldsStepTwo;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;

class DatabaseFixesCommand extends Command
{
    use DispatchesJobs;

    /**
     * The console command name.
     */
    protected $signature = 'data:fix {step}';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * @var \App\Services\FixFields\FixFieldsStepOne
     */
    private FixFieldsStepOne $fixFieldsStepOne;

    /**
     * @var \App\Services\FixFields\FixFieldsStepTwo
     */
    private FixFieldsStepTwo $fixFieldsStepTwo;

    /**
     * @var \App\Services\FixFields\FixFieldsStepThree
     */
    private FixFieldsStepThree $fixFieldsStepThree;

    /**
     * @var \App\Services\FixFields\FixFieldsStepFour
     */
    private FixFieldsStepFour $fixFieldsStepFour;

    /**
     * @var \App\Services\FixFields\FixFieldsStepFive
     */
    private FixFieldsStepFive $fixFieldsStepFive;

    /**
     * @var \App\Services\FixFields\FixFieldsStepSix
     */
    private FixFieldsStepSix $fixFieldsStepSix;

    /**
     * @var \App\Services\FixFields\FixFieldsStepSeven
     */
    private FixFieldsStepSeven $fixFieldsStepSeven;

    /**
     * @var \App\Services\FixFields\FixFieldsStepEight
     */
    private FixFieldsStepEight $fixFieldsStepEight;

    /**
     * @var \App\Services\FixFields\FixFieldsStepNine
     */
    private FixFieldsStepNine $fixFieldsStepNine;

    /**
     * UpdateQueries constructor.
     *
     * @param \App\Services\FixFields\FixFieldsStepOne $fixFieldsStepOne
     * @param \App\Services\FixFields\FixFieldsStepTwo $fixFieldsStepTwo
     * @param \App\Services\FixFields\FixFieldsStepThree $fixFieldsStepThree
     * @param \App\Services\FixFields\FixFieldsStepFour $fixFieldsStepFour
     * @param \App\Services\FixFields\FixFieldsStepFive $fixFieldsStepFive
     * @param \App\Services\FixFields\FixFieldsStepSix $fixFieldsStepSix
     * @param \App\Services\FixFields\FixFieldsStepSeven $fixFieldsStepSeven
     * @param \App\Services\FixFields\FixFieldsStepEight $fixFieldsStepEight
     * @param \App\Services\FixFields\FixFieldsStepNine $fixFieldsStepNine
     */
    public function __construct(
        FixFieldsStepOne $fixFieldsStepOne,
        FixFieldsStepTwo $fixFieldsStepTwo,
        FixFieldsStepThree $fixFieldsStepThree,
        FixFieldsStepFour $fixFieldsStepFour,
        FixFieldsStepFive $fixFieldsStepFive,
        FixFieldsStepSix $fixFieldsStepSix,
        FixFieldsStepSeven $fixFieldsStepSeven,
        FixFieldsStepEight $fixFieldsStepEight,
        FixFieldsStepNine $fixFieldsStepNine
    ) {
        parent::__construct();

        $this->fixFieldsStepOne = $fixFieldsStepOne;
        $this->fixFieldsStepTwo = $fixFieldsStepTwo;
        $this->fixFieldsStepThree = $fixFieldsStepThree;
        $this->fixFieldsStepFour = $fixFieldsStepFour;
        $this->fixFieldsStepFive = $fixFieldsStepFive;
        $this->fixFieldsStepSix = $fixFieldsStepSix;
        $this->fixFieldsStepSeven = $fixFieldsStepSeven;
        $this->fixFieldsStepEight = $fixFieldsStepEight;
        $this->fixFieldsStepNine = $fixFieldsStepNine;
    }

    /**
     * Fire command
     */
    public function handle()
    {
        // Step db: run db queries
        if ($this->argument('step') === "db") {
            \DB::statement("ALTER TABLE properties CHANGE short short VARCHAR(255) BINARY NOT NULL;");
            \DB::statement("ALTER TABLE `properties` DROP INDEX `properties_qualified_unique`");
            \DB::statement("ALTER TABLE `properties`DROP `qualified`");
            \DB::statement("ALTER TABLE `properties`DROP `namespace`");

            return;
        }

        // Step 1: generate properties with counts
        if ($this->argument('step') === "1") {
            $this->fixFieldsStepOne->start();

            return;
        }

        // Step 2: Removed unused fields from header array in headers table
        if ($this->argument('step') === "2") {
            $this->fixFieldsStepTwo->start();

            return;
        }

        // Step 3: Remove property fields that have no values from properties table
        if ($this->argument('step') === "3") {
            $this->fixFieldsStepThree->start();

            return;
        }

        // Step 4: remove empty fields
        if ($this->argument('step') === "4") {
            $this->fixFieldsStepFour->start();

            return;
        }

        // Step 5: Check if field & alternate fields exist together in same record
        if ($this->argument('step') === "5") {
            $this->fixFieldsStepFive->start();

            return;
        }

        // Step 6: Fix dup image subjects
        if ($this->argument('step') === "6") {
            $this->fixFieldsStepSix->start();

            return;
        }

        // Step 7: Fix dup occurrence subjects
        if ($this->argument('step') === "7") {
            $this->fixFieldsStepSeven->start();

            return;
        }

        // Step 8: Fix dup mixed subjects
        if ($this->argument('step') === "8") {
            $this->fixFieldsStepEight->start();

            return;
        }

        // Step 9: Fix dup mixed occurrence subjects
        if ($this->argument('step') === "9") {
            $this->fixFieldsStepNine->start();
        }
    }
}
