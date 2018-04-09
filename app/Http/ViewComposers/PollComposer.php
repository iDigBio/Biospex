<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use JavaScript;

class PollComposer
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
            'groupIds'      => json_encode(Session::get('groupIds')),
            'ocrChannel'    => config('config.poll_ocr_channel'),
            'exportChannel' => config('config.poll_export_channel')
        ]);

        $view->with('process-modal');
    }
}