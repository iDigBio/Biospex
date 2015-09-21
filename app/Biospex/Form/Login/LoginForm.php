<?php namespace Biospex\Form\Login;

/**
 * LoginForm.php
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
use Biospex\Form\Form;
use Biospex\Validation\ValidableInterface;
use Biospex\Repo\Session\SessionInterface;

class LoginForm extends Form
{
    public function __construct(ValidableInterface $validator, SessionInterface $session)
    {
        $this->validator = $validator;
        $this->repo = $session;
    }

    /**
     * Create a new session and override the save method in Form
     *
     * @return integer
     */
    public function save(array $input)
    {
        if (! $this->valid($input)) {
            return false;
        }

        return $this->repo->store($input);
    }
}
