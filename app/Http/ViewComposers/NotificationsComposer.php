<?php
namespace App\Http\ViewComposers;

use App\Repositories\Contracts\Notification;
use Illuminate\Contracts\View\View;

class NotificationsComposer
{

    /**
     * @var Notification
     */
    public $notification;

    /**
     * Create a new profile composer.
     *
     * @param Notification $notification
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $notifications = $this->notification->where(['user_id' => auth()->id()])->get();
        $notifications = $notifications->isEmpty() ? null : $notifications;

        $view->with('notifications', $notifications);
    }
}