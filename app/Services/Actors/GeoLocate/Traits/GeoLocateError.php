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

namespace App\Services\Actors\GeoLocate\Traits;

use App\Notifications\GeoLocateStatsError;
use Illuminate\Support\Facades\Notification;
use Throwable;

trait GeoLocateError
{
    /**
     * Send error notification.
     *
     * @param \Throwable $exception
     * @return void
     */
    private function sendErrorNotification(Throwable $exception): void
    {
        $users = null;
        Notification::send($users, new GeoLocateStatsError());
    }
}