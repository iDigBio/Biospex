<?php

namespace App\Http\Controllers\Backend;

use App\Facades\DateHelper;
use App\Facades\Flash;
use App\Http\Requests\EditUserFormRequest;
use App\Http\Requests\PasswordFormRequest;
use App\Repositories\Interfaces\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Http\Controllers\Controller;

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
     * @param null $userId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($userId = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $users = $this->userContract->getAllUsersOrderByDate();

        $editUser = $userId !== null ? $this->userContract->findWith($userId, ['profile']) : null;

        $timezones = DateHelper::timeZoneSelect();

        return view('backend.users.index', compact('user', 'users', 'editUser', 'timezones'));
    }

    /**
     * Update user information.
     *
     * @param EditUserFormRequest $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $userId)
    {
        $result = $this->userContract->update($request->all(), $userId);
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $user->profile->first_name = $request->input('first_name');
        $user->profile->last_name = $request->input('last_name');
        $user->profile->timezone = $request->input('timezone');
        $user->profile()->save($user->profile);

        $result ? Flash::success('User has been updated.') :
            Flash::error('User could not be updated.');

        return redirect()->route('admin.users.index');
    }

    /**
     * User search for adding to group.
     *
     * @return string
     */
    public function search()
    {
        if ( ! request()->ajax())
        {
            return json_encode(['Invalid']);
        }

        $emails = $this->userContract->findUsersByEmailAjax(request()->get('q'));

        foreach ($emails as $key => $email)
        {
            $emails[$key]['id'] = $email['text'];
        }


        return json_encode(['results' => $emails, 'pagination' => ['more' => false]]);
    }

    /**
     * Process password request.
     *
     * @param PasswordFormRequest $request
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pass(PasswordFormRequest $request, $userId)
    {
        $user = $this->userContract->find($userId);

        $this->resetPassword($user, $request->input('newPassword'));

        Flash::success('User has been updated.');

        return redirect()->route('admin.users.edit', [$user->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $userId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($userId)
    {
        $this->userContract->delete($userId) ?
            Flash::success('User has been deleted.') :
            Flash::error('User could not be deleted.');

        return redirect()->route('admin.users.index');
    }
}
