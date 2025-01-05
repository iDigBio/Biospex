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
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\Generic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckLambdaReconcileCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-lambda-reconcile';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $files = Storage::disk('s3')->files(config('zooniverse.directory.lambda-reconciliation'));
        $fileNames = array_map(function ($file) {
            return basename($file);
        }, $files);

        foreach ($fileNames as $fileName) {
            Storage::disk('s3')->delete(config('zooniverse.directory.lambda-reconciliation').'/'.$fileName);
            $classification = config('zooniverse.directory.classification').'/'.$fileName;
            $lambda_reconciliation = config('zooniverse.directory.lambda-reconciliation').'/'.$fileName;
            Storage::disk('s3')->copy($classification, $lambda_reconciliation);
        }

        if (count($fileNames) > 0) {
            $attributes = [
                'subject' => t('Lambda Reconciliation Check'),
                'html' => [
                    t('Error: %s file(s) were found in the label-reconciliation folder.', count($fileNames)),
                    t('Expedition Ids: %s', implode(', ', $fileNames)),
                ],
            ];
            $user = User::find(config('config.admin.user_id'));
            $user->notify(new Generic($attributes, true));
        }
    }
}
