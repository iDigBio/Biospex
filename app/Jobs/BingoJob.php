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

namespace App\Jobs;

use App\Events\BingoEvent;
use App\Models\Bingo;
use App\Models\BingoMap;
use App\Models\User;
use App\Notifications\Generic;
use App\Services\Bingo\BingoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Class BingoJob
 */
class BingoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * BingoJob constructor.
     */
    public function __construct(protected Bingo $bingo, protected ?BingoMap $bingoMap = null)
    {
        $this->bingo = $bingo->withoutRelations();
        $this->bingoMap = $bingoMap?->withoutRelations();
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Job handle.
     */
    public function handle(BingoService $bingoService): void
    {
        $locations = $bingoService->getMapLocations($this->bingo->id);
        $data['markers'] = $locations->map(function ($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city,
            ];
        })->toArray();

        $data['winner'] = null;
        if (! is_null($this->bingoMap)) {
            $data['winner']['city'] = $this->bingoMap->city;
            $data['winner']['uuid'] = $this->bingoMap->uuid;
        }

        BingoEvent::dispatch($this->bingo, $data);
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $throwable): void
    {
        $attributes = [
            'subject' => t('Bingo Job Failed'),
            'html' => [
                t('File: %s', $throwable->getFile()),
                t('Line: %s', $throwable->getLine()),
                t('Message: %s', $throwable->getMessage()),
            ],
        ];

        $user = User::find(config('config.admin.user_id'));
        $user->notify(new Generic($attributes));
    }
}
