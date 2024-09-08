<?php
/*
 * Copyright (C) 2015  Biospex
 * biospex@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Services\Helpers;

/**
 * Class FlashHelper
 */
class FlashHelper
{
    /**
     * Private function used to create flash messages.
     */
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
