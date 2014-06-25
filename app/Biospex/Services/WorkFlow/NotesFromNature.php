<?php namespace Biospex\Services\WorkFlow;
/**
 * NotesFromNature.php
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

class NotesFromNature extends WorkFlow
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Set state for expedition
     *
     * @return array
     */
    protected function setState()
    {
        return $this->state = array(
            'export'    => 'export',
            'status'     => 'getStatus',
            'results'    => 'getResults',
            'completed'  => 'completed',
            'analyze'    => 'analyze',

        );
    }

    public function processState()
    {

    }

    /**
     * Export the expedition
     *
     * @param $expeditionId
     * @return string
     */
    public function export($expeditionId)
    {

    }

    /**
     * Get current status
     */
    public function getStatus()
    {
        return;
    }

    /**
     * Get results
     */
    public function getResults()
    {
        return;
    }
}