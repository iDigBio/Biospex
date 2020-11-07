<?php
/*
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

/**
 * Class GroupPresenter
 *
 * @package App\Presenters
 */
class GroupPresenter extends Presenter
{
    /**
     * Return show icon.
     *
     * @return string
     */
    public function groupShowIcon()
    {
        return '<a href="'.route('admin.groups.show', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'. t('View Group').'">
            <i class="fas fa-eye"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIcon()
    {
        return '<a href="'.route('admin.groups.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'. t('Edit Group').'">
            <i class="fas fa-edit"></i></a>';
    }

    /**
     * Return return edit icon.
     *
     * @return string
     */
    public function groupEditIconLrg()
    {
        return '<a href="'.route('admin.groups.edit', [
                $this->model->id,
            ]).'" data-hover="tooltip" title="'. t('Edit Group').'">
            <i class="fas fa-edit fa-2x"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIcon()
    {
        return '<a href="'.route('admin.groups.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'. t('Delete Group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Delete Group').'?" data-content="'. t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt"></i></a>';
    }

    /**
     * Return return delete icon.
     *
     * @return string
     */
    public function groupDeleteIconLrg()
    {
        return '<a href="'.route('admin.groups.delete', [
                $this->model->id,
            ]).'" class="prevent-default"
            title="'. t('Delete Group').'" 
            data-hover="tooltip"        
            data-method="delete"
            data-confirm="confirmation"
            data-title="'. t('Delete Group').'?" data-content="'. t('This will permanently delete the record and all associated records.').'">
            <i class="fas fa-trash-alt fa-2x"></i></a>';
    }

    /**
     * Return return invite icon.
     *
     * @return string
     */
    public function groupInviteIcon()
    {
        $route = route('admin.invites.index', [$this->model->id]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#invite-modal" 
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
        $route = route('admin.invites.index', [$this->model->id]);

        return '<a href="#" class="preventDefault" data-toggle="modal" data-remote="'.$route.'" 
                    data-target="#invite-modal" 
                    data-hover="tooltip" title="'.t('Invite users to %s group.', $this->model->title).'">
                    <i class="fas fa-user-plus fa-2x"></i></a>';
    }
}