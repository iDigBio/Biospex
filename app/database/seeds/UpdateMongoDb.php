<?php
use Illuminate\Database\Seeder;
use Biospex\Repo\Project\ProjectInterface;
use Biospex\Repo\Expedition\ExpeditionInterface;
use Biospex\Repo\Subject\SubjectInterface;

class UpdateMongoDb extends Seeder {

	public function __construct (
		ProjectInterface $project,
		ExpeditionInterface $expedition,
		SubjectInterface $subject
	)
	{
		$this->project = $project;
		$this->expedition = $expedition;
		$this->subject = $subject;
	}

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run ()
	{
		Eloquent::unguard();

		try
		{
			$subjects = $this->subject->all();
			foreach ($subjects as $subject)
			{
				$subject->project_id = [(string) $subject->project_id];
				$subject->save();

				$results = DB::select("SELECT * FROM expedition_subject where subject_id = ?", array($subject->_id));
				if ($results)
				{
					foreach ($results as $result)
					{
						$expedition = $this->expedition->find($result->expedition_id);
						$expedition->subjects()->attach($subject);
					}
				}
				else
				{
					$subject->expedition_ids = [];
				}

				$subject->ocr = '';
				$subject->save();
			}

		} catch (Exception $e)
		{
			die($e->getMessage() . $e->getTraceAsString());
		}
	}
}