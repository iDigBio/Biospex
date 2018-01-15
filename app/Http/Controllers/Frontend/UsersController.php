<?php

namespace App\Http\Controllers\Frontend;

use App\Facades\Flash;
use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordFormRequest;
use App\Repositories\Interfaces\User;
use App\Http\Requests\EditUserFormRequest;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    use ResetsPasswords;
    
    /**
     * @var User
     */
    public $userContract;

    /**
     * UsersController constructor.
     * @param User $userContract
     */
    public function __construct(User $userContract)
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
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($userId)
    {
        return redirect()->route('web.users.edit', [$userId]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);

        if ($user->cannot('update', $user))
        {
            Flash::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        $timezones = timezone_select();
        $cancel = route('web.projects.index');

        return view('frontend.users.edit', compact('user', 'timezones', 'cancel'));
    }

    /**
     * Update the specified resource in storage
     * @param EditUserFormRequest $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $userId)
    {
        $user = $this->userContract->findWith($userId, ['profile']);

        if ($user->cannot('update', $user))
        {
            Flash::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        $result = $this->userContract->update($request->all(), $user->id);

        $user->profile->fill($request->all());
        $user->profile()->save($user->profile);

        if ($result)
        {
            Flash::success(trans('users.updated'));
        }
        else
        {
            Flash::error(trans('users.notupdated'));
        }

        return redirect()->route('web.users.edit', [$user->id]);
    }

    /**
     * Process a password change request.
     *
     * @param PasswordFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pass(PasswordFormRequest $request)
    {
        $user = $this->userContract->find($request->route('id'));

        if ( ! policy($user)->pass($user))
        {
            Flash::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('web.projects.index');
        }

        if ( ! Hash::check($request->input('oldPassword'), $user->password))
        {
            Flash::error(trans('users.oldpassword'));

            return redirect()->route('web.users.edit', [$user->id]);
        }

        $this->resetPassword($user, $request->input('newPassword'));

        Flash::success(trans('users.passwordchg'));

        return redirect()->route('web.users.edit', [$user->id]);
    }
}
