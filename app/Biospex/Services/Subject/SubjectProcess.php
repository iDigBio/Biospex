<?php namespace Biospex\Services\Subject;

/**
 * Subject.php
 *
 * @package    Biospex Package
 * @version    1.0
 * @author     Robert Bruhn <bruhnrp@gmail.com>
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

use Validator;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Header\HeaderInterface;
use Biospex\Repo\Property\PropertyInterface;
use Biospex\Services\Xml\XmlProcess;
use Biospex\Repo\Meta\MetaInterface;
use Biospex\Repo\OcrQueue\OcrQueueInterface;
use Maatwebsite\Excel\Excel;
use Illuminate\Support\Facades\Config;

class SubjectProcess {

	/**
	 * Core file from XML
	 *
	 * @var
	 */
	private $coreFile;

	/**
	 * Extension file from XML
	 *
	 * @var
	 */
	private $extensionFile;

	/**
	 * Sets if media is core or not
	 *
	 * @var
	 */
	private $mediaIsCore;

	/**
	 * Core file xpath query
	 * @var
	 */
	private $coreXpathQuery;

	/**
	 * Extension file xpath query
	 * @var
	 */
	private $extXpathQuery;

	/**
	 * Header row for core file
	 * @var
	 */
	private $coreHeader = [];

	/**
	 * Header row for extension file
	 *
	 * @var array
	 */
	private $extensionHeader = [];

	/**
	 * Array of duplicate subjects
	 * @var array
	 */
	private $duplicateArray = [];

	/**
	 * Array of images with empty identifiers
	 *
	 * @var array
	 */
	private $rejectedMultimedia = [];

	/**
	 * Project Id
	 *
	 * @var null
	 */
	private $projectId = null;

	/**
	 * Dir
	 * @var
	 */
	private $dir;

	/**
	 * Core delimiter
	 */
	private $coreDelimiter;

	/**
	 * Core enclosure
	 */
	private $coreEnclosure;

	/**
	 * core line ending
	 */
	private $coreLineEnding;

	/**
	 * Extension delimiter
	 */
	private $extDelimiter;

	/**
	 * Extension enclosure
	 */
	private $extEnclosure;

	/**
	 * Extension line ending
	 */
	private $extLineEnding;

	/**
	 * Header array for project
	 *
	 * @var
	 */
	private $headerArray = [];

	/**
	 * Header Id for headers associated with project
	 *
	 * @var null
	 */
	private $headerId = null;

	/**
	 * Qualified field array from meta file.
	 *
	 * @var array
	 */
	private $metaFields = [];

	/**
	 * Saved meta xml id
	 * @var
	 */
	private $metaId;

	/**
	 * Array of identifier columns
	 * @var
	 */
	private $identifiers;

	/**
	 * Media identifier.
	 * @var
	 */
	private $identifierColumn;

	/**
	 * Crop for OCR
	 */
	private $ocrCrop;

	/**
	 * Queue to use when processing OCR.
	 * @var
	 */
	private $queue;

    /**
     * @var mixed
     */
    private $disableOcr;

	/**
	 * Constructor
	 *
	 * @param SubjectInterface $subject
	 * @param HeaderInterface $header
	 * @param PropertyInterface $property
	 * @param XmlProcess $xmlProcess
	 * @param MetaInterface $meta
	 * @param OcrQueueInterface $ocr
	 * @param Excel $excel
	 */
	public function __construct (
		SubjectInterface $subject,
		HeaderInterface $header,
		PropertyInterface $property,
		XmlProcess $xmlProcess,
		MetaInterface $meta,
		OcrQueueInterface $ocr,
		Excel $excel
	)
	{
		$this->subject = $subject;
		$this->header = $header;
		$this->property = $property;
		$this->xmlProcess = $xmlProcess;
		$this->meta = $meta;
		$this->ocr = $ocr;
		$this->excel = $excel;

		$this->identifiers = Config::get('config.identifiers');
		$this->ocrCrop = Config::get('config.ocrCrop');
        $this->disableOcr = Config::get('config.disableOcr');
		$this->queue = Config::get('config.beanstalkd.ocr');

	}

	/**
	 * Process subjects from meta file
	 *
	 * @param $projectId
	 * @param $dir
	 */
	public function processSubjects($projectId, $dir)
	{
		$this->setProjectId($projectId);
		$this->setDir($dir);
		$this->processMetaFile();

		$core = $this->loadCsv("core");
		$extension = $this->extensionFile ? $this->loadCsv("extension") : null;

		$this->setHeaderArray();

		$subjects = $this->buildSubjectsArray($core, $extension);

		if ( ! empty($subjects))
			$this->insertDocs($subjects);

		return;
	}

	/**
	 * Process meta file.
	 */
	private function processMetaFile ()
	{
		// Load xml
		$xml = $this->xmlProcess->load("{$this->dir}/meta.xml");

		// Set core type and file
		$coreType = $this->xmlProcess->getDomTagAttribute('core', 'rowType');
		if (empty($coreType))
			throw new \Exception(trans('emails.error_core_type'));

		$coreFile = $this->xmlProcess->getElementByTag('core');
		if (empty($coreFile))
			throw new \Exception(trans('emails.error_core_file_missing'));

		// Set csv settings
		$this->setCsvSettings('core');
		$this->setCsvSettings('extension');

		$this->mediaIsCore = preg_match('/occurrence/i', $coreType) ? false : true;

		$this->setMetaFiles($coreFile);
		$this->setMetaQueries();
		$this->buildMetaFields();
		$this->saveMeta($xml);

		return;
	}

	/**
	 * Load csv file
	 *
	 * @param $type
	 * @return array
	 * @throws \Exception
	 */
	public function loadCsv ($type)
	{
		$file = ($type == 'core') ? "{$this->dir}/{$this->coreFile}" : "{$this->dir}/{$this->extensionFile}";
		$delimiter = ($type == 'core') ? $this->coreDelimiter : $this->extDelimiter;
		$enclosure = ($type == 'core') ? $this->coreEnclosure : $this->extEnclosure;
		$lineEnding = ($type == 'core') ? $this->coreLineEnding : $this->extLineEnding;

		$data = $this->excel->setDelimiter($delimiter)
			->setEnclosure($enclosure)
			->setLineEnding($lineEnding)
			->load($file)
			->get();

		// Get first row for header. Laravel-Excel returns array with key starting at 1 so reset array keys starting at zero.
		$header = $this->buildHeaderRow(array_values($data->first()->toArray()), $type);
		$this->setIdentifierColumn($header);
		$this->setHeaderProperty($header, $type);
		$rows = $data->toArray();

		foreach ($rows as $key => $row)
		{
			if ($key == 0)
				continue;

			// Check row and header have same count
			if (count($header) != count($row))
				throw new \Exception(trans('emails.error_csv_row_count', ['headers' => count($header), 'rows' => count($row)]));

			$combined = array_combine($header, $row);
			$this->stripUuidPrefix($combined, $type);

			$results[] = $combined;
		}

		return $results;
	}

	/**
	 * Build subject array and insert extension
	 *
	 * @param $core
	 * @param $extension
	 * @return array
	 * @throws \Exception
	 */
	public function buildSubjectsArray ($core, $extension = null)
	{
		if ($this->mediaIsCore)
		{
			$occurrence = $extension;
			$multimedia = $core;
			$header = $this->coreHeader;
		} else
		{
			$occurrence = $core;
			$multimedia = $extension;
			$header = $this->extensionHeader;
		}

		$subjects = [];

		// create new array with occurrence id as key
		$occurrences = is_null($occurrence) ? null : $this->formatOccurrences($occurrence);

		foreach ($multimedia as $key => $subject)
		{
			$occurrenceId = $this->mediaIsCore ? null : $subject[$header[0]];
			$subject['id'] = $this->mediaIsCore ? $subject[$header[0]] : $subject[$this->identifierColumn];

			if (empty($subject['id']))
			{
				$this->rejectedMultimedia[] = $subject;
				continue;
			}

			$subjects[$key] = ['project_id' => (string) $this->projectId, 'ocr' => '', 'expedition_ids' => []]
				+ array_merge($this->headerArray, $subject)
				+ ['occurrence' => is_null($occurrences) ? '' : $occurrences[$occurrenceId]];
		}

		return $subjects;
	}

	/**
	 * Rebuild occurrence array using id as key
	 *
	 * @param $occurrence
	 * @return array
	 */
	private function formatOccurrences($occurrence)
	{
		$header = $this->mediaIsCore ? $this->extensionHeader : $this->coreHeader;
		$result = [];
		foreach ($occurrence as $key => $row)
		{
			$result[$row[substr($header[0], -36)]] = $row;
		}

		return $result;
	}

	/**
	 * Insert docs
	 *
	 * @param $subjects
	 * @return array
	 */
	public function insertDocs ($subjects)
	{
		$data = [];
		$count = 0;
		foreach ($subjects as $subject) {
			if (!$this->validateDoc($subject)) {
				$this->unsetSubjectVariables($subject);
				$this->duplicateArray[] = $subject;
				continue;
			}

			$newSubject = $this->subject->create($subject);
			$this->buildOcrQueue($data, $newSubject);
			$count++;
		}

		if ($count == 0)
			return;

        if ($this->disableOcr)
            return;

		$id = $this->saveOcrQueue($data, $count);
        \Queue::push('Biospex\Services\Queue\OcrService', ['id' => $id], $this->queue);

		return;
	}

	/**
	 * Build the ocr and send to the queue.
	 *
	 * @param $data
	 * @param $subject
	 */
	public function buildOcrQueue(&$data, $subject)
	{
		$data[$subject->_id] = [
			'crop' => $this->ocrCrop,
			'ocr' => '',
			'status' => 'pending',
			'url' => $subject->accessURI
		];

		return;
	}

	/**
	 * Save OCR data for later processing.
	 *
	 * @param $data
	 * @param $count
	 * @return mixed
	 */
	public function saveOcrQueue($data, $count)
	{
		$queue = $this->ocr->create([
			'project_id' => $this->projectId,
			'data' => json_encode(['subjects' => $data]),
			'subject_count' => $count
		]);
		return $queue->id;
	}

	/**
	 * Build header csv file so it matches qualified names
	 * and set the multimediaIdentifier string value if media is not core.
	 *
	 * @param $row
	 * @param $type
	 * @return array
	 * @throws \Exception
	 */
	public function buildHeaderRow($row, $type)
	{
		$header = ['id'];
		$row = array_intersect_key($row, $this->metaFields[$type]);

		foreach ($this->metaFields[$type] as $key => $qualified)
		{
			if ( ! isset($row[$key]))
				throw new \Exception(trans('', ['key' => $key, 'qualified' => $qualified]));

			$short = $this->checkProperty($qualified, $row[$key]);
			$header[$key] = $short;
		}

		return $header;
	}

	/**
	 * Check property for qualified and short name. Create when necessary.
	 *
	 * @param $qualified
	 * @param $ns_short
	 * @return string
	 */
	public function checkProperty($qualified, $ns_short)
	{
		list($namespace, $short) = preg_match('/:/', $ns_short) ? preg_split('/:/', $ns_short) : ['', $ns_short];

		$checkQualified = $this->property->findByQualified($qualified);
		$checkShort = $this->property->findByShort($short);

		// Return if qualified exists and short is the same.
		if ( ! is_null($checkQualified))
		{
			$short = $checkQualified->short;
		}
		// Create using new short if qualified is null and short exists.
		elseif (is_null($checkQualified) && ! is_null($checkShort))
		{
			$short .= substr(md5(uniqid(mt_rand(), true)), 0, 4);
			$array = [
				'qualified' => $qualified,
				'short' => $short,
				'namespace' => $namespace,
			];
			$this->property->create($array);
		}
		// Create if neither exist using same short
		elseif (is_null($checkQualified) && is_null($checkShort))
		{
			$array = [
				'qualified' => $qualified,
				'short' => $short,
				'namespace' => $namespace,
			];
			$this->property->create($array);
		}

		return $short;
	}

	/**
	 * Build the core and extension field array from meta file.
	 */
	public function buildMetaFields ()
	{
		$this->metaFields['core'][0] = 'id';
		foreach ($this->xmlProcess->xpathQuery($this->coreXpathQuery) as $child)
		{
			$index = $child->attributes->getNamedItem("index")->nodeValue;
			$qualified = $child->attributes->getNamedItem("term")->nodeValue;
			$this->metaFields['core'][$index] = $qualified;
		}

		if ($this->extensionFile)
		{
			$this->metaFields['extension'][0] = 'id';
			foreach ($this->xmlProcess->xpathQuery($this->extXpathQuery) as $child)
			{
				$index = $child->attributes->getNamedItem("index")->nodeValue;
				$qualified = $child->attributes->getNamedItem("term")->nodeValue;
				$this->metaFields['extension'][$index] = $qualified;
			}
		}

		return;
	}

	/**
	 * Validate if subject exists using project_id and id.
	 * Validator->fails() returns true if validation fails.
	 *
	 * @param $subject
	 * @return bool
	 */
	public function validateDoc ($subject)
	{
		$rules = ['project_id' => 'unique_with:subjects,id'];
		$values = ['project_id' => $subject['project_id'], 'id' => $subject['id']];

		$validator = Validator::make($values, $rules);
		$validator->getPresenceVerifier()->setConnection('mongodb');

		return $validator->fails() ? false : true;
	}

	/**
	 * Set csv settings.
	 *
	 * @param $type
	 * @throws \Exception
	 */
	public function setCsvSettings ($type)
	{
		if ($type == 'core')
		{
			$delimiter = $this->xmlProcess->getDomTagAttribute('core', 'fieldsTerminatedBy');
			$this->coreDelimiter = ($delimiter == "\\t") ? "\t" : $delimiter;
			$this->coreLineEnding = $this->xmlProcess->getDomTagAttribute('core', 'linesTerminatedBy');
			$this->coreEnclosure = $this->xmlProcess->getDomTagAttribute('core', 'fieldsEnclosedBy');

			if (empty($this->coreDelimiter))
				throw new \Exception(trans('emails.error_csv_core_delimiter'));
		}
		else
		{
			$delimiter = $this->xmlProcess->getDomTagAttribute('extension', 'fieldsTerminatedBy');
			$this->extDelimiter = ($delimiter == "\\t") ? "\t" : $delimiter;
			$this->extLineEnding = $this->xmlProcess->getDomTagAttribute('extension', 'linesTerminatedBy');
			$this->extEnclosure = $this->xmlProcess->getDomTagAttribute('extension', 'fieldsEnclosedBy');

			if (empty($this->extDelimiter))
				throw new \Exception(trans('emails.error_csv_ext_delimiter'));
		}

		return;
	}

	/**
	 * Unset unnecessary variables when creating csv.
	 *
	 * @param $subject
	 */
	private function unsetSubjectVariables(&$subject)
	{
		unset($subject['project_id']);
		unset($subject['ocr']);
		unset($subject['expedition_ids']);
		unset($subject['occurrence']);
	}

	/**
	 * Set project id being processed
	 *
	 * @param $id
	 */
	public function setProjectId($id)
	{
		$this->projectId = $id;
	}

	/**
	 * Set the directory for files
	 *
	 * @param $dir
	 */
	private function setDir ($dir)
	{
		$this->dir = $dir;
	}

	/**
	 * Set files by meta.
	 *
	 * @param $coreFile
	 * @throws \Exception
	 */
	public function setMetaFiles ($coreFile)
	{
		$extension = $this->mediaIsCore ? 'occurrence' : 'multimedia';

		$query = "//ns:archive//ns:extension[contains(php:functionString('strtolower', @rowType), '$extension')]";
		$result = $this->xmlProcess->xpathQuery($query, true);

		$this->coreFile = $coreFile;
		$this->extensionFile = empty($result->nodeValue) ? false : $result->nodeValue;

		return;
	}

	/**
	 * Set Xpath queries depending on core
	 */
	public function setMetaQueries ()
	{
		if ($this->mediaIsCore)
		{
			$this->coreXpathQuery = "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field";
			if ($this->extensionFile)
			{
				$this->extXpathQuery = "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field";
			}
		}
		else
		{
			$this->coreXpathQuery = "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field";
			if ($this->extensionFile)
			{
				$this->extXpathQuery = "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field";
			}
		}

		return;
	}

	/**
	 * Set the identifier column
	 *
	 * @param $header
	 */
	private function setIdentifierColumn($header)
	{
		if ( ! $result = array_intersect($this->identifiers, $header))
			return;

		$this->identifierColumn = $result[0];

		return;
	}

	/**
	 * Set header properties.
	 *
	 * @param $header
	 * @param $type
	 */
	private function setHeaderProperty($header, $type)
	{
		if ($type == 'core')
		{
			$this->coreHeader = $header;
		}
		else
		{
			$this->extensionHeader = $header;
		}

		return;
	}

	/**
	 * Set header array and update/save
	 */
	public function setHeaderArray()
	{
		$result = $this->header->getByProjectId($this->projectId);

		$header = $this->mediaIsCore ? $this->coreHeader : $this->extensionHeader;

		$headerFields = array_map(function (){}, array_flip($header));
		if ( ! in_array('ocr', $headerFields)) $headerFields['ocr'] = '';

		if (is_null($result))
		{
			$this->headerArray = $headerFields;
			$array = [
				'project_id' => $this->projectId,
				'header' => json_encode($this->headerArray),
			];
			$header = $this->header->create($array);
			$this->headerId = $header->id;
		}
		else
		{
			$this->headerArray = array_merge(json_decode($result->header, true), $headerFields);
			$result->header = json_encode($this->headerArray);
			$this->headerId = $result->id;
		}

		return;
	}

	/**
	 * Strip prefixes from uuids.
	 *
	 * @param $combined
	 * @param $type
	 */
	private function stripUuidPrefix(&$combined, $type)
	{
		if (isset($combined[$this->identifierColumn]) && ! empty($combined[$this->identifierColumn]))
			$combined[$this->identifierColumn] = substr($combined[$this->identifierColumn], -36);

		$combined[$this->metaFields[$type][0]] = substr($combined[$this->metaFields[$type][0]], -36);

		return;
	}

	/**
	 * Save meta data for this upload.
	 *
	 * @param $xml
	 */
	public function saveMeta($xml)
	{
		$result = $this->meta->create([
			'project_id' => $this->projectId,
			'xml' => $xml,
		]);

		$this->metaId = $result->id;

		return;
	}

	/**
	 * Return duplicate array
	 *
	 * @return array
	 */
	public function getDuplicates()
	{
		return $this->duplicateArray;
	}

	/**
	 * Return empty UUID array
	 *
	 * @return array
	 */
	public function getRejectedMedia()
	{
		return $this->rejectedMultimedia;
	}

}