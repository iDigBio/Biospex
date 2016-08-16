<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNfnWorkflowIdToNfnClassifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nfn_classifications', function (Blueprint $table) {
            $table->unsignedInteger('nfn_workflow_id')->after('id')->nullable();
            $table->foreign('nfn_workflow_id')->references('id')->on('nfn_workflows')->onDelete('cascade');
            $table->text('subjects')->after('classification_id');

            $table->dropForeign('nfn_classifications_project_id_foreign');
            $table->dropForeign('nfn_classifications_expedition_id_foreign');
            $table->dropIndex('nfn_classifications_project_id_finished_at_index');

            $table->index('finished_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nfn_classifications', function (Blueprint $table) {
            $table->dropForeign('nfn_classifications_nfn_workflow_id_foreign');
            $table->dropColumn('nfn_workflow_id');
        });
    }
}
