<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmchartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('amcharts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index('amcharts_project_id_foreign');
            $table->json('series')->nullable();
            $table->json('data')->nullable();
            $table->tinyInteger('queued')->default(0)->index();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amcharts');
    }
}
