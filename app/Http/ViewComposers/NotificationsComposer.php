<?php

namespace App\Http\ViewComposers;

use App\Repositories\Contracts\NotificationContract;
use Illuminate\Contracts\View\View;

class NotificationsComposer
{

    /**
     * @var NotificationContract
     */
    public $notificationContract;

    /**
     * Create a new profile composer.
     *
     * @param NotificationContract $notificationContract
     */
    public function __construct(NotificationContract $notificationContract)
    {
        $this->notificationContract = $notificationContract;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $notifications = $this->notificationContract->where('user_id', '=', auth()->id())->findAll();
        $notifications = $notifications->isEmpty() ? null : $notifications;

        $view->with('notifications', $notifications);
    }
}