<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('wedigbio_event_transcriptions', function (\Illuminate\Database\Schema\Blueprint $table) {
            DB::statement('ALTER TABLE `wedigbio_event_transcriptions` CHANGE `date_id` `event_id` BIGINT UNSIGNED NOT NULL;');
            DB::statement('ALTER TABLE `biospex`.`wedigbio_event_transcriptions` DROP INDEX `wedigbio_event_transcriptions_date_id_foreign`, ADD INDEX `wedigbio_event_transcriptions_event_id_foreign` (`event_id`) USING BTREE;');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
