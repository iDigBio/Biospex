<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\User;
use App\Http\Requests\EditUserFormRequest;

class UsersController extends Controller
{
    /**
     * @var User
     */
    public $user;

    /**
     * UsersController constructor.
     * @param User $user
     */
    public function __construct(
        User $user
    ) {
        $this->user = $user;
    }

    /**
     * Redirect to edit page.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($id)
    {
        return redirect()->route('users.get.edit', [$id]);
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

            return redirect()->route('projects.get.index');
        }

        $timezones = timezone_select();
        $cancel = route('projects.get.index');

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

            return redirect()->route('projects.get.index');
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

        return redirect()->route('users.get.edit', [$user->id]);
    }
}
