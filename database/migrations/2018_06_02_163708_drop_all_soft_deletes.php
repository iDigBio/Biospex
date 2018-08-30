<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropAllSoftDeletes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $softDeletes = [
            'actors',
            'amcharts',
            'expeditions',
            'expedition_stats',
            'groups',
            'headers',
            'metas',
            'nfn_workflows',
            'notices',
            'projects',
            'resources',
            'transcription_locations',
            'users',
            'workflows',
            'workflow_managers'
        ];

        foreach ($softDeletes as $softDelete)
        {
            Schema::table($softDelete, function(Blueprint $table) use ($softDelete) {

                if (!Schema::hasColumn($softDelete, 'deleted_at')) {
                    $table->dropSoftDeletes();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
