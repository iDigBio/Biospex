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

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\FaqCategory;
use App\Models\FaqCategory as Model;

class FaqCategoryRepository extends EloquentRepository implements FaqCategory
{

    /**
     * Specify Model class name
     *
     * @return \Illuminate\Database\Eloquent\Model|string
     */
    public function model()
    {
        return Model::class;
    }

    /**
     * @inheritdoc
     */
    public function getCategoriesWithFaqOrdered()
    {
        $results =  $this->model
            ->with('faqs')
            ->groupBy('id')
            ->orderBy('id', 'asc')
            ->get();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getFaqCategorySelect()
    {
        $results = $this->model->pluck('name', 'id')->toArray();

        $this->resetModel();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getFaqCategoryOrderId()
    {
        $results = $this->model->with('faqs')->groupBy('id')->get();

        $this->resetModel();

        return $results;
    }
}
