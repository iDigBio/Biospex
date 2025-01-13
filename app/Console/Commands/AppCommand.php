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

use App\Models\GeoLocateCommunity;
use App\Models\GeoLocateDataSource;
use Illuminate\Console\Command;

/**
 * Class AppCommand
 */
class AppCommand extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:test';

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
        $records = GeoLocateDataSource::with('geoLocateCommunity')->get();
        $records->each(function ($record) {
            $this->info($record->geoLocateCommunity->name);
        });

        $records = GeoLocateCommunity::with('geoLocateDataSources')->find(1);
        $records->geoLocateDataSources->each(function ($source) {
            $this->info($source->data_source);
        });

        /*
        $records = GeoLocateDataSource::with('expedition')->get();
        $records->each(function ($record) {

            $form = GeoLocateForm::find($record->expedition->geo_locate_form_id);

            $download = Download::where('expedition_id', $record->expedition_id)
                ->where('type', $form->source)->first();

            $record->form_id = $form->id;
            $record->download_id = $download->id;
            $record->save();
        });
        */
    }
}
