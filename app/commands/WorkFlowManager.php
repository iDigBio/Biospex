<?php
/**
 * Workflow.php
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
use Illuminate\Support\Contracts\MessageProviderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Biospex\Repo\Expedition\ExpeditionInterface;

class WorkFlowManager extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Workflow manager";

    /**
     * @var Illuminate\Support\Contracts\MessageProviderInterface
     */
    protected $messages;

    /**
     * Class constructor
     */
    public function __construct(
        Filesystem $filesystem,
        MessageProviderInterface $messages,
        ExpeditionInterface $expedition
    )
    {
        $this->filesystem = $filesystem;
        $this->messages = $messages;
        $this->expedition = $expedition;

        parent::__construct();
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

    }
}

