<?php

namespace App\Services\Helpers;

class FlashHelper
{

    /**
     * Private function used to create flash messages.
     *
     * @param $message
     * @param $type
     * @param $icon
     */
    private function create($message, $type, $icon)
    {
        session()->flash('flash_message', [
            'type'    => $type,
            'message' => $message,
            'icon'    => $icon
        ]);
    }

    /**
     * Create success message.
     *
     * @param $message
     */
    public function success($message)
    {
        $this->create($message, 'success', 'ok');
    }

    /**
     * Create info message.
     *
     * @param $message
     */
    public function info($message)
    {
        $this->create($message, 'info', 'info-sign');
    }

    /**
     * Create warning message.
     *
     * @param $message
     */
    public function warning($message)
    {
        $this->create($message, 'warning', 'warning-sign');
    }

    /**
     * Create danger message.
     *
     * @param $message
     */
    public function error($message)
    {
        $this->create($message, 'danger', 'fire');
    }
}