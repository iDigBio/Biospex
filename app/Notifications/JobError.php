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
 * Class JobError
 *
 * @package App\Notifications
 */
class JobError extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $messages;

    /**
     * @var
     */
    private $fileName;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */
    private $adminEmail;

    /**
     * Create a new notification instance.
     *
     * @param string $file
     * @param array $messages
     * @param string|null $fileName
     */
    public function __construct(string $file, array $messages = [],  string $fileName = null)
    {
        $this->messages = $messages;
        $this->file = $file;
        $this->fileName = $fileName;
        $this->adminEmail = config('mail.from.address');
        $this->onQueue(config('config.default_tube'));
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
        $message = implode('<br><br>', $this->messages);

        $mailMessage = new MailMessage;

        if($notifiable->email !== $this->adminEmail)
        {
            $mailMessage->bcc($this->adminEmail);
        }

        return $mailMessage->markdown('mail.joberror', [
            'file' => $this->file,
            'message' => $message,
            'url' => isset($this->fileName) ? route('admin.downloads.report', ['file' => $this->fileName]) : null
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
