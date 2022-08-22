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
 * Class NfnExportComplete
 *
 * @package App\Notifications
 */
class NfnExportComplete extends Notification implements ShouldQueue
{

    use Queueable;

    /**
     * @var string
     */
    private $title;

    /**
     * @var null
     */
    private $fileName;

    /**
     * Create a new notification instance.
     *
     * @param string $title
     * @param null $fileName
     */
    public function __construct(string $title, $fileName = null)
    {
        $this->title = $title;
        $this->fileName = $fileName;
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
        $mailMessage = new MailMessage;

        $attributes = [
            'title' => $this->title,
            'url' => isset($this->fileName) ? route('admin.downloads.report', ['file' => $this->fileName]) : null,
        ];

        return $mailMessage->subject(t('BIOSPEX Export Completed'))->markdown('mail.nfnexportcomplete', $attributes);
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
