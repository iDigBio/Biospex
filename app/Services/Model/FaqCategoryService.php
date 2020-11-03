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

namespace App\Services\Model;

use App\Models\FaqCategory;
use App\Services\Model\Traits\ModelTrait;

/**
 * Class FaqCategoryService
 *
 * @package App\Services\Model
 */
class FaqCategoryService
{
    use ModelTrait;

    /**
     * @var \App\Models\FaqCategory
     */
    private $model;

    /**
     * FaqCategoryService constructor.
     *
     * @param \App\Models\FaqCategory $faqCategory
     */
    public function __construct(FaqCategory $faqCategory)
    {

        $this->model = $faqCategory;
    }

    /**
     * Get categories with faq ordered.
     *
     * @return mixed
     */
    public function getCategoriesWithFaqOrdered()
    {
        return $this->model
            ->with('faqs')
            ->groupBy('id')
            ->orderBy('id', 'asc')
            ->get();
    }
}