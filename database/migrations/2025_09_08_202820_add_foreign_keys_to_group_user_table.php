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
        if (Schema::hasTable('group_user')) {
            Schema::table('group_user', function (Blueprint $table) {
                $table->foreign(['group_id'], 'users_groups_group_id_foreign')->references(['id'])->on('groups')->onUpdate('cascade')->onDelete('cascade');
                $table->foreign(['user_id'], 'users_groups_user_id_foreign')->references(['id'])->on('users')->onUpdate('cascade')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('group_user')) {
            Schema::table('group_user', function (Blueprint $table) {
                $table->dropForeign('users_groups_group_id_foreign');
                $table->dropForeign('users_groups_user_id_foreign');
            });
        }
    }
};
