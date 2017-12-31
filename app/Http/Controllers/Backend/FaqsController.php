<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\FaqCategoryFormRequest;
use App\Http\Requests\FaqFormRequest;
use App\Interfaces\FaqCategory;
use App\Interfaces\User;
use App\Interfaces\Faq;
use App\Facades\Flash;

class FaqsController extends Controller
{

    /**
     * @var Faq
     */
    private $faqContract;

    /**
     * @var FaqCategory
     */
    private $faqCategoryContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * FaqController constructor.
     *
     * @param FaqCategory $faqCategoryContract
     * @param Faq $faqContract
     * @param User $userContract
     */
    public function __construct(
        FaqCategory $faqCategoryContract,
        Faq $faqContract,
        User $userContract
    )
    {
        $this->faqCategoryContract = $faqCategoryContract;
        $this->faqContract = $faqContract;
        $this->userContract = $userContract;
    }

    /**
     * Show Faq list by category.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id,['profile']);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->getFaqCategorySelect();
        $categories = $this->faqCategoryContract->getFaqCategoryOrderId();
        $categoryId = null;
        $faqId = null;

        return view('backend.faqs.index', compact('user', 'categories', 'select', 'categoryId', 'faqId'));
    }

    /**
     * Show create forms for categories and faq.
     *
     * @param $categoryId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create($categoryId)
    {
        $user = $this->userContract->findWith(request()->user()->id,['profile']);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->getFaqCategorySelect();
        $categories = $this->faqCategoryContract->getFaqCategoryOrderId();

        return view('backend.faqs.index', compact('user', 'categories', 'select', 'categoryId'));
    }

    /**
     * Create FAQ.
     *
     * @param FaqFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(FaqFormRequest $request)
    {
        $faq = $this->faqContract->create($request->all());

        $faq ? Flash::success('FAQ has been created successfully.') :
            Flash::error('FAQ could not be saved.');

        return redirect()->route('admin.faqs.create', $request->get('faq_category_id'));
    }

    /**
     * Store Category.
     *
     * @param FaqCategoryFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeCategory(FaqCategoryFormRequest $request)
    {
        $category = $this->faqCategoryContract->create(['name' => $request->get('name')]);

        $category ? Flash::success('Category has been created successfully.') :
            Flash::error('Category could not be saved.');

        return redirect()->route('admin.faqs.index', $category->id);
    }

    /**
     * Edit Category or Faq.
     *
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit($categoryId, $faqId)
    {
        $user = $this->userContract->findWith(request()->user()->id,['profile']);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->getFaqCategorySelect();
        $categories = $this->faqCategoryContract->getFaqCategoryOrderId();
        $faq = $this->faqCategoryContract->find($faqId);

        return view('backend.faqs.index', compact('user', 'categories', 'select', 'categoryId', 'faq'));
    }

    /**
     * Update FAQ.
     *
     * @param FaqFormRequest $request
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(FaqFormRequest $request, $categoryId, $faqId)
    {
        $result = $this->faqContract->update($request->all(), $faqId);

        $result ? Flash::success('FAQ has been updated successfully.')
            : Flash::error('FAQ could not be updated.');

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Edit Category.
     *
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editCategory($categoryId, $faqId)
    {
        $user = $this->userContract->findWith(request()->user()->id,['profile']);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->getFaqCategorySelect();
        $categories = $this->faqCategoryContract->getFaqCategoryOrderId();
        $category = $this->faqCategoryContract->find($categoryId);

        return view('backend.faqs.index', compact('user', 'select', 'category', 'categories', 'categoryId', 'faqId'));
    }

    /**
     * Update category.
     * 
     * @param FaqCategoryFormRequest $request
     * @param $categoryId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCategory(FaqCategoryFormRequest $request, $categoryId)
    {
        $result = $this->faqCategoryContract->update(['name' => $request->get('name')], $categoryId);

        $result ? Flash::success('Category has been updated successfully.')
            : Flash::error('Category could not be updated.');

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Delete resource.
     * 
     * Remove the specified resource from storage.
     * @param $categoryId
     * @param null $faqId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($categoryId, $faqId)
    {
        if ((int) $faqId === 0)
        {
            $this->faqCategoryContract->delete($categoryId) ?
                Flash::success('The category and all faqs have been deleted.') :
                Flash::error('Category could not be deleted.');
        }
        else
        {
            $result = $this->faqContract->delete($faqId);
            $result ? Flash::success('The FAQ has been deleted.')
                : Flash::error('The FAQ could not be deleted.');
        }

        return redirect()->route('admin.faqs.index');
    }
}