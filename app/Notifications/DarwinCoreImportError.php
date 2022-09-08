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
 * Class DarwinCoreImportError
 *
 * @package App\Notifications
 */
class DarwinCoreImportError extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $adminEmail;

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $identifier;

    /**
     * @var string
     */
    private $message;

    /**
     * DarwinCoreImportError constructor.
     *
     * @param string $title
     * @param int $identifier
     * @param array $message
     */
    public function __construct(string $title, int $identifier, array $message)
    {
        $this->title = $title;
        $this->identifier = $identifier;
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
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $message = implode('<br>', $this->message);

        $attributes = [
            'title' => $this->title,
            'id' => $this->identifier,
            'message' => $message
        ];

        return (new MailMessage)
            ->bcc($this->adminEmail)
            ->markdown('mail.importerror', $attributes);
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
