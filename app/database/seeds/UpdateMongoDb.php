<?php
use Illuminate\Database\Seeder;
use Biospex\Repo\SubjectDoc\SubjectDocInterface;
use Biospex\Repo\Subject\SubjectInterface;

class UpdateMongoDb extends Seeder {

	public function __construct (
		SubjectDocInterface $subjectdoc,
		SubjectInterface $subject
	)
	{
		$this->subjectdoc = $subjectdoc;
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
			$results = $this->subjectdoc->all();
			foreach ($results as $result)
			{
				$subject = $this->subject->findByForeignId('mongo_id', $result->_id);
				$newDoc['project_id'] = $result->project_id;
				foreach ($result->subject as $key => $value)
				{
					$newDoc[$key] = $value;
				}
				$newDoc['occurrence'] = $result->occurrence;

				$doc = $this->subjectdoc->create($newDoc);
				$subject->mongo_id = $doc->_id;
				$subject->save();

				$result->delete();
			}
		} catch (Exception $e)
		{
			die($e->getMessage() . $e->getTraceAsString());
		}
	}
}