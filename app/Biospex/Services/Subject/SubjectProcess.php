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
use Biospex\Repo\SubjectDoc\SubjectDocInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Header\HeaderInterface;
use Biospex\Repo\Property\PropertyInterface;
use Biospex\Services\Xml\XmlProcess;
use Biospex\Repo\Meta\MetaInterface;

class SubjectProcess {

	/**
	 * Multimedia file from XML
	 *
	 * @var
	 */
	private $multiMediaFile;

	/**
	 * Occurrence file from XML
	 *
	 * @var
	 */
	private $occurrenceFile;

	/**
	 * Sets if media is core or not
	 *
	 * @var
	 */
	private $mediaIsCore;

	/**
	 * Column string value of multimedia identifier.
	 * @var
	 */
	private $multiMediaIdentifier;

	/**
	 * Multimedia identifier column if occurrence file is core
	 * @var
	 */
	private $multiMediaIdentifierIndex;

	/**
	 * Header row for multimedia file
	 * @var
	 */
	private $multiMediaHeader = array();

	/**
	 * Header row for occurrence file
	 *
	 * @var array
	 */
	private $occurrenceHeader = array();

	/**
	 * Array of duplicate subjects
	 * @var array
	 */
	private $duplicateArray = array();

	/**
	 * Array of images with empty identifiers
	 *
	 * @var array
	 */
	private $rejectedMultimedia = array();

	/**
	 * Project Id
	 *
	 * @var null
	 */
	private $projectId = null;

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
	private $headerArray = array();

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
	private $metaFields = array();

	/**
	 * Saved meta xml id
	 * @var
	 */
	private $metaId;

	/**
	 * Constructor
	 *
	 * @param SubjectDocInterface $subjectdoc
	 * @param SubjectInterface $subject
	 * @param HeaderInterface $header
	 * @param PropertyInterface $property
	 * @param XmlProcess $xmlProcess
	 * @param MetaInterface $meta
	 */
	public function __construct (
		SubjectDocInterface $subjectdoc,
		SubjectInterface $subject,
		HeaderInterface $header,
		PropertyInterface $property,
		XmlProcess $xmlProcess,
		MetaInterface $meta
	)
	{
		$this->subjectdoc = $subjectdoc;
		$this->subject = $subject;
		$this->header = $header;
		$this->property = $property;
		$this->xmlProcess = $xmlProcess;
		$this->meta = $meta;
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
		$this->processMetaFile("$dir/meta.xml");

		$multiMediaFile = $this->getMultiMediaFile();
		$occurrenceFile = $this->getOccurrenceFile();

		$multimedia = $this->loadCsv("$dir/$multiMediaFile", "multimedia");
		$occurrence = $this->loadCsv("$dir/$occurrenceFile", "occurrence");

		$this->setHeaderArray();

		$subjects = $this->buildSubjectsArray($multimedia, $occurrence);
		dd($subjects);

		$this->insertDocs($subjects);

		return;
	}

	/**
	 * Process meta file
	 *
	 * @param $file
	 */
	private function processMetaFile($file)
	{
		// Load xml
		$xml = $this->xmlProcess->load($file);

		// Set core type and file
		$coreType = $this->xmlProcess->getDomTagAttribute('core', 'rowType');
		$coreFile = $this->xmlProcess->getElementByTag('core');
		if (empty($coreType) || empty($coreFile))
			throw new SubjectProcessException('[SubjectProcess] Error querying core.');

		// Set delimiter
		$delimiter = $this->xmlProcess->getDomTagAttribute('core', 'fieldsTerminatedBy');
		if (empty($delimiter))
			throw new SubjectProcessException('[SubjectProcess] Error querying delimiter.');
		$this->setDelimiter($delimiter);

		if (preg_match('/occurrence/i', $coreType))
		{
			$this->setCore(false);
			$this->setOccurrenceFile($coreFile);

			$query = "//ns:extension[contains(php:functionString('strtolower', @rowType), 'multimedia')]";
			$multiMediaQuery = $this->xmlProcess->xpathQueryOne($query);
			if (empty($multiMediaQuery))
				throw new SubjectProcessException('[SubjectProcess] Error querying multimedia file.');
			$this->setMultiMediaFile($multiMediaQuery->nodeValue);

			$query = "//ns:extension/ns:field[contains(php:functionString('strtolower', @term), 'identifier')]";
			$multiMediaIndexQuery = $this->xmlProcess->xpathQueryOne($query);
			if (empty($multiMediaIndexQuery))
				throw new SubjectProcessException('[SubjectProcess] Error querying multimedia identifier index.');

			$identifier = $multiMediaIndexQuery->attributes->getNamedItem("index")->nodeValue;
			$this->setMultiMediaIdentifierIndex($identifier);

			$occurrence_xpath_query = "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field";
			$multimedia_xpath_query = "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field";

		}
		elseif (preg_match('/multimedia/i', $coreType))
		{
			$this->setCore(true);
			$this->setMultiMediaFile($coreFile);

			$query = "//ns:extension[contains(php:functionString('strtolower', @rowType), 'occurrence')]";
			$occurrenceQuery = $this->xmlProcess->xpathQueryOne($query);
			if (empty($occurrenceQuery))
				throw new SubjectProcessException('[SubjectProcess] Error querying occurrence file.');

			$this->setOccurrenceFile($occurrenceQuery->nodeValue);

			$occurrence_xpath_query = "//ns:archive/ns:extension[contains(php:functionString('strtolower', @rowType), 'occurrence')]/ns:field";
			$multimedia_xpath_query = "//ns:archive/ns:core[contains(php:functionString('strtolower', @rowType), 'multimedia')]/ns:field";
		}

		$this->buildMetaFields($multimedia_xpath_query, $occurrence_xpath_query);

		$this->saveMeta($xml);

		return;
	}

	/**
	 * Load csv file
	 *
	 * @param $filePath
	 * @param null $type
	 * @return array
	 * @throws SubjectProcessException
	 */
	public function loadCsv ($filePath, $type = null)
	{
		$results = array();
		$handle = fopen($filePath, "r");
		if ($handle) {
			$header = null;
			while (($row = fgetcsv($handle, 10000, $this->delimiter)) !== FALSE) {
				if ($header === null)
				{
					$header = $this->buildHeaderRow($row, $type);

					if ($type == 'multimedia')
					{
						$this->multiMediaHeader = $header;
					}
					else
					{
						$this->occurrenceHeader = $header;
					}

					continue;
				}

				$row = array_intersect_key($row, $header);

				if (count($header) != count($row))
					throw new SubjectProcessException('[SubjectProcess] Header column count does not match row count.');

				$results[] = array_combine($header, $row);
			}
			fclose($handle);
		}

		return $results;
	}

	/**
	 * Build subject array and insert extension
	 *
	 * @param $multimedia
	 * @param $occurrences
	 * @return array
	 * @throws \Exception
	 */
	public function buildSubjectsArray ($multimedia, $occurrence)
	{
		// create new array with occurrence id as key
		$occurrences = $this->formatOccurrences($occurrence);

		foreach ($multimedia as $key => $subject)
		{
			// TODO: Need to find what id will be when media is core file
			$occurrenceId = $subject[$this->multiMediaHeader[0]];
			$subject['id'] = $this->mediaIsCore ? $this->multiMediaHeader[0] : $subject[$this->multiMediaIdentifier];

			if (empty($subject['id']))
			{
				$this->rejectedMultimedia[] = $subject;
				continue;
			}

			$subjects[$key] = array(
				'project_id' => $this->projectId,
				'subject_id' => $subject['id'],
				'subject' => array_merge($this->headerArray, $subject),
				'occurrence' => $occurrences[$occurrenceId]
			);

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
		$result = array();
		foreach ($occurrence as $key => $row)
		{
			$result[$row[$this->occurrenceHeader[0]]] = $row;
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
		foreach ($subjects as $subject) {
			if (!$this->validateDoc($subject)) {
				$this->duplicateArray[] = array($subject['subject_id']);
				continue;
			}

			$subjectDoc = $this->subjectdoc->create($subject);
			$data = array(
				'project_id' => $subjectDoc->project_id,
				'header_id' => $this->headerId,
				'meta_id' => $this->metaId,
				'mongo_id' => $subjectDoc->_id,
				'object_id' => $subjectDoc->subject_id,
			);
			$this->subject->create($data);
		}

		return;
	}

	/**
	 * Build header array for multimedia csv file so it matches qualified names
	 * and set the multimediaIdentifier string value.
	 *
	 * @param $row
	 * @param $type
	 * @return array
	 */
	public function buildHeaderRow($row, $type)
	{
		$header = array('id');
		$row = array_intersect_key($row, $this->metaFields[$type]);

		foreach ($this->metaFields[$type] as $key => $qualified)
		{
			if ( ! isset($row[$key]))
				throw new SubjectProcessException("[SubjectProcess] Undefined index for $key => $qualified.");

			$short = $this->checkProperty($qualified, $row[$key]);
			$header[$key] = $short;

			if ($type == 'multimedia' && $key == $this->multiMediaIdentifierIndex)
				$this->multiMediaIdentifier = $short;
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
		list($namespace, $short) = preg_match('/:/', $ns_short) ? preg_split('/:/', $ns_short) : array('', $ns_short);

		$checkQualified = $this->property->findByQualified($qualified);
		$checkShort = $this->property->findByShort($short);

		// Return if qualified exists and short is the same.
		if ( ! is_null($checkQualified))
		{
			return $checkQualified->short;
		}
		// Create using new short if qualified is null and short exists.
		elseif (is_null($checkQualified) && ! is_null($checkShort))
		{
			$short .= substr(md5(uniqid(mt_rand(), true)), 0, 4);
			$array = array(
				'qualified' => $qualified,
				'short' => $short,
				'namespace' => $namespace,
			);
			$this->property->create($array);

			return $short;
		}
		// Create if neither exist using same short
		elseif (is_null($checkQualified) && is_null($checkShort))
		{
			$array = array(
				'qualified' => $qualified,
				'short' => $short,
				'namespace' => $namespace,
			);
			$this->property->create($array);

			return $short;
		}
	}

	/**
	 * Build the core and extension field array from meta file.
	 *
	 * @param $multimedia
	 * @param $occurrence
	 */
	public function buildMetaFields($multimedia, $occurrence)
	{
		foreach($this->xmlProcess->xpathQuery($multimedia) as $child)
		{
			$index = $child->attributes->getNamedItem("index")->nodeValue;
			$qualified = $child->attributes->getNamedItem("term")->nodeValue;
			$this->metaFields['multimedia'][$index] = $qualified;
		}

		foreach($this->xmlProcess->xpathQuery($occurrence) as $child)
		{
			$index = $child->attributes->getNamedItem("index")->nodeValue;
			$qualified = $child->attributes->getNamedItem("term")->nodeValue;
			$this->metaFields['occurrence'][$index] = $qualified;
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
		$rules = array('project_id' => 'unique_with:subjectdocs,subject_id');
		$values = array('project_id' => $subject['project_id'], 'subject_id' => $subject['subject_id']);

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
		$this->projectId = (int)$id;
	}

	/**
	 * Set whether core is medi or not
	 *
	 * @param $value
	 */
	public function setCore ($value)
	{
		$this->mediaIsCore = $value;
	}

	/**
	 *  Set occurrence file
	 *
	 * @param $filename
	 */
	public function setOccurrenceFile ($filename)
	{
		$this->occurrenceFile = $filename;
	}

	/**
	 * Set multimedia file
	 *
	 * @param $filename
	 */
	public function setMultiMediaFile ($filename)
	{
		$this->multiMediaFile = $filename;
	}

	/**
	 * Set column index for multimedia identifier
	 *
	 * @param $index
	 */
	public function setMultiMediaIdentifierIndex($index)
	{
		$this->multiMediaIdentifierIndex = $index;
	}

	/**
	 * Set header array and update/save
	 */
	public function setHeaderArray()
	{
		$result = $this->header->getByProjectId($this->projectId);

		$headerFields = array_map(function() {}, array_flip($this->multiMediaHeader));

		if (is_null($result))
		{
			$this->headerArray = $headerFields;
			$array = array(
				'project_id' => $this->projectId,
				'header' => json_encode($this->headerArray),
			);
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
		$result = $this->meta->create(array(
			'project_id' => $this->projectId,
			'xml' => $xml,
		));

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

	/**
	 * Return multimedia file
	 * @return mixed
	 */
	public function getMultiMediaFile ()
	{
		return $this->multiMediaFile;
	}

	/**
	 * Return occurrence file
	 * @return mixed
	 */
	public function getOccurrenceFile ()
	{
		return $this->occurrenceFile;
	}
}