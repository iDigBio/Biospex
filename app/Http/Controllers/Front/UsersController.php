<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Services\Common\UserService;
use App\Http\Requests\EditUserFormRequest;

use App\Repositories\Contracts\User;
use App\Repositories\Contracts\Group;
use App\Repositories\Contracts\Permission;
use App\Repositories\Contracts\Invite;
use App\Http\Requests\RegisterFormRequest;
use Illuminate\Contracts\Events\Dispatcher;

class UsersController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var UserForm
     */
    protected $userForm;

    /**
     * @var ResendActivationForm
     */
    protected $resendActivationForm;

    /**
     * @var ForgotPasswordForm
     */
    protected $forgotPasswordForm;

    /**
     * @var ChangePasswordForm
     */
    protected $changePasswordForm;

    /**
     * @var SuspendUserForm
     */
    protected $suspendUserForm;

    /**
     * @var Permission
     */
    protected $permission;
    /**
     * @var UserService
     */
    private $service;

    /**
     * Instantiate a new UsersController
     *
     * @param UserService $service
     * @param Dispatcher $events
     * @param User $user
     * @param Group $group
     * @param Permission $permission
     * @param Invite $invite
     */
    public function __construct(
        UserService $service,
        Dispatcher $events,
        User $user,
        Group $group,
        Permission $permission,
        Invite $invite
    ) {
        $this->events = $events;
        $this->user = $user;
        $this->group = $group;
        $this->permission = $permission;
        $this->invite = $invite;
        $this->service = $service;
    }

    /**
     * \Redirect to edit page.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        return redirect()->route('users.edit', [$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit()
    {
        $vars = $this->service->edit();

        if ( ! $vars)
            return redirect()->route('home');

        return view('front.users.edit', $vars);
    }

    /**
     * Update the specified resource in storage.
     * .
     * @param EditUserFormRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(EditUserFormRequest $request)
    {
        $this->service->update($request);

        return redirect()->route('users.edit', $request->get('id'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (! is_numeric($id)) {
            session_flash_push('error', trans('pages.error_delete_user'));

            return redirect()->route('users.index');
        }

        if ($this->user->destroy($id)) {
            $this->events->fire('user.destroyed', ['userId' => $id]);

            session_flash_push('success', trans('users.deleted'));

            return redirect()->route('users.index');
        } else {
            session_flash_push('error', trans('pages.error_delete_user'));

            return redirect()->route('users.index');
        }
    }

    /**
     * Process a password change request
     *
     * @param  int $id
     * @return redirect
     */
    public function change($id)
    {
        $data = \Input::all();
        $data['id'] = $id;

        // Form Processing
        $result = $this->changePasswordForm->change($data);

        if ($result['success']) {
            $this->events->fire('user.passwordchange', ['userId' => $id]);

            // Success!
            session_flash_push('success', $result['message']);

            return redirect()->route('users.show', [$id]);
        } else {
            session_flash_push('error', $result['message']);

            return redirect()->route('users.edit', [$id])
                ->withInput()
                ->withErrors($this->changePasswordForm->errors());
        }
    }
}
