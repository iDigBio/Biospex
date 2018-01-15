<?php

namespace App\Repositories\Interfaces;

use App\Repositories\RepositoryInterface;

interface FaqCategory extends RepositoryInterface
{

    /**
     * Get categories with faq, ordered by id and grouped by id.
     *
     * @return mixed
     */
    public function getCategoriesWithFaqOrdered();

    /**
     * Get category select.
     *
     * @return mixed
     */
    public function getFaqCategorySelect();

    /**
     * @return mixed
     */
    public function getFaqCategoryOrderId();
}