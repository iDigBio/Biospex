<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JavaScript;

class FlashHelperMessage
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $message = [
            'flashType'    => '',
            'flashMessage' => '',
            'flashIcon'    => ''
        ];

        if (session()->has('flash_message'))
        {
            $message['flashType'] = session('flash_message')['type'];
            $message['flashMessage'] = session('flash_message')['message'];
            $message['flashIcon'] = session('flash_message')['icon'];
        }

        JavaScript::put($message);

        return $next($request);
    }
}
