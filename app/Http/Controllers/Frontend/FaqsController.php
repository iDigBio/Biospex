<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\FaqContract;
use App\Repositories\Contracts\FaqCategoryContract;

class FaqsController extends Controller
{

    /**
     * @var FaqContract
     */
    public $faqContract;
    
    /**
     * @var FaqCategoryContract
     */
    public $faqCategoryContract;

    /**
     * FaqController constructor.
     *
     * @param FaqContract $faqContract
     * @param FaqCategoryContract $faqCategoryContract
     */
    public function __construct(FaqContract $faqContract, FaqCategoryContract $faqCategoryContract)
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
        $categories = $this->faqCategoryContract->setCacheLifetime(0)
            ->with('faqs')
            ->orderBy('id', 'asc')
            ->groupBy('id')->findAll();

        return view('frontend.faqs.index', compact('categories'));
    }
}
