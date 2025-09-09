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
        if (! Schema::hasTable('actor_contacts')) {
            Schema::create('actor_contacts', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('actor_id')->index('actor_contacts_actor_id_foreign');
                $table->string('email', 255);
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
        Schema::dropIfExists('actor_contacts');
    }
};
