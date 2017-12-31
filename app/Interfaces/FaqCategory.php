<?php

namespace App\Interfaces;

interface FaqCategory extends Eloquent
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