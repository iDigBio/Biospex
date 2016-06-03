<?php 

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Faq;
use App\Repositories\Contracts\FaqCategory;

class FaqsController extends Controller
{

    /**
     * @var Faq
     */
    private $faq;
    
    /**
     * @var FaqCategory
     */
    private $category;

    /**
     * FaqController constructor.
     *
     * @param Faq $faq
     * @param FaqCategory $category
     */
    public function __construct(Faq $faq, FaqCategory $category)
    {

        $this->faq = $faq;
        $this->category = $category;
    }

    /**
     * Show categories.
     * 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $categories = $this->category->with(['faqs'])->orderBy(['id' => 'asc'])->get();

        return view('frontend.faq.index', compact('categories'));
    }
}
