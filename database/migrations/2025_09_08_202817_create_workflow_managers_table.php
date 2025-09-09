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
        if (! Schema::hasTable('workflow_managers')) {
            Schema::create('workflow_managers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('expedition_id')->index('workflow_manager_expedition_id_foreign');
                $table->tinyInteger('stopped')->default(0)->index('workflow_manager_stopped_index');
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_managers');
    }
};
