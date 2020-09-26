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


class ExportFormPresenter extends Presenter
{
    /**
     * Set form name with user.
     *
     * @return string
     */
    public function formNameUser()
    {
        $user = explode('@', $this->model->user->email);

        $formName = [
            strtoupper($this->model->destination),
            $this->model->id,
            $this->model->created_at->format('Y-m-d'),
            $user[0]
        ];

        return implode('_', $formName);
    }

    /**
     * Set form name without user.
     *
     * @see \App\Jobs\RapidExportJob
     * @return string
     */
    public function formName()
    {
        $formName = [
            strtoupper($this->model->destination),
            $this->model->id,
            $this->model->created_at->format('Y-m-d'),
        ];

        return implode('_', $formName);
    }
}