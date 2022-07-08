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

namespace App\Services\Actor\NfnPanoptes;

use App\Models\Actor;
use App\Models\Expedition;
use App\Models\ExportQueue;
use App\Models\User;
use App\Services\Actor\ActorDirectory;
use App\Services\Csv\Csv;
use Illuminate\Support\Facades\File;
use function config;

/**
 * Class ZooniverseBase
 *
 * @package App\Services\Actor
 */
class ZooniverseBase
{
    /**
     * @var \App\Services\Actor\NfnPanoptes\ZooniverseDbService
     */
    protected ZooniverseDbService $dbService;

    /**
     * @var \App\Services\Actor\ActorDirectory
     */
    protected ActorDirectory $actorDirectory;

    /**
     * @var \App\Services\Csv\Csv
     */
    protected Csv $csv;

    /**
     * @var ExportQueue
     */
    protected ExportQueue $queue;

    /**
     * @var \App\Models\Expedition
     */
    protected Expedition $expedition;

    /**
     * @var \App\Models\Actor
     */
    protected Actor $actor;

    /**
     * @var \App\Models\User
     */
    protected User $owner;

    /**
     * @var array
     */
    protected array $rejected = [];

    /**
     * @var array
     */
    protected array $nfnCsvMap = [];

    /**
     * @var string
     */
    protected string $nfnImageWidth;

    /**
     * @var string
     */
    protected string $nfnImageHeight;

    /**
     * ZooniverseConvertImage constructor.
     *
     * @param \App\Services\Actor\NfnPanoptes\ZooniverseDbService $dbService
     * @param \App\Services\Actor\ActorDirectory $actorDirectory
     * @param \App\Services\Csv\Csv $csv
     */
    public function __construct(
        ZooniverseDbService $dbService,
        ActorDirectory $actorDirectory,
        Csv $csv
    )
    {
        $this->dbService = $dbService;
        $this->actorDirectory = $actorDirectory;
        $this->csv = $csv;
        $this->nfnImageWidth = config('config.nfn_image_width');
        $this->nfnImageHeight = config('config.nfn_image_height');
    }

    /**
     * Set queue property.
     *
     * @param \App\Models\ExportQueue $queue
     */
    public function setQueue(ExportQueue $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Set expedition property.
     *
     * @param \App\Models\Expedition $expedition
     */
    public function setExpedition(Expedition $expedition)
    {
        $this->expedition = $expedition;
    }

    /**
     * Set actor property.
     *
     * @param \App\Models\Actor $actor
     */
    public function setActor(Actor $actor)
    {
        $this->actor = $actor;
    }

    /**
     * Set owner property.
     *
     * @param \App\Models\User $user
     */
    public function setOwner(User $user)
    {
        $this->owner = $user;
    }

    /**
     * Set nfnCsvMap.
     *
     * @return void
     */
    protected function setNfnCsvMap()
    {
        $this->nfnCsvMap = config('config.nfnCsvMap');
    }

    /**
     * Check if file exists and is image.
     *
     * @param string $filePath
     * @param string $subjectId
     * @return bool
     */
    public function checkFile(string $filePath, string $subjectId): bool
    {
        if (!File::exists($filePath)) {
            $this->rejected[$subjectId] = 'Image was not downloaded and converted.';

            return false;
        }

        if (File::exists($filePath) && false === exif_imagetype($filePath)) {
            $this->rejected[$subjectId] = 'Converted image file was corrupt.';
            return false;
        }

        return true;
    }
}