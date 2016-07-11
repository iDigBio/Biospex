<?php
namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use App\Repositories\Contracts\Notice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoticesComposer
{

    /**
     * @var Notice
     */
    private $notice;

    /**
     * Create a new profile composer.
     *
     * @param Notice $notice
     */
    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $notices = $this->notice->where(['enabled' => 1])->get();
        $notices = $notices->isEmpty() ? null : $notices;

        $view->with('notices', $notices);
    }
}