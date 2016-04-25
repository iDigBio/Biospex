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

        foreach ($actors as $actor)
        {
            App\Models\Actor::create($actor);
        }
    }

    public function getActors()
    {
        return [
            [
                'title' => "Notes From Nature Original",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNatureOrig",
            ],
            [
                'title' => "Notes From Nature Manifest",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNatureManifest",
            ],
            [
                'title' => "Notes From Nature CSV",
                'url'   => "http://www.notesfromnature.org/",
                'class' => "NotesFromNatureCsv",
            ],
            [
                'title'   => "OCR",
                'url'     => "http://ocr.idiginfo.org",
                'class'   => "Ocr",
                'private' => 1
            ]
        ];
    }
}
