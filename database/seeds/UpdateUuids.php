<?php

use Rhumsaa\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;

class UpdateUuids extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $projects = App\Models\Project::withTrashed()->get();
        foreach ($projects as $project) {
            $project->uuid = is_null($project->uuid) ? Uuid::uuid4()->__toString() : $project->uuid;
            $project->save();
        }

        $expeditions = Expedition::withTrashed()->get();
        foreach ($expeditions as $expedition) {
            $expedition->uuid = empty($expedition->uuid) ? Uuid::uuid4()->__toString() : $expedition->uuid;
            $expedition->save();
        }
    }
}
