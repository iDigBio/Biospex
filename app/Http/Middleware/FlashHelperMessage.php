<?php

namespace App\Http\Middleware;

use Closure;
use JavaScript;

class FlashHelperMessage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $message = [
            'flashType' => '',
            'flashMessage' => '',
            'flashIcon' => '',
        ];

        if ($request->session()->has('status')) {
            $message['flashType'] = 'success';
            $message['flashMessage'] = session('status');
            $message['flashIcon'] = 'ok';
        }

        if ($request->session()->has('flash_message')) {
            $message['flashType'] = session('flash_message.type');
            $message['flashMessage'] = session('flash_message.message');
            $message['flashIcon'] = session('flash_message.icon');
        }

        JavaScript::put($message);

        return $next($request);
    }
}
