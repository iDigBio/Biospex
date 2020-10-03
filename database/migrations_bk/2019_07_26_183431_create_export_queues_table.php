<?php
/**
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateExportQueuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('export_queues', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('expedition_id')->unsigned();
			$table->integer('actor_id')->unsigned()->index('export_queues_actor_id_foreign');
			$table->integer('stage')->default(0)->index();
			$table->boolean('queued')->default(0)->index();
			$table->boolean('error')->default(0)->index();
			$table->text('missing')->nullable();
			$table->timestamps();
			$table->unique(['expedition_id','actor_id','stage']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('export_queues');
	}

}
