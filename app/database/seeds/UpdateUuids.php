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

		$projects = Project::with(['expeditions', 'expeditions.actors', 'expeditions.workflowManager', 'header', 'imports', 'metas', 'actors', 'subjects'])->get();
		foreach ($projects as $project)
		{
			$project->uuid = empty($project->uuid) ? Uuid::uuid4()->__toString() : $project->uuid;
			$project->save();

			$project->header->project_uuid = $project->uuid;
			$project->header->save();

			if ( ! empty($project->imports))
				$this->updateRelation($project->imports, $project->uuid);

			if ( ! empty($project->metas))
				$this->updateRelation($project->metas, $project->uuid);

			if ( ! empty($project->actors))
				$this->updatePivot($project->actors, $project->uuid);

			if ( ! empty($project->subjects))
				$this->updateRelation($project->subjects, $project->uuid, 'project_id');

			foreach ($project->expeditions as $expedition)
			{
				$expedition->uuid = empty($expedition->uuid) ? Uuid::uuid4()->__toString() : $expedition->uuid;
				$expedition->project_uuid = $project->uuid;
				$expedition->save();

				if ( ! empty($expedition->actors))
					$this->updatePivot($expedition->actors, $expedition->uuid, 'expedition_uuid');

				if ( ! empty($expedition->workflowManager))
					$this->updateRelation($expedition->workflowManager, $expedition->uuid, 'expedition_uuid');

				$subjects = Subject::where('expedition_ids', '=', $expedition->id)->get();
				if ( ! empty($subjects))
				{
					foreach ($subjects as $subject)
					{
						$vars = $subject->expedition_ids;
						foreach ($vars as $key => $id)
						{
							if ($id == $expedition->id)
								$vars[$key] = $expedition->uuid;
						}
						$subject->expedition_ids = $vars;
						$subject->save();
					}
				}

			}
		}

	}

	protected function updateRelation($relations, $uuid, $field = 'project_uuid')
	{
		foreach ($relations as $relation)
		{
			$relation->{$field} = $uuid;
			$relation->save();
		}
	}

	protected function updatePivot($relations, $uuid, $field = 'project_uuid')
	{
		foreach ($relations as $relation)
		{
			$relation->pivot->{$field} = $uuid;
			$relation->pivot->save();
		}
	}
}