<?php

use App\Models\Bingo;
use App\Models\BingoMap;
use App\Models\BingoWord;
use Illuminate\Database\Seeder;

class BingosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('TRUNCATE TABLE `bingos`');
        DB::statement('TRUNCATE TABLE `bingo_words`');
        DB::statement('TRUNCATE TABLE `bingo_maps`');

        factory(Bingo::class, 3)->create()->each(function ($bingo) {
            factory(BingoWord::class, 24)->create(['bingo_id'=>$bingo->id]);
            factory(BingoMap::class, 10)->create(['bingo_id'=>$bingo->id]);
        });
    }
}