<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserContract;
use App\Http\Requests\EditUserFormRequest;

class UsersController extends Controller
{

    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * UsersController constructor.
     * @param UserContract $userContract
     */
    public function __construct(UserContract $userContract)
    {
        $this->userContract = $userContract;
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        return redirect()->route('web.users.edit', [request()->user()->id]);
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
        $user = $this->userContract->with('profile')->find($id);

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
        $user = $this->userContract->setCacheLifetime(0)->with('profile')->find($users);

        if ($user->cannot('update', $user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        $result = $this->userContract->update($user->id, $request->all());

        $user->profile->fill($request->all());
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
}
