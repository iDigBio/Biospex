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
        if (! Schema::hasTable('profiles')) {
            Schema::create('profiles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->index('profiles_user_id_foreign');
                $table->string('first_name', 50);
                $table->string('last_name', 50);
                $table->string('timezone', 255);
                $table->timestamp('created_at')->nullable()->useCurrent();
                $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
                $table->string('avatar_file_name', 255)->nullable();
                $table->integer('avatar_file_size')->nullable();
                $table->string('avatar_content_type', 255)->nullable();
                $table->timestamp('avatar_updated_at')->nullable();
                $table->timestamp('avatar_created_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
