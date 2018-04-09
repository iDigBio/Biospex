<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use App\Repositories\Interfaces\Notice;

class NoticesComposer
{

    /**
     * @var Notice
     */
    private $noticeContract;

    /**
     * Create a new profile composer.
     *
     * @param Notice $noticeContract
     */
    public function __construct(Notice $noticeContract)
    {
        $this->noticeContract = $noticeContract;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $notices = $this->noticeContract->getEnabledNotices();
        $notices = $notices->isEmpty() ? null : $notices;

        $view->with('notices', $notices);
    }
}