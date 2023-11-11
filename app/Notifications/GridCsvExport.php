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

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Class GridCsvExport
 *
 * @package App\Notifications
 */
class GridCsvExport extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $route;

    /**
     * @var int
     */
    private $projectId;

    /**
     * @var int
     */
    private $expeditionId;

    /**
     * GridCsvExport constructor.
     *
     * @param string $route
     * @param int $projectId
     * @param int $expeditionId
     */
    public function __construct(string $route, int $projectId, int $expeditionId = 0)
    {
        $this->route = $route;
        $this->projectId = $projectId;
        $this->expeditionId = $expeditionId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via()
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail()
    {
        return (new MailMessage)->markdown('mail.gridcsvexport', [
            'url' => $this->route,
            'projectId' => $this->projectId,
            'expeditionId' => $this->expeditionId
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            //
        ];
    }
}
