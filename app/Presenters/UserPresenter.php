<?php
/**
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Presenters;

class UserPresenter extends Presenter
{
    /**
     * Return full name or email of user.
     *
     * @return mixed|string
     */
    public function fullNameOrEmail()
    {
        $firstName = $this->model->profile->first_name;
        $lastName = $this->model->profile->last_name;
        $email = $this->model->email;

        $isNull = null === $firstName || null === $lastName;

        return $isNull ? $email : $firstName.' '.$lastName;
    }

    /**
     * Return email icon.
     *
     * @return string
     */
    public function email()
    {
        return $this->model->email === null ? '' :
            '<a href="mailto:'.$this->model->email.'" 
            data-hover="tooltip" 
            title="'.__('Contact').'">
            <i class="far fa-envelope"></i> <span class="d-none text d-sm-inline"></span></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function deleteGroupUserIcon()
    {
        return '<a href="'.route('admin.groups.deleteUser', [
                $this->model->pivot->group_id,
                $this->model->id,
            ]).'" class="prevent-default"
            title="'.__('Delete Member').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.__('Delete Member').'?" data-content="'.__('This will permanently delete the member').'">
            <i class="fas fa-trash-alt"></i></a>';
    }
}