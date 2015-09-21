<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropSubjectsExpeditionSubjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::drop('subjects');
        Schema::drop('expedition_subject');
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
        Schema::create('subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->string('mongo_id');
            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
        });

        Schema::create('expedition_subject', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('expedition_id');
            $table->foreign('expedition_id')->references('id')->on('expeditions')->onDelete('cascade');
            $table->unsignedInteger('subject_id');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
        });

        DB::connection('mongodb')->collection('subjectdocs')->unset('expedition_ids');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
