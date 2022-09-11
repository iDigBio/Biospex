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
 * Class GridCsvExportError
 *
 * @package App\Notifications
 */
class GridCsvExportError extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var array
     */
    public $message;

    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    private $adminEmail;

    /**
     * GridCsvExport constructor.
     *
     * @param array $message
     */
    public function __construct(array $message)
    {
        $this->message = $message;
        $this->adminEmail = config('mail.from.address');
        $this->onQueue(config('config.queues.default'));
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
        $message = implode('<br>', $this->message);
        return (new MailMessage)->bcc($this->adminEmail)->markdown('mail.gridcsvexporterror', ['message' => $message]);
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
