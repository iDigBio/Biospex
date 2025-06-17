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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('geo_locate_data_sources', function (Blueprint $table) {
            $table->unsignedBigInteger('geo_locate_form_id')->index('geo_locate_data_sources_geo_locate_form_id_foreign')->nullable()->after('expedition_id');
            $table->foreign(['geo_locate_form_id'], 'geo_locate_data_sources_geo_locate_form_id_foreign')->references(['id'])->on('geo_locate_forms')->onUpdate('cascade')->onDelete('cascade');

            $table->unsignedBigInteger('download_id')->index('geo_locate_data_sources_download_id_foreign')->nullable()->after('geo_locate_community_id');
            $table->foreign(['download_id'], 'geo_locate_data_sources_download_id_foreign')->references(['id'])->on('downloads')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_locate_data_sources', function (Blueprint $table) {
            $table->dropForeign('geo_locate_data_sources_download_id_foreign');
            $table->dropColumn('download_id');
            $table->dropForeign('geo_locate_data_sources_geo_locate_form_id_foreign');
            $table->dropColumn('geo_locate_form_id');
        });
    }
};
