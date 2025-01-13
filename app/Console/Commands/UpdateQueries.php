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

use App\Models\Download;
use App\Models\GeoLocateDataSource;
use App\Models\GeoLocateForm;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Class UpdateQueries
 */
class UpdateQueries extends Command
{
    /**
     * The console command name.
     */
    protected $signature = 'app:update-queries';

    /**
     * The console command description.
     */
    protected $description = 'Used for custom queries when updating database';

    /**
     * UpdateQueries constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Fire command
     */
    public function handle()
    {
        $this->fixGeoLocateForms();

        \Artisan::call('zooniverse:count --update');
    }

    private function fixGeoLocateForms(): void
    {
        $records = GeoLocateDataSource::with('expedition')->get();
        $records->each(function ($record) {

            $form = GeoLocateForm::find($record->expedition->geo_locate_form_id);

            $download = Download::where('expedition_id', $record->expedition_id)
                ->where('type', $form->source)->first();

            $record->form_id = $form->id;
            $record->download_id = $download->id;
            $record->save();
        });

        Schema::table('expeditions', function (Blueprint $table) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            $table->dropForeign('expeditions_geo_locate_form_id_foreign');
            $table->dropColumn('geo_locate_form_id');
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        });

        Schema::table('geo_locate_forms', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
