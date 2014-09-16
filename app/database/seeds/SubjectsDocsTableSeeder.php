<?php
/**
 * SubjectsDocsTableSeeder.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <79e6ef82@opayq.com>
 * @license    GNU General Public License, version 3
 * @copyright  (c) 2014, Biospex
 * @link       http://biospex.org
 *
 * This file is part of Biospex.
 * Biospex is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Biospex is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Biospex.  If not, see <http://www.gnu.org/licenses/>.
 */

use Illuminate\Database\Seeder;
use Biospex\Services\Subject\SubjectProcess;
use Biospex\Services\Xml\XmlProcess;

class SubjectsDocsTableSeeder extends Seeder {

    /**
     * Default project Id
     *
     * @var int
     */
    protected $projectId = 1;

    /**
     * Constructor
     *
     * @param SubjectsImport $subjectsImport
     */
    public function __construct (
		SubjectProcess $subjectProcess,
		XmlProcess $xmlProcess
	)
    {
        $this->subjectProcess = $subjectProcess;
		$this->xmlProcess = $xmlProcess;
		$this->metaFile = Config::get('config.metaFile');
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run ()
    {
        Eloquent::unguard();

        DB::table('metas')->truncate();
        DB::table('subjects')->truncate();
        DB::connection('mongodb')->collection('subjectsdocs')->delete();

		$xml = $this->xmlProcess->load('app/database/seeds/data/meta.xml');
		//$array = $this->xmlProcess->createArray($xml);

		$coreType = $this->xmlProcess->getDomTagAttribute('core', 'rowType');
		$coreFile = $this->xmlProcess->getElementByTag('core');

		$this->subjectProcess->setDelimiter($this->xmlProcess->getDomTagAttribute('core', 'fieldsTerminatedBy'));
		$this->subjectProcess->setProjectId($this->projectId);

		if (preg_match('/occurrence/i', $coreType))
		{
			$this->subjectProcess->setCore(false);
			$this->subjectProcess->setOccurrenceFile($coreFile);

			$multiMediaQuery = $this->xmlProcess->getXpathQuery("//ns:extension[contains(@rowType, '{$this->metaFile['multimediaFile']}')]");
			$this->subjectProcess->setMultiMediaFile($multiMediaQuery->nodeValue);

			$multiMediaIndexQuery = $this->xmlProcess->getXpathQuery("//ns:field[contains(@term, '{$this->metaFile['identifier']}')]");
			$identifier = $multiMediaIndexQuery->attributes->getNamedItem("index")->nodeValue;
		}
		elseif (preg_match('/multimedia/', $coreType))
		{
			$this->subjectProcess->setCore(true);
			$this->subjectProcess->setMultiMediaFile($coreFile);

			$occurrenceQuery = $this->xmlProcess->getXpathQuery("//ns:extension[contains(@rowType, '{$this->metaFile['occurrenceFile']}')]");
			$this->subjectProcess->setOccurrenceFile($occurrenceQuery->nodeValue);
			$identifier = null;
		}

        $multiMediaFile = $this->subjectProcess->getMultiMediaFile();
        $occurrenceFile = $this->subjectProcess->getOccurrenceFile();

        $multimedia = $this->subjectProcess->loadCsv("app/database/seeds/data/$multiMediaFile", 'multimedia');
        $occurrence = $this->subjectProcess->loadCsv("app/database/seeds/data/$occurrenceFile", 'occurrence');

		$this->subjectProcess->setMultiMediaIdentifier($identifier);
		$this->subjectProcess->setHeaderArray();

		//$multimediaHeader = $this->subjectProcess->getMultiMediaHeader();
		//$occurrenceHeader = $this->subjectProcess->getOccurrenceHeader();

		$subjects = $this->subjectProcess->buildSubjectsArray($multimedia, $occurrence);

		$meta = $this->subjectProcess->saveMeta($xml);

        $this->subjectProcess->insertDocs($subjects, $meta);
    }
}