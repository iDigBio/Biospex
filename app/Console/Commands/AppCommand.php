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
use App\Notifications\ExportNotification;
use App\Notifications\JobErrorNotification;
use App\Services\Export\RapidExportService;
use DB;
use Exception;
use Illuminate\Console\Command;

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
     * @var \App\Services\Export\RapidExportService
     */
    private $rapidExportService;

    /**
     * AppCommand constructor.
     *
     * @param \App\Services\Export\RapidExportService $rapidExportService
     */
    public function __construct(RapidExportService $rapidExportService)
    {
        parent::__construct();
        $this->rapidExportService = $rapidExportService;
    }

    /**
     * Handle command.
     */
    public function handle()
    {

        DB::beginTransaction();

        $user = User::find(1);
        $data = json_decode(\Storage::get('generic.json'), true);

        try {
            $fields = isset($data['exportFields']) ?
                $this->rapidExportService->mapExportFields($data) :
                $this->rapidExportService->mapDirectFields($data);

            $form = $this->rapidExportService->saveForm($fields, $user->id);
            $this->rapidExportService->createFileName($form, $user, $fields);

            $downloadUrl = $this->rapidExportService->buildExport($fields);

            DB::commit();

            $user->notify(new ExportNotification($downloadUrl));

            return;

        } catch (Exception $exception) {
            DB::rollback();

            $attributes = [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ];

            $user->notify(new JobErrorNotification($attributes));
        }

    }
}