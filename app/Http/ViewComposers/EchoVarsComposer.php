<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use JavaScript;

class EchoVarsComposer
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
            'groupUuids'    => json_encode(Session::get('groupUuids')),
            'ocrChannel'    => config('config.poll_ocr_channel'),
            'exportChannel' => config('config.poll_export_channel')
        ]);

        $view->with('process-modal');
    }
}