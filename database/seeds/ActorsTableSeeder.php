<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class ActorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $actors = $this->getActors();

        foreach ($actors as $actor) {
            App\Models\Actor::create($actor);
        }
    }

    public function getActors()
    {
        return [
            [
                'title' => "Notes From Nature",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNature",
            ]
        ];
    }
}
