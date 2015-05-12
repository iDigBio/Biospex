<?php
/**
 * SubjectImportProcess.php.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Console\Command;
use Biospex\Repo\Import\ImportInterface;

class SubjectImportCommand extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'subjects:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to re-queue subjects after a failure.";

    /**
     * Class constructor.
     * @param ImportInterface $import
     */
    public function __construct(ImportInterface $import)
    {
        parent::__construct();

        $this->import = $import;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $imports = $this->import->findByError();

        $count = 0;
        foreach ($imports as $import)
        {
            Queue::push('Biospex\Services\Queue\SubjectsImportService', ['id' => $import->id], Config::get('config.beanstalkd.subjects-import'));
            $count++;
        }

        echo $count . " Imports added to Queue." . PHP_EOL;

        return;
    }
}
