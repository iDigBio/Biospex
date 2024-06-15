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

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Generic;
use App\Notifications\Traits\ButtonTrait;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    use ButtonTrait;
    /**
     * The console command name.
     */
    protected $signature = 'test:test';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        $dupButton = $this->createButton('dupurl', t('View Duplicate Records'));
        $rejButton = $this->createButton('rejUrl', t('View Rejected Records'), 'error');

        $buttons = array_merge([$dupButton, $rejButton]);

        $attributes = [
            'subject' => t('DWC File Import Complete'),
            'html'    => [
                t('The subject import for %s has been completed.', 'Project Title'),
                t('OCR processing may take longer and you will receive an email when it is complete.')
            ],
            'buttons' => $buttons
        ];

        $user = User::find(1);
        $user->notify(new Generic($attributes));
    }

}