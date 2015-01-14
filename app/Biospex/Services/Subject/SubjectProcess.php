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
use Illuminate\Support\Facades\Config;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Header\HeaderInterface;
use Biospex\Repo\Property\PropertyInterface;
use Biospex\Services\Xml\XmlProcess;
use Biospex\Repo\Meta\MetaInterface;

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
	 * Delimiter from meta file
	 * @var
	 */
	private $delimiter;

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
	 * Constructor
	 *
	 * @param SubjectInterface $subject
	 * @param HeaderInterface $header
	 * @param PropertyInterface $property
	 * @param XmlProcess $xmlProcess
	 * @param MetaInterface $meta
	 */
	public function __construct (
		SubjectInterface $subject,
		HeaderInterface $header,
		PropertyInterface $property,
		XmlProcess $xmlProcess,
		MetaInterface $meta
	)
	{
		$this->subject = $subject;
		$this->header = $header;
		$this->property = $property;
		$this->xmlProcess = $xmlProcess;
		$this->meta = $meta;

		$this->identifiers = Config::get('config.identifiers');
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
	 * Process meta file
	 */
	private function processMetaFile ()
	{
		// Load xml
		$xml = $this->xmlProcess->load("{$this->dir}/meta.xml");

		// Set core type and file
		$coreType = $this->xmlProcess->getDomTagAttribute('core', 'rowType');
		if (empty($coreType))
			throw new \Exception('[SubjectProcess] Error querying core type.');

		$coreFile = $this->xmlProcess->getElementByTag('core');
		if (empty($coreFile))
			throw new \Exception('[SubjectProcess] Error querying core file.');

		// Set delimiter
		$delimiter = $this->xmlProcess->getDomTagAttribute('core', 'fieldsTerminatedBy');
		if (empty($delimiter))
			throw new \Exception('[SubjectProcess] Error querying delimiter.');
		$this->setDelimiter($delimiter);

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
		$results = [];
		$file = $type == 'core' ? "{$this->dir}/{$this->coreFile}" : "{$this->dir}/{$this->extensionFile}";
		$handle = fopen($file, "r");
		if ($handle) {
			$header = null;
			while (($row = fgetcsv($handle, 10000, $this->delimiter)) !== FALSE && ! is_null($row[0])) {

				if ($header === null)
				{
					$header = $this->buildHeaderRow($row, $type);

					if ($type == 'core')
					{
						$this->coreHeader = $header;
					}
					else
					{
						$this->extensionHeader = $header;
					}

					continue;
				}

				$row = array_intersect_key($row, $header);

				if (count($header) != count($row))
					throw new \Exception('[SubjectProcess] Header column count does not match row count. Header - Row: ' . count($header) . ' - ' . count($row));

				$results[] = array_combine($header, $row);
			}
			fclose($handle);
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
			$identifier = $this->getIdentifier($subject);
			// TODO: Need to find what id will be when media is core file
			$occurrenceId = $this->mediaIsCore ? null : $subject[$header[0]];
			$subject['id'] = $this->mediaIsCore ? $subject[$header[0]] : $identifier;

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
			$result[$row[$header[0]]] = $row;
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

		foreach ($subjects as $subject) {
			if (!$this->validateDoc($subject)) {
				$this->duplicateArray[] = [$subject['id']];
				continue;
			}

			$newSubject = $this->subject->create($subject);
			$this->buildOcrQueue($data, $newSubject);
		}

		\Queue::push('Biospex\Services\Queue\OcrService', ['data' => $data], 'ocr');

		return;
	}

	/**
	 * Build the ocr and send to the queue.
	 *
	 * @param $data
	 * @param $subject
	 */
	private function buildOcrQueue(&$data, $subject)
	{
		$data[$subject->_id] = [
			'id' => $subject->id,
			'project_id' => $subject->project_id,
			'url' => $subject->bestQualityAccessURI
		];

		return;
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
				throw new \Exception("[SubjectProcess] Undefined index for $key => $qualified.");

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
		foreach ($this->xmlProcess->xpathQuery($this->coreXpathQuery) as $child)
		{
			$index = $child->attributes->getNamedItem("index")->nodeValue;
			$qualified = $child->attributes->getNamedItem("term")->nodeValue;
			$this->metaFields['core'][$index] = $qualified;
		}

		if ($this->extensionFile)
		{
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
	 * Set delimiter
	 *
	 * @param $delimiter
	 */
	public function setDelimiter ($delimiter)
	{
		$this->delimiter = ($delimiter == ",") ? "," : str_replace("\\t", "\t", $delimiter);
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
	 * Set column index for multimedia identifier
	 * dcterms:identifier
	 * ac:providerManagedID
	 * idigbio:uuid
	 * idigbio:recordId
	 * @param $subject
	 * @return bool
	 */
	public function getIdentifier ($subject)
	{
		foreach ($this->identifiers as $value)
		{
			if (isset($subject[$value]) && !empty($subject[$value]))
				return $subject[$value];
		}

		return false;
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