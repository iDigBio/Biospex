<?php namespace Biospex\Http\Controllers\Front;

use Biospex\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Biospex\Http\Requests\GroupFormRequest;
use Biospex\Repositories\Contracts\User;
use Biospex\Repositories\Contracts\Group;

class GroupsController extends Controller
{
    /**
     * @var Group
     */
    public $group;

    /**
     * @var User
     */
    public $user;
    /**
     * @var Request
     */
    private $request;

    /**
     * Instantiate a new GroupsController
     *
     * @param Request $request
     * @param Group $group
     * @param User $user
     * @internal param Auth|AuthManager $auth
     * @internal param Sentry $sentry
     * @internal param User $user
     * @internal param Dispatcher $events
     * @internal param Group $group
     * @internal param Permission $permission
     * @internal param GroupForm $groupForm
     */
    public function __construct(Request $request, Group $group, User $user)
    {
        $this->group = $group;
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * Display groups.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = $this->user->findWith($this->request->user()->id, ['groups']);

        return view('front.groups.index', compact('user'));
    }

    /**
     * Show create group form.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $user = $this->request->user();

        return view('front.groups.create', compact('user'));
    }

    /**
     * Store a newly created group.
     *
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(GroupFormRequest $request)
    {
        $user = $request->user();

        $data = [
            'user_id' => $user->id,
            'name'    => $request->get('name'),
            'label'   => $request->get('name')
        ];

        $group = $this->group->create($data);

        if ($group) {
            $user->assignGroup($group);
            session_flash_push('success', trans('groups.created'));

            return redirect()->route('groups.get.index');
        }

        session_flash_push('warning', trans('groups.loginreq'));

        return redirect()->route('groups.get.create');
    }

    /**
     * Show group page.
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function read($id)
    {
        $user = $this->request->user();
        $group = $this->group->findWith($id, [
            'projects',
            'owner.profile',
            'users.profile'
        ]);

        if ($user->cannot('read', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('groups.get.index');
        }

        return view('front.groups.read', compact('group'));

    }

    /**
     * Show group edit form.
     *
     * @param $id
     * @return \Illuminate\View\View1
     */
    public function edit($id)
    {
        $user = $this->request->user();
        $group = $this->group->find($id);

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('groups.get.index');
        }

        return view('front.groups.edit', compact('group'));
    }

    /**
     * Update group
     * @param GroupFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(GroupFormRequest $request)
    {
        $user = $this->request->user();
        $group = $this->group->find($request->get('id'));

        if ($user->cannot('update', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('groups.get.index');
        }

        $group->name = $group->name == 'admins' ? $group->name : $request->get('name');
        $group->label = $group->name == 'admins' ? $group->name : $request->get('name');
        $group->save();

        session_flash_push('success', trans('groups.updated'));

        return redirect()->route('groups.get.index');
    }

    /**
     * Remove group.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $user = $this->auth->user();
        $group = $this->group->find($id);

        if ($user->cannot('delete', $group))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('groups.get.index');
        }

        $group->delete();
        session_flash_push('success', trans('groups.group_destroyed'));

        return redirect()->route('groups.get.index');
    }
}
