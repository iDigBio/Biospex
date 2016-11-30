<?php

namespace App\Http\Controllers\Backend;

use App\Repositories\Contracts\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{

    /**
     * @var User
     */
    public $user;
    /**
     * @var Request
     */
    private $request;

    /**
     * UsersController constructor.
     * @param User $user
     * @param Request $request
     */
    public function __construct(User $user, Request $request)
    {
        $this->user = $user;
        $this->request = $request;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('admin.users.edit', [$request->user()->id]);
    }

    /**
     * Redirect to edit page.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('web.users.edit', [$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $user = $this->user->with(['profile'])->find($id);

        if ($user->cannot('update', $user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        $timezones = timezone_select();
        $cancel = route('web.projects.index');

        return view('frontend.users.edit', compact('user', 'timezones', 'cancel'));
    }

    /**
     * Update the specified resource in storage
     * @param EditUserFormRequest $request
     * @param $users
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $users)
    {
        $user = $this->user->with(['profile'])->find($users);

        if ($user->cannot('update', $user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        $result = $this->user->update($request->all(), $user->id);
        $user->profile->first_name = $request->input('first_name');
        $user->profile->last_name = $request->input('last_name');
        $user->profile->timezone = $request->input('timezone');
        $user->profile()->save($user->profile);

        if ($result)
        {
            session_flash_push('success', trans('users.updated'));
        }
        else
        {
            session_flash_push('error', trans('users.notupdated'));
        }

        return redirect()->route('web.users.edit', [$user->id]);
    }

    /**
     * User search for adding to group.
     *
     * @return string
     */
    public function search()
    {
        if (! $this->request->ajax())
        {
            return json_encode(['Invalid']);
        }

        $emails = $this->user->where([['email', 'like', $this->request->get('q') . '%']])
            ->get(['email as text'])->toArray();

        foreach ($emails as $key => $email)
        {
            $emails[$key]['id'] = $email['text'];
        }


        return json_encode(['results' => $emails, 'pagination' => ['more' => false]]);
    }
}
