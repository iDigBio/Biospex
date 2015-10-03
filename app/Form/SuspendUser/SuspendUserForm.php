<?php namespace App\Form\SuspendUser;

/**
 * SuspendUserForm.php
 *
 * @package    Biospex Package
 * @version    2.0
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
use App\Form\Form;
use App\Repositories\Contracts\User;

class SuspendUserForm extends Form
{
    public function __construct(User $user)
    {
        $this->repo = $user;
    }

    /**
     * Process the requested action
     *
     * @return integer
     */
    public function suspend(array $input)
    {
        if (! $this->valid($input)) {
            return false;
        }

        return $this->user->suspend($input['id'], $input['minutes']);
    }
}
