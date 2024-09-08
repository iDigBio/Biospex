<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JavaScript;

class FlashHelperMessage
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $message = [
            'flashType' => '',
            'flashMessage' => '',
            'flashIcon' => '',
        ];

        $status = ['success', 'info', 'warning', 'danger'];
        if (session()->hasAny('success', 'info', 'warning', 'danger')) {
            foreach ($status as $type) {
                if (session()->has($type)) {
                    $message['flashType'] = $type;
                    $message['flashMessage'] = session($type);
                    $message['flashIcon'] = match ($type) {
                        'success' => 'check-circle',
                        'info' => 'info-circle',
                        'warning' => 'exclamation-circle',
                        'danger' => 'times-circle',
                    };
                    break;
                }
            }
        }

        JavaScript::put($message);

        return $next($request);
    }

    private function create($message, $type, $icon)
    {
        session()->flash('flash_message', [
            'type' => $type,
            'message' => $message,
            'icon' => $icon,
        ]);
    }

    /**
     * Create success message.
     */
    public function success($message)
    {
        $this->create($message, 'success', 'check-circle');
    }

    /**
     * Create info message.
     */
    public function info($message)
    {
        $this->create($message, 'info', 'info-circle');
    }

    /**
     * Create warning message.
     */
    public function warning($message)
    {
        $this->create($message, 'warning', 'exclamation-circle');
    }

    /**
     * Create danger message.
     */
    public function error($message)
    {
        $this->create($message, 'danger', 'times-circle');
    }
}
