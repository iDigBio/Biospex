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
        Schema::create('state_counties', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('county_name', 255)->nullable();
            $table->string('state_county', 255)->nullable()->index();
            $table->string('state_abbr', 255)->nullable();
            $table->string('state_abbr_cap', 255)->nullable();
            $table->text('geometry')->nullable();
            $table->string('value', 255)->nullable();
            $table->string('geo_id', 255)->nullable();
            $table->string('geo_id_2', 255)->nullable();
            $table->string('geographic_name', 255)->nullable();
            $table->string('state_num', 255)->nullable()->index('state_num');
            $table->string('county_num', 255)->nullable();
            $table->string('fips_forumla', 255)->nullable();
            $table->string('has_error', 255)->nullable();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('state_counties');
    }
};
