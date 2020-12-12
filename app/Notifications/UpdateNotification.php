<?php
/**
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

class UpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var int
     */
    private $recordsUpdated;

    /**
     * @var array
     */
    private $fields;

    /**
     * @var string|null
     */
    private $downloadUrl;

    /**
     * ImportNotification constructor.
     *
     * @param string $fileName
     * @param int $recordsUpdated
     * @param array $fields
     * @param string|null $downloadUrl
     */
    public function __construct(string $fileName, int $recordsUpdated, array $fields, string $downloadUrl = null)
    {
        $this->onQueue(config('config.rapid_tube'));
        $this->fileName = $fileName;
        $this->recordsUpdated = $recordsUpdated;
        $this->fields = implode(', ', $fields);
        $this->downloadUrl = $downloadUrl;
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
     *
     * @param $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $attributes = [
            'fileName'       => $this->fileName,
            'recordsUpdated' => $this->recordsUpdated,
            'fields'         => $this->fields,
            'downloadUrl'    => $this->downloadUrl,
        ];

        return (new MailMessage)->markdown('mail.update-notification', $attributes);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray()
    {
        return [//
        ];
    }
}
