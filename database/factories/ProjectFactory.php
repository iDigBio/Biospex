<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

$factory->define(App\Models\Project::class, function (Faker $faker) {
    return [
        'uuid' => $faker->uuid,
        'group_id' => factory(App\Models\Group::class),
        'title' => $faker->word,
        'slug' => $faker->slug,
        'contact' => $faker->word,
        'contact_email' => $faker->word,
        'contact_title' => $faker->word,
        'organization_website' => $faker->word,
        'organization' => $faker->word,
        'project_partners' => $faker->text,
        'funding_source' => $faker->text,
        'description_short' => $faker->word,
        'description_long' => $faker->text,
        'incentives' => $faker->text,
        'geographic_scope' => $faker->word,
        'taxonomic_scope' => $faker->word,
        'temporal_scope' => $faker->word,
        'keywords' => $faker->word,
        'blog_url' => $faker->word,
        'facebook' => $faker->word,
        'twitter' => $faker->word,
        'activities' => $faker->word,
        'language_skills' => $faker->word,
        'workflow_id' => factory(App\Models\Workflow::class),
        'logo_file_name' => $faker->word,
        'logo_file_size' => $faker->randomNumber(),
        'logo_content_type' => $faker->word,
        'logo_updated_at' => $faker->dateTime(),
        'banner_file' => $faker->word,
        'target_fields' => $faker->text,
        'status' => $faker->word,
        'advertise' => $faker->word,
    ];
});
