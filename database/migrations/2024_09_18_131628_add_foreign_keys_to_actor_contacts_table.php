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
        Schema::table('actor_contacts', function (Blueprint $table) {
            $table->foreign(['actor_id'])->references(['id'])->on('actors')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('actor_contacts', function (Blueprint $table) {
            $table->dropForeign('actor_contacts_actor_id_foreign');
        });
    }
};
