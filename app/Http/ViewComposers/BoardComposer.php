<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use JavaScript;

class BoardComposer
{

    /**
     * Bind data to the view.
     *
     * @param  View $view
     * @return void
     */
    public function compose(View $view)
    {
        JavaScript::put([
            'boardChannel' => config('config.poll_board_channel')
        ]);

        $view->with('board');
    }
}