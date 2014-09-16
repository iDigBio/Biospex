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
use Biospex\Repo\Meta\MetaInterface;
use Biospex\Repo\SubjectDoc\SubjectDocInterface;
use Biospex\Repo\Subject\SubjectInterface;
use Biospex\Repo\Header\HeaderInterface;

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
	 * Multimedia identifier column if occurrence file is core
	 * @var
	 */
	private $multiMediaIdentifier;

	/**
	 * Header row for occurrence file
	 * @var
	 */
	private $occurrenceHeader = array();

	/**
	 * Header row for multimedia file
	 * @var
	 */
	private $multiMediaHeader = array();

	/**
	 * Array of duplicate subjects
	 * @var array
	 */
	private $duplicateArray = array();

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
	 * Constructor
	 *
	 * @param MetaInterface $meta
	 * @param SubjectDocInterface $subjectdoc
	 * @param SubjectInterface $subject
	 * @param HeaderInterface $header
	 */
	public function __construct (
		MetaInterface $meta,
		SubjectDocInterface $subjectdoc,
		SubjectInterface $subject,
		HeaderInterface $header
	)
	{
		$this->meta = $meta;
		$this->subjectdoc = $subjectdoc;
		$this->subject = $subject;
		$this->header = $header;
	}

	/**
	 * Load csv file
	 *
	 * @param $filePath
	 * @param $type
	 * @return array
	 */
	public function loadCsv ($filePath, $type)
	{
		$result = array();
		$handle = fopen($filePath, "r");
		if ($handle) {
			$header = null;
			while (($row = fgetcsv($handle, 10000, $this->delimiter)) !== FALSE) {
				if ($header === null) {
					$this->occurrenceHeader = ($type == 'occurrence') ? $row : $this->occurrenceHeader;
					$this->multiMediaHeader = ($type == 'multimedia') ? $row : $this->multiMediaHeader;
					$header = $row;
					continue;
				}
				$result[] = array_combine($header, $row);
			}
			fclose($handle);
		}

		return $result;
	}

	/**
	 * Build subject array and insert extension
	 *
	 * @param $multimedia
	 * @param $occurrence
	 * @param $projectId
	 * @param $metaId
	 * @return array
	 */
	public function buildSubjectsArray ($multimedia, $occurrence)
	{
		// create new array with occurrence id as key
		$occurrenceInstance = array();
		foreach ($occurrence as $key => $row) {
			$occurrenceInstance[$row['id']] = $row;
			unset($occurrence[$key]);
		}

		if ($this->mediaIsCore) {
			$subjects = array();
			foreach ($multimedia as $key => $subject) {
				$subjects[$key] = array(
					'project_id' => $this->projectId,
					'subject_id' => $subject[$this->multiMediaHeader[0]],
					'subject' => $subject,
					'occurrence' => $occurrenceInstance[$subject[$this->multiMediaHeader[0]]]
				);
			}
		} else {
			$subjects = array();
			foreach ($multimedia as $key => $subject) {
				// Set occurrence before changing subject id
				$occurrenceId = $subject[$this->multiMediaHeader[0]];
				// Set subject id using identifier then unset identifier
				$subject['id'] = $subject[$this->multiMediaIdentifier];
				unset($subject[$this->multiMediaIdentifier]);

				$subjects[$key] = array(
					'project_id' => $this->projectId,
					'subject_id' => $subject['id'],
					'subject' => $subject,
					'occurrence' => $occurrenceInstance[$occurrenceId]
				);
			}
		}

		return $subjects;
	}

	/**
	 * Insert docs
	 *
	 * @param $subjects
	 * @param $meta
	 * @return array
	 */
	public function insertDocs ($subjects, $meta)
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
				'meta_id' => $meta->id,
				'mongo_id' => $subjectDoc->_id,
				'object_id' => $subjectDoc->subject_id,
			);
			$this->subject->create($data);
		}

		return $this->duplicateArray;
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
		$rules = array('project_id' => 'unique_with:subjectsdocs,subject_id');
		$values = array('project_id' => $subject['project_id'], 'subject_id' => $subject['subject_id']);

		$validator = Validator::make($values, $rules);
		$validator->getPresenceVerifier()->setConnection('mongodb');

		return $validator->fails() ? false : true;
	}

	/**
	 * Save Meta file and header information
	 *
	 * @param $xml
	 * @return mixed
	 */
	public function saveMeta ($xml)
	{
		$meta = $this->meta->create(array('project_id' => $this->projectId, 'xml' => $xml, 'header' => json_encode($this->multiMediaHeader)));

		return $meta;
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
	 * Set delimiter
	 *
	 * @param $delimiter
	 */
	public function setDelimiter ($delimiter)
	{
		$this->delimiter = ($delimiter == ",") ? "," : str_replace("\\t", "\t", $delimiter);
	}

	/**
	 * Set column index for multimedia identifier
	 *
	 * @param $index
	 */
	public function setMultiMediaIdentifier($index)
	{
		if (is_null($index))
			return;

		$this->multiMediaIdentifier = $this->multiMediaHeader[$index];
	}

	/**
	 * Set header array and update/save
	 */
	public function setHeaderArray()
	{
		$result = $this->header->getByProjectId($this->projectId);

		$csvHeader = array_map(function() {}, array_flip($this->multiMediaHeader));

		if (is_null($result))
		{
			$this->headerArray = $csvHeader;
			$array = array(
				'project_id' => $this->projectId,
				'header' => json_encode($this->headerArray),
			);
			$header = $this->header->create($array);
			$this->headerId = $header->id;
		}
		else
		{
			$this->headerArray = array_merge(json_decode($result->header, true), $csvHeader);
			$result->header = json_encode($this->headerArray);
			$this->headerId = $result->id;
		}

		return;
	}

	/**
	 * Return the project id being processed
	 *
	 * @return mixed
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}

	/**
	 * Return meta file data
	 *
	 * @param $id
	 * @return mixed
	 */
	public function getMeta ($id)
	{
		return $this->meta->find($id);
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

	/**
	 * Return header array for occurrence
	 *
	 * @return array
	 */
	public function getOccurrenceHeader()
	{
		return $this->occurrenceHeader;
	}

	/**
	 * Return header array for multimedia file
	 *
	 * @return array
	 */
	public function getMultiMediaHeader()
	{
		return $this->multiMediaHeader;
	}
}