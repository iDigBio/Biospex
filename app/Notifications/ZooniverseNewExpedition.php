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

use App\Models\Expedition;
use App\Models\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Class ZooniverseNewExpedition
 */
class ZooniverseNewExpedition extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Project $project, protected Expedition $expedition)
    {
        $this->onQueue(config('config.queue.default'));
        $this->project = $project->withoutRelations();
        $this->expedition = $expedition->withoutRelations();
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
        $vars = [
            'contact' => $this->project->contact,
            'email' => $this->project->contact_email,
            'projectTitle' => $this->project->title,
            'expeditionTitle' => $this->expedition->title,
            'expeditionDescription' => $this->expedition->description,
        ];

        return (new MailMessage)->subject(t('Biospex - New Zooniverse Expedition Submitted'))->markdown('mail.newzooniverse', $vars);
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
