<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserContract;
use App\Services\Model\NotificationsService;

class NotificationsController extends Controller
{
    /**
     * @var UserContract
     */
    public $userContract;

    /**
     * @var NotificationsService
     */
    public $service;

    /**
     * DownloadsController constructor.
     *
     * @param NotificationsService $service
     * @param UserContract $userContract
     */
    public function __construct(
        NotificationsService $service,
        UserContract $userContract
    ) {
        $this->userContract = $userContract;
        $this->service = $service;
    }

    /**
     * Index showing downloads for Expedition.
     *
     */
    public function index()
    {
        $notifications = $this->service->notificationContract
            ->where('user_id', '=', request()->user()->id)
            ->findAll();
        $trashed = $this->service->notificationContract
            ->where('user_id', '=', request()->user()->id)
            ->onlyTrashed();

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
