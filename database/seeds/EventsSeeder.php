<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class EventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        DB::statement('TRUNCATE TABLE `events`');
        DB::statement('TRUNCATE TABLE `event_teams`');
        DB::statement('TRUNCATE TABLE `event_team_user`');
        DB::statement('TRUNCATE TABLE `event_transcriptions`');
        DB::statement('TRUNCATE TABLE `event_users`');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
        */
        $start_date = Carbon::parse('2020-01-06 15:20:00');
        $end_date = Carbon::parse('2020-01-06 20:00:00');
        $data = ['start_date' => $start_date, 'end_date' => $end_date];
        $intervals = $this->setInterval($start_date->copy(), $end_date->copy());

        factory(\App\Models\Event::class, 1)->create($data)->each(function ($event) use($intervals) {
            factory(\App\Models\EventTeam::class, 6)->create(['event_id' => $event->id])->each(function ($team) use ($event, $intervals) {
                $team->users()->saveMany(factory(\App\Models\EventUser::class, 5)->create()->each(function($user) use ($event, $team, $intervals){
                    $data = ['event_id' => $event->id, 'team_id' => $team->id, 'user_id' => $user->id];
                    $first = $intervals->keys()->first();
                    $intervals->each(function($value, $key) use ($user, $data, $first) {
                        if ($key === $first) return;
                        if (rand(0,1)) return;

                        $data['created_at'] = $key;
                        $data['updated_at'] = $key;
                        $user->transcriptions()->saveMany(factory(\App\Models\EventTranscription::class, 1)->create($data));
                    });
                }));
            });
        });
    }

    protected function setInterval($startLoad, $endLoad)
    {
        do {
            $intervals[] = $startLoad->copy()->format('Y-m-d H:i:s');
            $startLoad->addMinutes(4);
        } while ($startLoad->lt($endLoad) || $startLoad->eq($endLoad));

        return collect($intervals)->flip();
    }
}
