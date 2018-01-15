<?php

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
