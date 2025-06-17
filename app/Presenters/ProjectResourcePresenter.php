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
 * Class ProjectResourcePresenter
 */
class ProjectResourcePresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function resource()
    {
        $name = $this->model->name;
        $description = $this->model->description;

        if ($this->model->type === 'File Download') {
            return '<a href="'.$this->model->download->url().'" target="_blank" data-hover="tooltip" title="'.$description.'">
            <i class="fas fa-file"></i> '.$name.'</a>';
        }

        return '<a href="'.$name.'" target="_blank" data-hover="tooltip" title="'.$description.'">
            <i class="fas fa-link"></i> '.$name.'</a>';
    }
}
