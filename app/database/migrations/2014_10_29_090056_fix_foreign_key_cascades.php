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

class FixForeignKeyCascades extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up ()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		Schema::table('groups', function (Blueprint $table)
		{
			$table->dropIndex('groups_user_id_foreign');
			$table->dropForeign('groups_user_id_foreign');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('users_groups', function (Blueprint $table)
		{
			$table->dropIndex('users_groups_group_id_foreign');
			$table->dropForeign('users_groups_group_id_foreign');
			$table->dropForeign('users_groups_user_id_foreign');
			$table->dropPrimary(['user_id', 'group_id']);
			$table->index('user_id');
			$table->index('group_id');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
			$table->primary(array('user_id', 'group_id'));
		});

		Schema::table('projects', function (Blueprint $table)
		{
			$table->dropIndex('projects_group_id_foreign');
			$table->dropForeign('projects_group_id_foreign');
			$table->index('group_id');
			$table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('imports', function (Blueprint $table)
		{
			$table->dropForeign('imports_project_id_foreign');
			$table->dropForeign('imports_user_id_foreign');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('headers', function (Blueprint $table)
		{
			$table->dropIndex('headers_project_id_foreign');
			$table->dropForeign('headers_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('metas', function (Blueprint $table)
		{
			$table->dropIndex('metas_project_id_foreign');
			$table->dropForeign('metas_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('subjects', function (Blueprint $table)
		{
			$table->dropForeign('subjects_meta_id_foreign');
			$table->dropForeign('subjects_header_id_foreign');
			$table->dropForeign('subjects_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('header_id')->references('id')->on('headers')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('meta_id')->references('id')->on('metas')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('expeditions', function (Blueprint $table)
		{
			$table->dropForeign('expeditions_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropForeign('expedition_subject_subject_id_foreign');
			$table->dropForeign('expedition_subject_expedition_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('subject_id')->references('id')->on('subjects')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('project_workflow', function (Blueprint $table)
		{
			$table->dropForeign('project_workflow_workflow_id_foreign');
			$table->dropForeign('project_workflow_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('workflow_manager', function (Blueprint $table)
		{
			$table->dropForeign('workflow_manager_expedition_id_foreign');
			$table->dropForeign('workflow_manager_workflow_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('downloads', function (Blueprint $table)
		{
			$table->dropForeign('downloads_workflow_id_foreign');
			$table->dropForeign('downloads_expedition_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onUpdate('cascade')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onUpdate('cascade')->onDelete('cascade');
		});

		Schema::table('invites', function (Blueprint $table)
		{
			$table->dropForeign('invites_group_id_foreign');
			$table->foreign('group_id')->references('id')->on('groups')->onUpdate('cascade')->onDelete('cascade');
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down ()
	{
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');

		Schema::table('groups', function (Blueprint $table)
		{
			$table->dropForeign('groups_user_id_foreign');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
		});

		Schema::table('users_groups', function (Blueprint $table)
		{
			$table->dropIndex('users_groups_user_id_index');
			$table->dropForeign('users_groups_user_id_foreign');
			$table->dropIndex('users_groups_group_id_index');
			$table->dropForeign('users_groups_group_id_foreign');
			$table->dropPrimary(['user_id', 'group_id']);
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
			$table->primary(array('user_id', 'group_id'));
		});

		Schema::table('projects', function (Blueprint $table)
		{
			$table->dropIndex('projects_group_id_index');
			$table->dropForeign('projects_group_id_foreign');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		Schema::table('imports', function (Blueprint $table)
		{
			$table->dropForeign('imports_project_id_foreign');
			$table->dropForeign('imports_user_id_foreign');
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		Schema::table('headers', function (Blueprint $table)
		{
			$table->dropIndex('headers_project_id_foreign');
			$table->dropForeign('headers_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		Schema::table('metas', function (Blueprint $table)
		{
			$table->dropIndex('metas_project_id_foreign');
			$table->dropForeign('metas_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		Schema::table('subjects', function (Blueprint $table)
		{
			$table->dropForeign('subjects_meta_id_foreign');
			$table->dropForeign('subjects_header_id_foreign');
			$table->dropForeign('subjects_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('header_id')->references('id')->on('headers')->onDelete('cascade');
			$table->foreign('meta_id')->references('id')->on('metas')->onDelete('cascade');
		});

		Schema::table('expeditions', function (Blueprint $table)
		{
			$table->dropForeign('expeditions_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
		});

		Schema::table('expedition_subject', function (Blueprint $table)
		{
			$table->dropForeign('expedition_subject_subject_id_foreign');
			$table->dropForeign('expedition_subject_expedition_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
		});

		Schema::table('project_workflow', function (Blueprint $table)
		{
			$table->dropForeign('project_workflow_workflow_id_foreign');
			$table->dropForeign('project_workflow_project_id_foreign');
			$table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});

		Schema::table('workflow_manager', function (Blueprint $table)
		{
			$table->dropForeign('workflow_manager_expedition_id_foreign');
			$table->dropForeign('workflow_manager_workflow_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});

		Schema::table('downloads', function (Blueprint $table)
		{
			$table->dropForeign('downloads_workflow_id_foreign');
			$table->dropForeign('downloads_expedition_id_foreign');
			$table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
			$table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
		});

		Schema::table('invites', function (Blueprint $table)
		{
			$table->dropForeign('invites_group_id_foreign');
			$table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
		});

		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
	}

}
