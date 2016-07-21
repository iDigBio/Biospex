<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActorFormRequest;
use App\Repositories\Contracts\Translation;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Translation
     */
    private $translation;

    /**
     * ActorsController constructor.
     *
     * @param User $user
     * @param Translation $translation
     */
    public function __construct(User $user, Translation $translation)
    {
        $this->user = $user;
        $this->translation = $translation;
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
        $pages = $this->translation->where(['group' => 'html'])->get();

        return view('backend.pages.index', compact('user', 'pages'));
    }

    /**
     * Redirect show route.
     *
     * @return mixed
     */
    public function show()
    {
        return redirect()->route('admin.pages.index');
    }

    /**
     * Create form.
     *
     * @param Request $request
     * @return mixed
     */
    public function create(Request $request)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $pages = $this->actor->all();
        $trashed = $this->actor->trashed()->get();

        return view('backend.pages.index', compact('user', 'pages', 'trashed'));
    }

    /**
     * Create Actor.
     *
     * @param ActorFormRequest $request
     * @return mixed
     */
    public function store(ActorFormRequest $request)
    {
        $actor = $this->actor->create($request->all());

        $actor ? Toastr::success('Actor has been created successfully.', 'Actor Create') :
            Toastr::error('Actor could not be saved.', 'Actor Create');

        return redirect()->route('admin.pages.index');
    }

    /**
     * Edit Actor.
     *
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function edit(Request $request, $id)
    {
        $user = $this->user->with(['profile'])->find($request->user()->id);
        $pages = $this->actor->all();
        $actor = $this->actor->find($id);
        $trashed = $this->actor->trashed();

        return view('backend.pages.index', compact('user', 'pages', 'actor', 'trashed'));
    }

    /**
     * Update Actor.
     *
     * @param ActorFormRequest $request
     * @param $id
     * @return mixed
     */
    public function update(ActorFormRequest $request, $id)
    {
        $result = $this->actor->update($request->all(), $id);

        $result ? Toastr::success('Actor has been updated successfully.', 'Actor Update')
            : Toastr::error('Actor could not be updated.', 'Actor Update');

        return redirect()->route('admin.pages.index');
    }

    /**
     * Delete actor.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $result = $this->actor->delete($id);

        $result ? Toastr::success('The actor has been deleted.', 'Actor Delete')
            : Toastr::error('Actor could not be deleted.', 'Actor Delete');

        return redirect()->route('admin.pages.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $result = $this->actor->forceDelete($id);

        $result ? Toastr::success('Actor has been forcefully deleted.', 'Actor Destroy')
            : Toastr::error('Actor could not be forcefully deleted.', 'Actor Destroy');

        return redirect()->route('admin.pages.index');
    }
}