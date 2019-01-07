<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogoToExpeditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->string('logo_file_name')->nullable()->after('keywords');
            $table->integer('logo_file_size')->nullable()->after('logo_file_name');
            $table->string('logo_content_type')->nullable()->after('logo_file_size');
            $table->timestamp('logo_updated_at')->nullable()->after('logo_content_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expeditions', function (Blueprint $table) {
            $table->dropColumn('logo_file_name');
            $table->dropColumn('logo_file_size');
            $table->dropColumn('logo_content_type');
            $table->dropColumn('logo_updated_at');
        });
    }
}
