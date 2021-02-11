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
 * Class ActorPresenter
 *
 * @package App\Presenters
 */
class ActorPresenter extends Presenter
{
    /**
     * Return button and path for expert review.
     *
     * @return string
     */
    public function reconcileExpertReviewBtn()
    {
        $route = $this->model->pivot->expert ? 'admin.reconciles.index' : 'admin.reconciles.create';
        $url = route($route, [$this->model->pivot->expedition_id]);

        $class = $this->model->pivot->expert ? 'green' : '';

        return '<a class="btn btn-primary rounded-0 mb-1 '.$class.'" href="'.$url.'">'. t('Expert Review Ambiguities').'</a>';
    }
}