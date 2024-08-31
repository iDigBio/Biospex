<?php

namespace App\Http\Controllers\Front;

use Illuminate\Contracts\Console\Kernel as Artisan;
use Illuminate\Http\Request;

class PollController
{
    public function __construct(protected Artisan $artisan, protected Request $request)
    {}

    /**
     * Call polling command when process modal opened. Trigger inside biospex.js
     */
    public function index()
    {
        if ($this->request->ajax()) {
            $this->artisan->call('ocr:poll');
            $this->artisan->call('export:poll');
        }
    }
}
