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

namespace App\Jobs;

use App\Models\Download;
use App\Models\User;
use App\Notifications\Generic;
use App\Traits\NotifyOnJobFailure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ZooniverseExportBatchResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, NotifyOnJobFailure, Queueable, SerializesModels;

    public function __construct(protected array $data)
    {
        $this->onQueue(config('config.queue.default'));
    }

    public function handle(): void
    {
        $downloadId = $this->data['downloadId'] ?? throw new \InvalidArgumentException('Missing downloadId');
        $batchFiles = $this->data['batchFiles'] ?? throw new \InvalidArgumentException('Missing batchFiles');

        $download = Download::findOrFail($downloadId);

        $links = array_map(fn ($file) => $this->generateLink($file), $batchFiles);

        $attributes = [
            'subject' => t('Your Batched Export is Ready'),
            'html' => [
                t('Your export for Expedition "%s" has been split into %d batches.', $download->expedition->title, count($batchFiles)),
                t('Download links (expire in 72 hours):'),
                ...$links,
            ],
        ];

        $download->expedition->project->group->owner->notify(new Generic($attributes, true));

        // Stop the listener using the unified controller
        \Artisan::call('sqs:control batch_update --action=stop');

    }

    private function generateLink(string $file): string
    {
        $url = Storage::disk('s3')->temporaryUrl(
            config('config.batch_dir').'/'.$file,
            now()->addHours(72),
            ['ResponseContentDisposition' => 'attachment']
        );

        return "<a href=\"{$url}\">{$file}</a>";
    }

    public function failed(\Throwable $throwable): void
    {
        $downloadId = $this->data['downloadId'] ?? null;
        $download = $downloadId ? Download::find($downloadId) : null;

        if ($download) {
            $this->notifyGroupOnFailure($download, $throwable);
        } else {
            // Fallback
            $admin = User::find(config('config.admin.user_id'));
            $admin?->notify(new Generic([
                'subject' => t('Batch Export Job Failed'),
                'html' => [t('Error: %s', $throwable->getMessage())],
            ]));
        }
    }
}
