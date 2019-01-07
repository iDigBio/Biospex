<?php

use Illuminate\Database\Seeder;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('TRUNCATE TABLE `events`');
        DB::statement('TRUNCATE TABLE `event_teams`');
        DB::statement('TRUNCATE TABLE `event_team_user`');
        DB::statement('TRUNCATE TABLE `event_transcriptions`');
        DB::statement('TRUNCATE TABLE `event_users`');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');

        factory(\App\Models\Event::class, 5)->create()->each(function ($event) {
            factory(\App\Models\EventTeam::class, 3)->create(['event_id' => $event->id])->each(function ($team) use ($event) {
                $team->users()->saveMany(factory(\App\Models\EventUser::class, 5)->create()->each(function($user) use ($event, $team){
                    $data = ['event_id' => $event->id, 'team_id' => $team->id, 'user_id' => $user->id];
                    $user->transcriptions()->saveMany(factory(\App\Models\EventTranscription::class, 5)->create($data));
                }));
            });
        });
    }
}
