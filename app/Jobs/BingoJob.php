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
use App\Models\User;
use App\Notifications\JobError;
use App\Repositories\BingoMapRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class BingoJob
 *
 * @package App\Jobs
 */
class BingoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    private string $bingoId;

    /**
     * @var string|null
     */
    private ?string $mapId;

    /**
     * BingoJob constructor.
     *
     * @param string $bingoId
     * @param string|null $mapId
     */
    public function __construct(string $bingoId, string $mapId = null)
    {
        $this->bingoId = $bingoId;
        $this->mapId = $mapId;
        $this->onQueue(config('config.queue.default'));
    }

    /**
     * Job handle.
     *
     * @param \App\Repositories\BingoMapRepository $bingoMapRepo
     */
    public function handle(BingoMapRepository $bingoMapRepo): void
    {
        $locations = $bingoMapRepo->getBy('bingo_id', $this->bingoId);
        $data['markers'] = $locations->map(function($location) {
            return [
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'city' => $location->city
            ];
        })->toArray();

        $data['winner'] = null;
        if ($this->mapId !== null) {
            $map = $bingoMapRepo->find($this->mapId);
            $data['winner']['city'] = $map->city;
            $data['winner']['uuid'] = $map->uuid;
        }


        BingoEvent::dispatch($this->bingoId, $data);
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $throwable
     * @return void
     */
    public function failed(\Throwable $throwable): void
    {
        $user = User::find((int) config('config.admin.user_id'));
        $messages = [
            t('Error: %s', $throwable->getMessage()),
            t('File: %s', $throwable->getFile()),
            t('Line: %s', $throwable->getLine()),
        ];
        $user->notify(new JobError(__FILE__, $messages));
    }
}
