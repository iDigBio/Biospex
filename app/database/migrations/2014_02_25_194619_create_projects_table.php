<?php
/**
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateProjectsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('projects', function(Blueprint $table) {
			$table->increments('id');
            $table->unsignedInteger('group_id');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->index();
            $table->string('contact');
            $table->string('contact_email');
            $table->string('managed');
            $table->text('description');
            $table->text('goal');
            $table->text('circumscription');
            $table->text('strategy');
            $table->text('incentives');
            $table->string('geographic_scope');
            $table->string('taxonomic_scope');
            $table->string('temporal_scope');
            $table->string('keywords');
            $table->string('hashtag');
            $table->string('activities');
            $table->string('language_skills');
            $table->string("logo_file_name")->nullable();
            $table->integer("logo_file_size")->nullable();
            $table->string("logo_content_type")->nullable();
            $table->timestamp("logo_updated_at")->nullable();
            $table->string("banner_file_name")->nullable();
            $table->integer("banner_file_size")->nullable();
            $table->string("banner_content_type")->nullable();
            $table->timestamp("banner_updated_at")->nullable();
            $table->text('target_fields')->nullable();
			$table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('projects');
	}

}
