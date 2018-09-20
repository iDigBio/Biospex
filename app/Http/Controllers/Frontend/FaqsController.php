<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\FaqCategory;

class FaqsController extends Controller
{
    /**
     * Show categories.
     *
     * @param \App\Repositories\Interfaces\FaqCategory $faqCategoryContract
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(FaqCategory $faqCategoryContract)
    {
        $categories = $faqCategoryContract->getCategoriesWithFaqOrdered();

        return view('front.faq.index', compact('categories'));
    }
}
