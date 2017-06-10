<?php

namespace App\Services\Actor;


use App\Http\Requests\ActorFormRequest;
use App\Repositories\Contracts\ActorContract;
use App\Repositories\Contracts\UserContract;
use App\Services\BaseService;
use Illuminate\Http\Request;
use Toastr;

class ActorAdminService extends BaseService
{

    /**
     * @var UserContract
     */
    private $userContract;

    /**
     * @var ActorContract
     */
    private $actorContract;

    /**
     * ActorAdminService constructor.
     * @param UserContract $userContract
     * @param ActorContract $actorContract
     */
    public function __construct(UserContract $userContract, ActorContract $actorContract)
    {

        $this->userContract = $userContract;
        $this->actorContract = $actorContract;
    }

    public function showIndex(Request $request)
    {
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $actors = $this->actorContract->findAll();
        $trashed = $this->actorContract->getAllTrashed();

        return view('backend.actors.index', compact('user', 'actors', 'trashed'));
    }

    public function showCreateForm(Request $request)
    {
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $actors = $this->actorContract->findAll();
        $trashed = $this->actorContract->getAllTrashed();

        return view('backend.actors.index', compact('user', 'actors', 'trashed'));
    }

    /**
     * Create actor
     * @param ActorFormRequest $request
     * @return mixed
     */
    public function createActor(ActorFormRequest $request)
    {
        $actor = $this->actorContract->create($request->all());

        foreach ($request->get('contacts') as $contact)
        {
            if ($contact['email'] !== '')
            {
                $actor->contacts()->create(['email' => $contact['email']]);
            }
        }

        return $actor;
    }

    /**
     * Edit actor.
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function editActor(Request $request, $id)
    {
        $user = $this->userContract->with('profile')->find($request->user()->id);
        $actor = $this->actorContract->with('contacts')->find($id);
        $actors = $this->actorContract->findAll();
        $trashed = $this->actorContract->getAllTrashed();
        
        return view('backend.actors.index', compact('user', 'actors', 'actor', 'trashed'));
    }

    /**
     * @param ActorFormRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateActor(ActorFormRequest $request, $id)
    {
        $result = $this->actorContract->update($id, $request->all());

        $result ? Toastr::success('Actor has been updated successfully.', 'Actor Update')
            : Toastr::error('Actor could not be updated.', 'Actor Update');

        return redirect()->route('admin.actors.index');
    }
}