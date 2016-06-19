<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\FaqCategoryFormRequest;
use App\Http\Requests\FaqFormRequest;
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
     * @var User
     */
    private $user;

    /**
     * FaqController constructor.
     *
     * @param FaqCategory $category
     * @param Faq $faq
     * @param User $user
     */
    public function __construct(FaqCategory $category, Faq $faq, User $user)
    {
        $this->category = $category;
        $this->faq = $faq;
        $this->user = $user;
    }

    /**
     * Show Faq list by category.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();
        $categories = $this->category->with(['faqs'])->groupBy('id')->get();
        $categoryId = null;
        $faqId = null;

        return view('backend.faqs.index', compact('user', 'categories', 'select', 'categoryId', 'faqId'));
    }

    /**
     * Show create forms for categories and faq.
     *
     * @param Request $request
     * @param $categoryId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request, $categoryId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();
        $categories = $this->category->with(['faqs'])->groupBy('id')->get();

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
        $faq = $this->faq->create($request->all());

        $faq ? Toastr::success('FAQ has been created successfully.', 'FAQ Create') : Toastr::error('FAQ could not be saved.', 'FAQ Create');

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
        $category = $this->category->create(['name' => $request->get('name'), 'label' => $request->get('name')]);

        $category ? Toastr::success('Category has been created successfully.', 'Category Create') : Toastr::error('Category could not be saved.', 'Category Create');

        return redirect()->route('admin.faqs.index', $category->id);
    }

    /**
     * Edit Category or Faq.
     *
     * @param Request $request
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, $categoryId, $faqId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();
        $categories = $this->category->with(['faqs'])->groupBy('id')->get();
        $faq = $this->faq->find($faqId);

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
        $result = $this->faq->update($request->all(), $faqId);

        $result ? Toastr::success('FAQ has been updated successfully.', 'FAQ Update')
            : Toastr::error('FAQ could not be updated.', 'FAQ Update');

        return redirect()->route('admin.faqs.index');
    }

    /**
     * Edit Category.
     *
     * @param Request $request
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editCategory(Request $request, $categoryId, $faqId)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();
        $categories = $this->category->with(['faqs'])->groupBy('id')->get();
        $category = $this->category->find($categoryId);

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
        $result = $this->category->update(['name' => $request->get('name'), 'label' => $request->get('name')], $categoryId);

        $result ? Toastr::success('Category has been updated successfully.', 'Category Update')
            : Toastr::error('Category could not be updated.', 'Category Update');

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
            $result = $this->category->delete($categoryId);
            $result ? Toastr::success('The category and all faqs have been deleted.', 'Category Delete')
                : Toastr::error('Category could not be deleted.', 'Category Delete');
        }
        else
        {
            $result = $this->faq->delete($faqId);
            $result ? Toastr::success('The FAQ has been deleted.', 'FAQ Delete')
                : Toastr::error('The FAQ could not be deleted.', 'FAQ Delete');
        }

        return redirect()->route('admin.faqs.index');
    }
}