<?php

class ActorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

        $actors = $this->getActors();

        foreach ($actors as $actor) {
            Actor::create($actor);
        }
    }

    public function getActors()
    {
        return [
            [
                'title' => "Notes From Nature",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNature",
            ],
            [
                'title' => "Notes From Nature II",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNature2",
            ],
            [
                'title' => "OCR",
                'url'   => "http://ocr.idiginfo.org/",
                'class' => "Ocr",
                'private' => 1
            ]
        ];
    }
}
