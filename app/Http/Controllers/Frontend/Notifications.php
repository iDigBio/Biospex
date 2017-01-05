<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\Notification;
use App\Repositories\Contracts\User;
use App\Services\Model\NotificationsService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{

    /**
     * @var User
     */
    private $user;
    /**
     * @var NotificationsService
     */
    private $service;
    /**
     * @var Request
     */
    private $request;

    /**
     * DownloadsController constructor.
     *
     * @param NotificationsService $service
     * @param User $user
     * @param Request $request
     */
    public function __construct(
        NotificationsService $service,
        User $user,
        Request $request
    ) {
        $this->user = $user;
        $this->service = $service;
        $this->request = $request;
    }

    /**
     * Index showing downloads for Expedition.
     *
     */
    public function index()
    {
        $notifications = $this->service->repo->where(['user_id' => $this->request->user()->id])->get();
        $trashed = $this->service->repo->where(['user_id' => $this->request->user()->id])->trashed();

        return view('frontend.notifications.index', compact('notifications', 'trashed'));
    }

    /**
     * Notification delete.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function delete($id)
    {
        $this->service->delete($id) ?
            session_flash_push('success', trans('notifications.notification')) :
            session_flash_push('error', trans('notifications.notification_delete_error'));

        return redirect()->route('web.notifications.index');
    }

    /**
     * Notification destroy.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function destroy($id)
    {
        $this->service->destroy($id) ?
            session_flash_push('success', trans('notifications.notification_destroyed')) :
            session_flash_push('error', trans('notifications.notification_destroy_error'));

        return redirect()->route('web.notifications.index');
    }

    /**
     * Notification restore.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function restore($id)
    {
        $this->service->restore($id) ?
            session_flash_push('success', trans('notifications.notification_restored')) :
            session_flash_push('error', trans('notifications.notification_restored_error'));

        return redirect()->route('web.notifications.index');
    }
}
