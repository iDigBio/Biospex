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
 * Class RapidRecordPresenter
 *
 * @package App\Presenters
 */
class RapidRecordPresenter extends Presenter
{
    /**
     * Set GBIF link.
     *
     * @return string
     */
    public function gbifLink()
    {
        $baseUrl = 'https://gbif.org/occurrence/#';
        $id = !empty($this->model->gbifID_gbifR) ?
            $this->model->gbifID_gbifR :
            (!empty($this->model->gbifID_gbifP) ? $this->model->gbifID_gbifP : null);
        $url = isset($id) ? str_replace('#', $id, $baseUrl) : null;

        return isset($url) ? '<p><span class="font-weight-bold">'.t("GBIF").': </span><a href="'.$url.'" target="_blank">'.$url.'</a></p>' : '';
    }

    /**
     * Set iDigBio link.
     *
     * @return string
     */
    public function idigbioLink()
    {
        $baseUrl = 'https://www.idigbio.org/portal/records/#';
        $id = !empty($this->model->idigbio_uuid_idbP) ? $this->model->idigbio_uuid_idbP : null;
        $url = isset($id) ? str_replace('#', $id, $baseUrl) : null;

        return isset($url) ? '<p><span class="font-weight-bold">'.t("iDigBio").': </span><a href="'.$url.'" target="_blank">'.$url.'</a></p>' : '';
    }
}