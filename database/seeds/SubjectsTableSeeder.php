<?php

use Illuminate\Database\Seeder;
use Biospex\Services\Process\DarwinCore;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Constructor.
     *
     * @param DarwinCore $process
     */
    public function __construct(DarwinCore $process)
    {
        $this->process = $process;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $dir = base_path('database/seeds/data');
        try {
            $this->process->process(1, $dir);
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }
}
