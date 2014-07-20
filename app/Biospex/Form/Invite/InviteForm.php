<?php namespace Biospex\Form\Invite;
/**
 * SendInviteForm.php
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
use Biospex\Form\Form;
use Biospex\Validation\ValidableInterface;
use Biospex\Repo\Invite\InviteInterface;

class RegisterForm extends Form{

    public function __construct(ValidableInterface $validator, InviteInterface $invite)
    {
        $this->validator = $validator;
        $this->repo = $invite;
    }

    /**
     * Validate emails array
     *
     * @return boolean
     */
    protected function valid(array $input)
    {
        $emails = explode(',', $input['emails']);
        foreach ($emails as $email)
        {
            if (!$this->validator->with($email)->passes())
                return false;
        }

        return true;
    }

}