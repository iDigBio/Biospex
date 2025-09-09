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
        if (Schema::hasTable('group_invites')) {
            Schema::table('group_invites', function (Blueprint $table) {
                $table->foreign(['group_id'], 'invites_group_id_foreign')->references(['id'])->on('groups')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('group_invites')) {
            Schema::table('group_invites', function (Blueprint $table) {
                $table->dropForeign('invites_group_id_foreign');
            });
        }
    }
};
