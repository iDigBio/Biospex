<?php

namespace App\Http\Controllers\Admin;

use App\Facades\DateHelper;
use App\Facades\FlashHelper;
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
        return redirect()->route('admin.users.edit', [request()->user()->id]);
    }

    /**
     * Redirect to edit page.
     *
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function show($userId)
    {
        return redirect()->route('admin.users.edit', [$userId]);
    }

    /**
     * Show the form for user edit.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     * @throws \Exception
     */
    public function edit()
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);

        if ($user->cannot('update', $user))
        {
            FlashHelper::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('admin.projects.index');
        }

        $timezones = DateHelper::timeZoneSelect();
        $cancel = route('admin.projects.index');

        return view('admin.user.edit', compact('user', 'timezones', 'cancel'));
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
            FlashHelper::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('admin.projects.index');
        }

        $input = $request->all();
        $input['notification'] = $request->exists('notification') ? 1 : 0;
        $result = $this->userContract->update($input, $user->id);

        $user->profile->fill($request->all());
        $user->profile()->save($user->profile);

        if ($result)
        {
            FlashHelper::success(trans('messages.record_updated'));
        }
        else
        {
            FlashHelper::error(trans('messages.record_updated_error'));
        }

        return redirect()->route('admin.users.edit', [$user->id]);
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
            FlashHelper::warning( trans('pages.insufficient_permissions'));

            return redirect()->route('admin.projects.index');
        }

        if ( ! Hash::check($request->input('oldPassword'), $user->password))
        {
            FlashHelper::error(trans('messages.old_password'));

            return redirect()->route('admin.users.edit', [$user->id]);
        }

        $this->resetPassword($user, $request->input('newPassword'));

        FlashHelper::success(trans('messages.password_chg'));

        return redirect()->route('admin.users.edit', [$user->id]);
    }
}
