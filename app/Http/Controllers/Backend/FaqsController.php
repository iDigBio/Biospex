<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\FaqCategoryFormRequest;
use App\Repositories\Contracts\FaqCategory;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use App\Repositories\Contracts\Faq;

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
     * @param FaqCategory $category
     * @param Faq $faq
     */
    public function __construct(FaqCategory $category, Faq $faq)
    {

        $this->faq = $faq;
        $this->category = $category;
    }

    /**
     * Show Faq list by category.
     *
     * @param Request $request
     * @param User $repo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, User $repo)
    {
        Toastr::success('This is a message', 'My Title');
        $user = $repo->with(['profile'])->find($request->user()->id);
        $faqs = $this->faq->with(['categories'])->groupBy('faq_category_id')->get();

        return view('backend.faq.index', compact('user', 'faqs'));
    }

    /**
     * Show create forms for categories and faq.
     * 
     * @param Request $request
     * @param User $repo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request, User $repo)
    {
        $user = $repo->with(['profile'])->find($request->user()->id);

        return view('backend.faq.create', compact('user'));
    }

    /**
     * Store Category.
     * 
     * @param FaqCategoryFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(FaqCategoryFormRequest $request)
    {
        $category = $this->category->create(['name' => $request->get('name'), 'label' => $request->get('name')]);

        $category ? Toastr::success('Category created successfully.', 'Success') : Toastr::error('The category could not be saved.', 'Error');

        return redirect()->route('admin.faqs.create');
    }
    
    public function store()
    {
        
    }

    /**
     * 
     */
    public function show()
    {

    }

    /**
     * 
     */
    public function edit()
    {
        
    }

    /**
     * 
     */
    public function update()
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete()
    {
        
    }

}