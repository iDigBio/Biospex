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

class ImportComplete extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var string
     */
    public $project;

    /**
     * @var string|null
     */
    public $duplicates;

    /**
     * @var string|null
     */
    public $rejects;

    /**
     * Create a new notification instance.
     *
     * @param $project
     * @param $duplicates
     * @param $rejects
     */
    public function __construct($project, $duplicates, $rejects)
    {
        $this->project = $project;
        $this->duplicates = $duplicates;
        $this->rejects = $rejects;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail()
    {
        $mailMessage = new MailMessage;
        $mailMessage->markdown('mail.importcomplete', ['project' => $this->project]);

        if (file_exists($this->duplicates))
        {
            $mailMessage->attach($this->duplicates, [
                'as' => 'duplicates.csv',
                'mime' => 'text/csv',
            ]);
        }

        if (file_exists($this->rejects))
        {
            $mailMessage->attach($this->rejects, [
                'as' => 'rejects.csv',
                'mime' => 'text/csv',
            ]);
        }

        return $mailMessage;
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
