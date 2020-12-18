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
use App\Notifications\JobErrorNotification;
use App\Notifications\VersionNotification;
use App\Services\Model\RapidHeaderModelService;
use App\Services\Model\RapidVersionModelService;
use App\Services\RapidServiceBase;
use Illuminate\Console\Command;
use Storage;

/**
 * Class AppCommand
 *
 * @package App\Console\Commands
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'test:test {ids?}';

    /**
     * The console command description.
     */
    protected $description = 'Used to test code';

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @var string
     */
    private $versionFileName;

    /**
     * @var \App\Services\RapidServiceBase
     */
    private $rapidServiceBase;

    /**
     * @var \App\Services\Model\RapidVersionModelService
     */
    private $rapidVersionModelService;

    /**
     * @var \App\Services\Model\RapidHeaderModelService
     */
    private $rapidHeaderModelService;

    /**
     * AppCommand constructor.
     */
    public function __construct(
        RapidServiceBase $rapidServiceBase,
        RapidVersionModelService $rapidVersionModelService,
        RapidHeaderModelService $rapidHeaderModelService
    )
    {
        parent::__construct();
        $this->rapidServiceBase = $rapidServiceBase;
        $this->rapidVersionModelService = $rapidVersionModelService;
        $this->rapidHeaderModelService = $rapidHeaderModelService;
    }

    /**
     * Handle command.
     */
    public function handle()
    {
        if (! Storage::exists(config('config.rapid_version_dir'))) {
            Storage::makeDirectory(config('config.rapid_version_dir'));
        }

        $this->user = User::find(1);

        try {

            $versionFilePath = $this->rapidServiceBase->getVersionFilePath($this->versionFileName);
            $header = $this->rapidHeaderModelService->getLatestHeader();
            $this->rapidServiceBase->buildExportHeader($header->data);
            $exportHeaderPath = $this->rapidServiceBase->getExportHeaderFile();
            $dbHost = config('database.connections.mongodb.host');

            exec('mongoexport --host='.$dbHost.' --db=rapid --collection=rapid_records --type=csv --fieldFile='.$exportHeaderPath.' --out='.$versionFilePath, $output, $result_code);

            if (! $result_code) {
                throw new \Exception(t('Error in executing command to build version file %s', $this->versionFileName));
            }

            $size = $this->rapidServiceBase->getVersionFileSize($this->versionFileName);

            if (! $size) {
                throw new \Exception(t('Version file was empty for file %s', $this->versionFileName));
            }

            $this->rapidVersionModelService->create([
                'header_id' => $header->id,
                'user_id'   => $this->user->id,
                'file_name' => $this->versionFileName,
            ]);

            $this->rapidServiceBase->deleteExportHeaderFile();

            $downloadUrl = route('admin.download.version', [base64_encode($this->versionFileName)]);
            $this->user->notify(new VersionNotification($downloadUrl));
        } catch (\Exception $e) {
            $this->rapidServiceBase->deleteVersionFile($this->versionFileName);
            $this->rapidServiceBase->deleteExportHeaderFile();

            $attributes = [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ];

            $this->user->notify(new JobErrorNotification($attributes));
        }

    }
}