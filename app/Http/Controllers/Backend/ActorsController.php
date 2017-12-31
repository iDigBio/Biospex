<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActorFormRequest;
use App\Interfaces\Actor;
use App\Interfaces\User;

class ActorsController extends Controller
{

    /**
     * @var Actor
     */
    private $actorContract;

    /**
     * @var User
     */
    private $userContract;

    /**
     * ActorsController constructor.
     *
     * @param Actor $actorContract
     * @param User $userContract
     */
    public function __construct(
        Actor $actorContract,
        User $userContract
    )
    {
        $this->actorContract = $actorContract;
        $this->userContract = $userContract;
    }

    /**
     * Show Actor index.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $actors = $this->actorContract->all();
        $trashed = $this->actorContract->getOnlyTrashed();

        return view('backend.actors.index', compact('user', 'actors', 'trashed'));
    }

    /**
     * Create form.
     *
     * @return mixed
     */
    public function create()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $actors = $this->actorContract->all();
        $trashed = $this->actorContract->getOnlyTrashed();

        return view('backend.actors.index', compact('user', 'actors', 'trashed'));
    }

    /**
     * Create Actor.
     *
     * @param ActorFormRequest $request
     * @return mixed
     */
    public function store(ActorFormRequest $request)
    {
        $actor = $this->actorContract->create($request->all());

        collect($request->get('contacts'))->reject(function ($contact) {
            return $contact['email'] === '';
        })->each(function ($contact) use ($actor) {
            $actor->contacts()->create(['email' => $contact['email']]);
        });

        $actor ?
            Flash::success('Actor has been created successfully.') :
            Flash::error('Actor could not be saved.');

        return redirect()->route('admin.actors.index');
    }

    /**
     * Edit Actor.
     *
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $actors = $this->actorContract->all();
        $trashed = $this->actorContract->getOnlyTrashed();
        $actor = $this->actorContract->findWith($id, ['contacts']);

        return view('backend.actors.index', compact('user', 'actors', 'actor', 'trashed'));
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
        $this->actorContract->update($request->all(), $id) ?
            Flash::success('Actor has been updated successfully.') :
            Flash::error('Actor could not be updated.');

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
        $this->actorContract->delete($id) ?
            Flash::success('The actor has been deleted.') :
            Flash::error('Actor could not be deleted.');

        return redirect()->route('admin.actors.index');
    }

    /**
     * Force delete soft deleted records.
     *
     * @param $id
     * @return mixed
     */
    public function trash($id)
    {
        $this->actorContract->destroy($id) ?
            Flash::success('Actor has been forcefully deleted.') :
            Flash::error('Actor could not be forcefully deleted.');

        return redirect()->route('admin.actors.index');
    }
}
