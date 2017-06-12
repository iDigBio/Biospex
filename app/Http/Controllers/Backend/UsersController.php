<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Toastr;
use App\Http\Requests\EditUserFormRequest;
use App\Http\Requests\PasswordFormRequest;
use App\Repositories\Contracts\UserContract;
use App\Services\Model\ModelDeleteService;
use App\Services\Model\ModelDestroyService;
use App\Services\Model\ModelRestoreService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use App\Http\Controllers\Controller;

class UsersController extends Controller
{

    use ResetsPasswords;

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
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index($id = null)
    {
        $user = $this->userContract->with('profile')->find(request()->user()->id);
        $users = $this->userContract->with('profile')->orderBy('created_at', 'asc')->findAll();
        $trashed = $this->userContract->onlyTrashed();

        $editUser = $id !== null ? $this->userContract->with('profile')->find($id) : null;

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
        $result = $this->userContract->update($id, $request->all());
        $user = $this->userContract->with('profile')->find($id);
        $user->profile->first_name = $request->input('first_name');
        $user->profile->last_name = $request->input('last_name');
        $user->profile->timezone = $request->input('timezone');
        $user->profile()->save($user->profile);

        $result ? Toastr::success('User has been updated.', 'User Update') :
            Toastr::error('User could not be updated.', 'User Update');

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

        $emails = $this->userContract->where('email', 'like', request()
                ->get('q') . '%')
                ->findAll(['email as text'])->toArray();

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

        Toastr::success('User has been updated.', 'User Update');

        return redirect()->route('admin.users.edit', [$user->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ModelDeleteService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(ModelDeleteService $service, $id)
    {
        $service->deleteUser($id) ?
            Toastr::success('User has been deleted.', 'User Delete') :
            Toastr::error('User could not be deleted.', 'User Delete');

        return redirect()->route('admin.users.index');
    }

    /**
     * Forcefully delete trashed records.
     *
     * @param ModelDestroyService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(ModelDestroyService $service, $id)
    {
        $service->destroyUser($id) ?
            Toastr::success('User has been forcefully deleted.', 'User Destroy') :
            Toastr::error('User could not be forcefully deleted.', 'User Destroy');

        return redirect()->route('admin.users.index');
    }

    /**
     * Restore deleted record.
     *
     * @param ModelRestoreService $service
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore(ModelRestoreService $service, $id)
    {
        $service->restoreUser($id) ?
            Toastr::success('User has been restored successfully.', 'User Restore') :
            Toastr::error('User could not be restored.', 'User Restore');

        return redirect()->route('admin.users.index');
    }
}
