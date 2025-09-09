<?php

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
        if (! Schema::hasTable('actor_workflow')) {
            Schema::create('actor_workflow', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('workflow_id')->index('actor_workflow_workflow_id_foreign');
                $table->unsignedBigInteger('actor_id')->index('actor_workflow_actor_id_foreign');
                $table->integer('order')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actor_workflow');
    }
};
