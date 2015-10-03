<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class PrepareForNewActorWorkflow extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Rename workflows table
        if (Schema::hasTable('workflows')) {
            Schema::rename('workflows', 'actors');
        }

        // Rename project_workflows
        if (Schema::hasTable('project_workflow')) {
            Schema::rename('project_workflow', 'project_actor');
        }

        // Create expedition_actor
        Schema::create('expedition_actor', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expedition_id');
            $table->unsignedInteger('actor_id');
            $table->tinyInteger('state')->default(0);
            $table->integer('completed')->default(0);
            $table->timestamps();

            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
            $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
        });

        // Add private to actors table
        if (! Schema::hasColumn('actors', 'private')) {
            Schema::table('actors', function (Blueprint $table) {
                $table->tinyInteger('private')->default(0)->after('class');;
            });
        }

        // Create proper indexes and keys on project_workflow
        if (Schema::hasTable('project_actor')) {
            Schema::table('project_actor', function (Blueprint $table) {
                $table->dropIndex('project_workflow_project_id_foreign');
                $table->dropForeign('project_workflow_project_id_foreign');
                $table->dropIndex('project_workflow_workflow_id_foreign');
                $table->dropForeign('project_workflow_workflow_id_foreign');
            });
            Schema::table('project_actor', function (Blueprint $table) {
                $table->renameColumn('workflow_id', 'actor_id');
                $table->tinyInteger('order_by')->index()->default(0);
                $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            });
        }

        // Rename workflow_id and create foreign key for actors
        if (Schema::hasTable('downloads')) {
            Schema::table('downloads', function (Blueprint $table) {
                $table->dropIndex('downloads_workflow_id_foreign');
                $table->dropForeign('downloads_workflow_id_foreign');
                $table->renameColumn('workflow_id', 'actor_id');
                $table->dropColumn('count');
            });
            // Work around to read count to downloads. Fails when in same migration
            Schema::table('downloads', function (Blueprint $table) {
                $table->foreign('actor_id')->references('id')->on('actors')->onDelete('cascade');
                $table->unsignedInteger('count')->default(0)->after('file');
            });
        }

        // Create proper indexes on workflow_manager and add columns
        if (Schema::hasTable('workflow_manager')) {
            Schema::table('workflow_manager', function (Blueprint $table) {
                $table->dropIndex('workflow_manager_workflow_id_foreign');
                $table->dropForeign('workflow_manager_workflow_id_foreign');
                $table->dropTimestamps();
                $table->dropSoftDeletes();
                $table->dropColumn('workflow_id');
                $table->tinyInteger('stopped')->index()->default(0);
                $table->tinyInteger('error')->index()->default(0);
            });
        }

        // Drop state, completed columns
        if (Schema::hasTable('expeditions')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->dropColumn(['state', 'completed']);
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Rename tables and drop expedition_actor
        if (Schema::hasTable('actors')) {
            Schema::rename('actors', 'workflows');
        }
        if (Schema::hasTable('project_actor')) {
            Schema::rename('project_actor', 'project_workflow');
        }
        if (Schema::hasTable('expedition_actor')) {
            Schema::drop('expedition_actor');
        }

        // Reconfigure
        if (Schema::hasTable('project_workflow')) {
            Schema::table('project_workflow', function (Blueprint $table) {
                $table->dropIndex('project_actor_actor_id_foreign');
                $table->dropForeign('project_actor_actor_id_foreign');
            });
            Schema::table('project_workflow', function (Blueprint $table) {
                $table->dropIndex('project_actor_project_id_foreign');
                $table->dropForeign('project_actor_project_id_foreign');
            });
            Schema::table('project_workflow', function (Blueprint $table) {
                $table->dropIndex('project_actor_order_by_index');
                $table->dropColumn('order_by');
                $table->renameColumn('actor_id', 'workflow_id');
            });
            Schema::table('project_workflow', function (Blueprint $table) {
                $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
                $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('workflow_manager')) {
            Schema::table('workflow_manager', function (Blueprint $table) {
                $table->dropIndex('workflow_manager_stopped_index');
                $table->dropIndex('workflow_manager_error_index');
                $table->dropColumn('stopped');
                $table->dropColumn('error');
                $table->unsignedInteger('workflow_id');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('downloads')) {
            Schema::table('downloads', function (Blueprint $table) {
                $table->dropIndex('downloads_actor_id_foreign');
                $table->dropForeign('downloads_actor_id_foreign');
                $table->renameColumn('actor_id', 'workflow_id');
            });
            Schema::table('downloads', function (Blueprint $table) {
                $table->foreign('workflow_id')->references('id')->on('workflows')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('expeditions')) {
            Schema::table('expeditions', function (Blueprint $table) {
                $table->tinyInteger('state')->default(0);
                $table->integer('completed')->default(0);
            });
        }

        if (Schema::hasTable('actors') && Schema::hasColumn('actors', 'private')) {
            Schema::table('actors', function (Blueprint $table) {
                $table->dropColumn('private');
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
