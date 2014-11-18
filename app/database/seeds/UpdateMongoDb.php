<?php
use Illuminate\Database\Seeder;

class UpdateMongoDb extends Seeder {

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
			$subjects = Subject::all();
			foreach ($subjects as $subject)
			{
				$subject->project_id = [(string) $subject->project_id];

				$results = DB::connection('mysql')->select("SELECT es.expedition_id
									FROM subjects s
									INNER JOIN expedition_subject es on es.subject_id = s.id
									WHERE s.mongo_id = ?", array($subject->_id));
				if ($results)
				{
					foreach ($results as $result)
					{
						$expedition = Expedition::find($result->expedition_id);
						$expedition->subjects()->attach($subject->_id);
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