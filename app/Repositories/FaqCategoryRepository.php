<?php

namespace App\Repositories;

use App\Interfaces\FaqCategory;
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
        return $this->model
            ->with('faqs')
            ->groupBy('id')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * @inheritdoc
     */
    public function getFaqCategorySelect()
    {
        return $this->model->pluck('name', 'id')->toArray();
    }

    /**
     * @inheritdoc
     */
    public function getFaqCategoryOrderId()
    {
        return $this->model->with('faqs')->groupBy('id')->get();
    }
}
