<?php  namespace Biospex\Services\Import;
/**
 * DarwinCoreUrlImport.php
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

use Illuminate\Support\Facades\Queue;

class DarwinCoreUrlImport extends ImportServiceAbstract{

    /**
     * Upload subjects for project.
     *
     * @param $id
     * @return string|void
     */
    public function import($id)
    {
        $validator = \Validator::make(
            ['data-url' => \Input::input('data-url')],
            ['data-url' => 'required|url']
        );

        if ($validator->fails())
            return $validator;

        $this->setQueue('config.beanstalkd.import');

        $data = [
            'id' => $id,
            'user_id' => \Input::input('user_id'),
            'url' => \Input::input('data-url'),
            'class' => 'DarwinCoreUrlImportQueue'
        ];

        \Queue::push('Biospex\Services\Queue\QueueFactory', $data, $this->queue);

        return;
    }
}
