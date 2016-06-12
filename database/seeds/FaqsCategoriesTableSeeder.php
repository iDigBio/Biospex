<?php

use Illuminate\Database\Seeder;

class FaqsCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('faqs')->truncate();
        DB::table('faq_categories')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        factory(\App\Models\FaqCategory::class, 5)
            ->create()
            ->each(function($f) {
                $f->faqs()->saveMany(factory(\App\Models\Faq::class, 5)->make());
            });

    }
}
