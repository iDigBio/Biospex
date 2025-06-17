<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Presenters;

/**
 * Class GroupPresenter
 */
class GroupPresenter extends Presenter
{
    public function groupProjectIcon()
    {
        // <i class="fas fa-users"></i>
        $route = route('admin.groups.show', [$this->model]);

        return '<a href="'.$route.'" 
            data-hover="tooltip" 
            title="'.t('View Group').'">
            <i class="fas fa-users"></i></a>';
    }

    public function groupProjectIconLrg()
    {
        // <i class="fas fa-users"></i>
        $route = route('admin.groups.show', [$this->model]);

        return '<a href="'.$route.'" 
            data-hover="tooltip" 
            title="'.t('View Group').'">
            <i class="fas fa-users fa-2x"></i></a>';
    }

    /**
     * Return show icon.
     *
     * @return string
     */
    public function groupShowIcon()
    {
        return '<a href="'.route('admin.groups.show', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('View Group').'">
            <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIcon()
    {
        return '<a href="'.route('admin.groups.edit', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Edit Group').'">
            <i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIconLrg()
    {
        return '<a href="'.route('admin.groups.edit', [$this->model]).'" 
            data-hover="tooltip" 
            title="'.t('Edit Group').'">
            <i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIcon()
    {
        return '<a href="'.route('admin.users.destroy', [
            $this->model,
        ]).'" class="prevent-default"
            title="'.t('Delete Group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Group').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIconLrg()
    {
        return '<a href="'.route('admin.users.destroy', [
            $this->model,
        ]).'" class="prevent-default"
            title="'.t('Delete Group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'.t('Delete Group').'?" data-content="'.t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return invite icon.
     *
     * @return string
     */
    public function groupInviteIcon()
    {
        $route = route('admin.invites.create', [$this->model]);

        return '<a href="#" class="prevent-default" data-toggle="modal" data-url="'.$route.'" 
                    data-target="#global-modal" data-size="modal-lg" data-dismiss="modal" data-toggle="modal"
                    data-title="'.t('Invite users to %s group.', $this->model->title).'"
                    data-hover="tooltip" title="'.t('Invite users to %s group.', $this->model->title).'">
                    <i class="fas fa-user-plus"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupInviteIconLrg()
    {
        $route = route('admin.invites.create', [$this->model]);

        return '<a href="#" class="prevent-default" data-toggle="modal" data-url="'.$route.'" 
                    data-target="#global-modal" data-size="modal-lg" data-dismiss="modal" data-toggle="modal"
                    data-title="'.t('Invite users to %s group.', $this->model->title).'"
                    data-hover="tooltip" title="'.t('Invite users to %s group.', $this->model->title).'">
                    <i class="fas fa-user-plus fa-2x"></i></a>';
    }
}
