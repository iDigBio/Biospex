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

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wedigbio_event_transcriptions', function (\Illuminate\Database\Schema\Blueprint $table) {
            DB::statement('ALTER TABLE `wedigbio_event_transcriptions` CHANGE `date_id` `event_id` BIGINT UNSIGNED NOT NULL;');
            DB::statement('ALTER TABLE `wedigbio_event_transcriptions` DROP INDEX `wedigbio_event_transcriptions_date_id_foreign`, ADD INDEX `wedigbio_event_transcriptions_event_id_foreign` (`event_id`) USING BTREE;');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
