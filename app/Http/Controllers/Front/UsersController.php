<?php namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\User;
use App\Services\Common\UserService;
use App\Http\Requests\EditUserFormRequest;

class UsersController extends Controller
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var UserService
     */
    public $service;

    /**
     * Instantiate a new UsersController
     *
     * @param UserService $service
     * @param Dispatcher $events
     * @param User $user
     * @param Group $group
     * @param Permission $permission
     * @param Invite $invite
     * @param Router $router
     */
    public function __construct(
        UserService $service,
        User $user
    ) {
        $this->user = $user;
        $this->service = $service;
    }

    /**
     * \Redirect to edit page.
     *
     * @param  int $id
     * @return Response
     */
    public function read($id)
    {
        return redirect()->route('users.get.edit', [$id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param $id
     * @return Response
     */
    public function edit($id)
    {
        $user = $this->user->find($id);

        if ( ! policy($user)->edit($user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('projects.get.index');
        }

        $vars = $this->service->edit($user);

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
        $user = $this->user->find($request->input('users'));

        if ( ! policy($user)->update($user))
        {
            session_flash_push('warning', trans('pages.insufficient_permissions'));

            return redirect()->route('projects.get.index');
        }

        $this->service->update($request);

        return redirect()->route('users.get.edit', [$user->id]);
    }
}
