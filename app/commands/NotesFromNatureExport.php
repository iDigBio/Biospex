<?php
/**
 * NotesFromNatureExport.php
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
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\SubjectDoc\SubjectDocInterface;

class NotesFromNatureExport extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'nfn:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Exports expedition for Notes From Nature";

    /**
     * Directory where darwin core files are stored
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Class constructor
     */
    public function __construct(
        Filesystem $filesystem,
        ExpeditionInterface $expedition
    )
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->expedition = $expedition;
        $this->dataDir = Config::get('config.dataDir');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('expeditionId', InputArgument::REQUIRED, 'Id of expedition being exported'),
        );
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $expeditionId = $this->argument('expeditionId');
        $expedition = $this->expedition->findWith($expeditionId, ['subject.subjectDoc']);
        foreach ($expedition->subject as $subject)
        {
            if (empty($subject->subjectDoc->acbestqualityaccessuri))
                echo $subject->subjectDoc->_id . PHP_EOL;
            echo $subject->subjectDoc->acbestqualityaccessuri . PHP_EOL;
        }
        dd("Complete");
        //echo $this->expeditionId . PHP_EOL;
        //echo "Fired" . PHP_EOL;

        return;
    }
}