<?php
/*
project_id
	Projects
	Expeditions
	Headers
	Imports
	Metas
	Project_Actor


expedition_id
	Downloads
	Expedition_Actor
	Workflow_Manager

 */
use Rhumsaa\Uuid\Uuid;

class UpdateUuids extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run ()
	{
		Eloquent::unguard();

		$projects = Project::all();
		foreach ($projects as $project)
		{
			$project->uuid = is_null($project->uuid) ? Uuid::uuid4()->__toString() : $project->uuid;
			$project->save();
		}

		$expeditions = Expedition::all();
		foreach($expeditions as $expedition)
		{
			$expedition->uuid = empty($expedition->uuid) ? Uuid::uuid4()->__toString() : $expedition->uuid;
			$expedition->save();
		}
	}
}