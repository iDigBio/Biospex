<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActorFormRequest;
use App\Repositories\Contracts\Actor;
use App\Repositories\Contracts\User;
use Illuminate\Http\Request;

class ActorsController extends Controller
{
    /**
     * @var Actor
     */
    private $actor;
    
    /**
     * @var User
     */
    private $user;

    /**
     * ActorsController constructor.
     * 
     * @param Actor $actor
     * @param User $user
     */
    public function __construct(Actor $actor, User $user)
    {
        $this->actor = $actor;
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
        $actors = $this->actor->all();
        
        return view('backend.actors.index', compact('user', 'actors'));
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
        $actors = $this->actor->all();

        return view('backend.actors.index', compact('user', 'actors'));
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

        return redirect()->route('admin.actors.index');
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
        $actors = $this->actor->all();
        $actor = $this->actor->find($id);

        return view('backend.actors.index', compact('user', 'actors', 'actor'));
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

        return redirect()->route('admin.actors.index');
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

        return redirect()->route('admin.actors.index');
    }
}
