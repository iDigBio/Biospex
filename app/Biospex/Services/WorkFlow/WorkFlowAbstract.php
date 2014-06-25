<?php namespace Biospex\Services\WorkFlow;
/**
 * WorkFlowAbstract.php
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

abstract class WorkFlow {

    /**
     * @var array
     */
    protected $state = array();

    public function __construct()
    {
        $this->setState();
    }

    abstract protected function setState();

    abstract protected function export($expeditionId);

    abstract protected function getStatus();

    abstract protected function getResults();

    /**
     * Use PHP 5.3 late static binding feature for child classes
     *
     * @return mixed
     */
    public static function factory()
    {
        $class = get_called_class();
        return new $class;
    }

    public function backGroundProcess($command, $priority = 0)
    {
        if ($priority)
            $pid = shell_exec("nohup nice -n $priority $command 2> /dev/null & echo $!");
        else
            $pid = shell_exec("nohup $command > /dev/null 2> /dev/null & echo $!");
        return($pid);
    }

    public function isProcessRunning($pid)
    {
        exec("ps $pid", $processState);
        return(count($processState) >= 2);
    }

}