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
 * Class ResourcePresenter
 */
class ResourcePresenter extends Presenter
{
    /**
     * Build link to logo thumb.
     *
     * @return string
     */
    public function documentUrl()
    {
        $document = $this->model->document;

        if ($this->variantExists($document)) {
            return '<a href="'.$document->url().'" target="_blank"><i class="fas fa-file"></i> '.$document->originalFilename().'</a>';
        }

        return '';
    }
}
