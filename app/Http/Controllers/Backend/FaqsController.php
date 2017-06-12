<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\FaqCategoryFormRequest;
use App\Http\Requests\FaqFormRequest;
use App\Repositories\Contracts\FaqCategoryContract;
use App\Repositories\Contracts\UserContract;
use Illuminate\Http\Request;
use App\Repositories\Contracts\FaqContract;

class FaqsController extends Controller
{

    /**
     * @var FaqContract
     */
    private $faqContract;

    /**
     * @var FaqCategoryContract
     */
    private $faqCategoryContract;

    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * FaqController constructor.
     *
     * @param FaqCategoryContract $faqCategoryContract
     * @param FaqContract $faqContract
     * @param UserContract $userContract
     */
    public function __construct(
        FaqCategoryContract $faqCategoryContract,
        FaqContract $faqContract,
        UserContract $userContract
    )
    {
        $this->faqCategoryContract = $faqCategoryContract;
        $this->faqContract = $faqContract;
        $this->userContract = $userContract;
    }

    /**
     * Show Faq list by category.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->faqCategoryContract->with('faqs')->groupBy('id')->findAll();
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
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->faqCategoryContract->with('faqs')->groupBy('id')->findAll();

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
        $category = $this->faqCategoryContract->create(['name' => $request->get('name')]);

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
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->faqCategoryContract->with('faqs')->groupBy('id')->findAll();
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
        $result = $this->faqContract->update($faqId, $request->all());

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
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $select = [null => 'Please Select'] + $this->faqCategoryContract->pluck('name', 'id')->toArray();
        $categories = $this->faqCategoryContract->with('faqs')->groupBy('id')->findAll();
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
        $result = $this->faqCategoryContract->update($categoryId, ['name' => $request->get('name')]);

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
            $result = $this->faqCategoryContract->delete($categoryId);
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