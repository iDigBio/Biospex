<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\TranscriptionLocation::class, function (Faker $faker) {
    return [
        'classification_id' => factory(App\Models\PanoptesTranscription::class),
        'project_id' => factory(App\Models\Project::class),
        'expedition_id' => factory(App\Models\Expedition::class),
        'state_county_id' => factory(App\Models\StateCounty::class),
    ];
});
