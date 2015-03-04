<?php
/**
 * NavigationsTableSeeder.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
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
class NavigationsTableSeeder extends Seeder {

    protected $items;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run ()
    {
        Eloquent::unguard();

        $this->items = $this->loadData();

        foreach ($this->items as $item) {
            if (isset($item['children']))
            {
                $children = $item['children'];
                unset($item['children']);
            }

            $navigation = Navigation::create($item);

            if ( ! empty($children))
            {
                foreach ($children as $child)
                {
                    $child['parent_id'] = $navigation->id;
                    Navigation::create($child);
                }
                $children = null;
            }
        }
    }

    public function loadData()
    {
        require_once 'data/navigation.php';

        return $items;
    }

}