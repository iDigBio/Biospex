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
        $user = $repo->with(['profile'])->find($request->user()->id);
        $categories = $this->category->with(['faqs'])->groupBy('id')->get();

        return view('backend.faqs.index', compact('user', 'categories'));
    }

    /**
     * Show create forms for categories and faq.
     *
     * @param Request $request
     * @param User $repo
     * @param null $category
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request, User $repo, $category = null)
    {
        $user = $repo->with(['profile'])->find($request->user()->id);
        $categories = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();

        return view('backend.faqs.create', compact('user', 'categories', 'category'));
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

        return redirect()->route('admin.faqs.index');
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

        return redirect()->route('admin.faqs.create', $category->id);
    }

    /**
     *
     */
    public function show()
    {

    }

    /**
     * Edit Category or Faq.
     *
     * @param Request $request
     * @param User $repo
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, User $repo, $categoryId, $faqId)
    {
        $user = $repo->with(['profile'])->find($request->user()->id);
        $categories = [null => 'Please Select'] + $this->category->pluck('label', 'id')->toArray();
        $category = $this->category->find($categoryId);
        $faq = $this->faq->find($faqId);

        return view('backend.faqs.edit', compact('user', 'categories', 'category', 'faq'));
    }

    /**
     * Update FAQ
     * @param FaqFormRequest $request
     * @param $categoryId
     * @param $faqId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(FaqFormRequest $request, $categoryId, $faqId)
    {
        if ((int) $faqId === 0)
        {
            Toastr::warning('FAQ Id needed in order to update.', 'FAQ Update Warning');

            return redirect()->route('admin.faqs.index');
        }

        $result = $this->faq->update($request->all(), $faqId);

        $result ? Toastr::success('FAQ has been updated successfully.', 'FAQ Update')
            : Toastr::error('FAQ could not be updated.', 'FAQ Update');

        return redirect()->route('admin.faqs.edit', [$categoryId, $faqId]);
    }

    /**
     *
     */
    public function updateCategory(FaqCategoryFormRequest $request, $categoryId)
    {
        $result = $this->category->update(['name' => $request->get('name'), 'label' => $request->get('name')], $categoryId);

        $result ? Toastr::success('Category has been updated successfully.', 'Category Update')
            : Toastr::error('Category could not be updated.', 'Category Update');

        return redirect()->route('admin.faqs.edit', [$categoryId, 0]);
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