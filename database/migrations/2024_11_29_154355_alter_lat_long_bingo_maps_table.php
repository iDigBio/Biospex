<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement('ALTER TABLE bingo_maps MODIFY COLUMN latitude DECIMAL(10, 8) NOT NULL');
        \DB::statement('ALTER TABLE bingo_maps MODIFY COLUMN longitude DECIMAL(11, 8) NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
