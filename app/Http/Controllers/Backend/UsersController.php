<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Flash;
use App\Http\Requests\EditUserFormRequest;
use App\Http\Requests\PasswordFormRequest;
use App\Interfaces\User;
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
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($id = null)
    {
        $user = $this->userContract->findWith(request()->user()->id, ['profile']);
        $users = $this->userContract->getAllUsersOrderByDate();
        $trashed = $this->userContract->getOnlyTrashed();

        $editUser = $id !== null ? $this->userContract->findWith($id, ['profile']) : null;

        $timezones = timezone_select();

        return view('backend.users.index', compact('user', 'users', 'trashed', 'editUser', 'timezones'));
    }

    /**
     * Update user information.
     *
     * @param EditUserFormRequest $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request, $id)
    {
        $result = $this->userContract->update($request->all(), $id);
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
     * Process a password change request.
     *
     * @param PasswordFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pass(PasswordFormRequest $request, $id)
    {
        $user = $this->userContract->find($id);

        $this->resetPassword($user, $request->input('newPassword'));

        Flash::success('User has been updated.');

        return redirect()->route('admin.users.edit', [$user->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id)
    {
        $this->userContract->delete($id) ?
            Flash::success('User has been deleted.') :
            Flash::error('User could not be deleted.');

        return redirect()->route('admin.users.index');
    }

    /**
     * Forcefully delete trashed records.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->userContract->destroy($id) ?
            Flash::success('User has been forcefully deleted.') :
            Flash::error('User could not be forcefully deleted.');

        return redirect()->route('admin.users.index');
    }

    /**
     * Restore deleted record.
     *
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        $this->userContract->restore($id) ?
            Flash::success('User has been restored successfully.') :
            Flash::error('User could not be restored.');

        return redirect()->route('admin.users.index');
    }
}
