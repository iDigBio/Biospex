<?php

use Illuminate\Database\Seeder;
use Biospex\Services\Process\DarwinCoreImport;
use Biospex\Services\Process\DarwinCore;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Constructor
     *
     * @param DarwinCore $process
     */
    public function __construct(
        DarwinCoreImport $process
    ) {
        $this->process = $process;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $dir = 'app/database/seeds/data';
        try {
            $this->process->process(1, $dir, true);
        } catch (Exception $e) {
            die($e->getMessage() . $e->getTraceAsString());
        }
    }
}
