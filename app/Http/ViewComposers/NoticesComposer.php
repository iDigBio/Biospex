<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use App\Repositories\Contracts\NoticeContract;

class NoticesComposer
{

    /**
     * @var NoticeContract
     */
    private $noticeContract;

    /**
     * Create a new profile composer.
     *
     * @param NoticeContract $noticeContract
     */
    public function __construct(NoticeContract $noticeContract)
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
        $notices = $this->noticeContract->where('enabled', '=', 1)->findAll();
        $notices = $notices->isEmpty() ? null : $notices;

        $view->with('notices', $notices);
    }
}