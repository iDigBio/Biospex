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
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;
use Laravel\Nova\Util;

class CreateActionEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('action_events', function (Blueprint $table) {
            $table->id();
            $table->char('batch_id', 36);
            $table->foreignIdFor(Util::userModel(), 'user_id')->index();
            $table->string('name');
            $table->morphs('actionable');
            $table->morphs('target');
            $table->string('model_type');

            if (Builder::$defaultMorphKeyType === 'uuid') {
                $table->uuid('model_id')->nullable();
            } elseif (Builder::$defaultMorphKeyType === 'ulid') {
                $table->ulid('model_id')->nullable();
            } else {
                $table->unsignedBigInteger('model_id')->nullable();
            }

            $table->text('fields');
            $table->string('status', 25)->default('running');
            $table->text('exception');
            $table->timestamps();

            $table->index(['batch_id', 'model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('action_events');
    }
}
