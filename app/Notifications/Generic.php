<?php

/*
 * Copyright (C) 2014 - 2025, Biospex
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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class Generic extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  array  $attributes  ['subject' => '', 'html' => '', 'buttons' => ['url' => ['color' => ''', 'text' => '']]]
     *                             color can be primary, success, and error
     */
    public function __construct(protected array $attributes, protected bool $admin = false)
    {
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = new MailMessage;

        $adminEmail = config('mail.from.address');
        if ($notifiable->email !== $adminEmail && $this->admin) {
            $mail->bcc($adminEmail);
        }

        $mail->subject($this->attributes['subject']);

        $this->attributes['message'] = implode('<br>', $this->attributes['html']);

        return $mail->markdown('mail.generic', $this->attributes);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [//
        ];
    }
}
