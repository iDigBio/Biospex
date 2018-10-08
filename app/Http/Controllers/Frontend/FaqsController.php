<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\Faq;
use App\Repositories\Interfaces\FaqCategory;

class FaqsController extends Controller
{

    /**
     * @var Faq
     */
    public $faqContract;
    
    /**
     * @var FaqCategory
     */
    public $faqCategoryContract;

    /**
     * FaqController constructor.
     *
     * @param Faq $faqContract
     * @param FaqCategory $faqCategoryContract
     */
    public function __construct(Faq $faqContract, FaqCategory $faqCategoryContract)
    {
        $this->faqContract = $faqContract;
        $this->faqCategoryContract = $faqCategoryContract;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = $this->faqCategoryContract->getCategoriesWithFaqOrdered();

        return view('front.faqs.index', compact('categories'));
    }
}
