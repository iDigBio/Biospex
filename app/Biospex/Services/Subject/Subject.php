<?php namespace Biospex\Services\Subject;
/**
 * SubjectImport.php
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
use Config;
use Biospex\Repo\Meta\MetaInterface;
use Biospex\Repo\SubjectDoc\SubjectDocInterface;

class Subject {

    /**
     * Dom document
     * @var
     */
    protected $dom;

    /**
     * Xpath for dom document
     * @var
     */
    protected $xpath;

    /**
     * Multimedia file from XML
     *
     * @var
     */
    protected $multiMediaFile;

    /**
     * Occurrence file from XML
     *
     * @var
     */
    protected $occurrenceFile;

    /**
     * Sets if media is core or not
     *
     * @var
     */
    protected $mediaIsCore;

    /**
     * Id column name for multimedia file
     * @var
     */
    protected $multiMediaIdColName;

    /**
     * Multimedia identifier column index if occurrence file is core
     * @var
     */
    protected $multiMediaIdentifierColIndex;

    /**
     * Id column name of occurrence file
     * @var
     */
    protected $occurrenceIdColName;

    /**
     * Header row for occurrence file
     * @var
     */
    protected $occurrenceHeader = array();

    /**
     * Header row for multimedia file
     * @var
     */
    protected $multiMediaHeader = array();

    /**
     * Array of duplicate subjects
     * @var array
     */
    protected $duplicateArray = array();

    /**
     * Constructor
     * @param MetaInterface $meta
     */
    public function __construct(
        MetaInterface $meta,
        SubjectDocInterface $subjectdoc
    )
    {
        $this->meta = $meta;
        $this->subjectdoc = $subjectdoc;
        $this->metaData = Config::get('config.metaData');
    }

    /**
     * Load the dom document, set xpath, and return xml string
     *
     * @param $meta
     * @param bool $string
     * @return string
     */
    public function loadDom($meta, $string = false)
    {
        $this->dom = new \DOMDocument();
        $this->dom->preserveWhiteSpace = false;

        $dom = ($string) ? $this->dom->loadXml($meta) : $this->dom->load($meta);
        if ( ! $dom)
            return false;

        $this->xpath = new \DOMXpath($this->dom);
        $this->xpath->registerNamespace('ns', $this->dom->documentElement->namespaceURI);

        return $this->dom->saveXML();
    }

    /**
     * Get dom document attribute by tag
     *
     * @param $tag
     * @param $attribute
     * @return mixed
     */
    public function getDomTagAttribute($tag, $attribute)
    {
        return $this->dom->getElementsByTagName($tag)->item(0)->getAttribute($attribute);
    }

    /**
     * Get dom document element by tag
     *
     * @param $tag
     * @return mixed
     */
    public function getElementByTag($tag)
    {
        return $this->dom->getElementsByTagName($tag)->item(0)->nodeValue;
    }

    /**
     * Perform query on dom document
     *
     * @param $query
     * @return mixed
     */
    public function getXpathQuery($query)
    {
        return $this->xpath->query($query)->item(0);
    }

    /**
     * Set Multimedia and Occurrence files from meta.xml
     * Since we do not always know which is the core and which is extension.
     */
    public function setFiles ()
    {
        $coreType = $this->getDomTagAttribute('core', 'rowType');
        $coreFile = $this->getElementByTag('core');

        if (preg_match('/occurrence/i', $coreType))
        {
            $this->mediaIsCore = false;
            $this->occurrenceFile = $coreFile;

            $multiMediaQuery = $this->getXpathQuery("//ns:extension[contains(@rowType, '{$this->metaData['multimediaFile']}')]");
            $this->multiMediaFile = $multiMediaQuery->nodeValue;

            $multiMediaIndexQuery = $this->getXpathQuery("//ns:field[contains(@term, '{$this->metaData['identifier']}')]");
            $this->multiMediaIdentifierColIndex = $multiMediaIndexQuery->attributes->getNamedItem("index")->nodeValue;
            //$this->multiMediaIdentifierColIndex = $xpath->query("//ns:field[contains(@term, 'identifier')]")->item(0)->nodeName;
        }
        elseif (preg_match('/multimedia/', $coreType))
        {
            $this->mediaIsCore = true;
            $this->multiMediaFile = $coreFile;

            $occurrenceQuery = $this->getXpathQuery("//ns:extension[contains(@rowType, '{$this->metaData['occurrenceFile']}')]");
            $this->occurrenceFile = $occurrenceQuery->nodeValue;
            //$this->occurrenceIdColName = $xpath->query("descendant::*[@index='0']", $occurrenceQuery)->item(0)->nodeName;
        }

        return;
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
        // TODO Use meta.xml fieldsTerminatedBy to determine tab or comma
        $result = array();
        $handle = fopen($filePath, "r");
        if ($handle) {
            $header = null;
            while (($row = fgetcsv($handle, 10000, "\t")) !== FALSE) {
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
    public function buildSubjectsArray ($multimedia, $occurrence, $projectId, $metaId)
    {
        // create new array with occurrence id as key
        $occurrenceInstance = array();
        foreach ($occurrence as $key => $row) {
            $occurrenceInstance[$row[$this->occurrenceHeader[0]]] = $row;
            unset($occurrence[$key]);
        }

        if ($this->mediaIsCore)
        {
            $subjects = array();
            foreach ($multimedia as $key => $subject) {
                $subjects[$key] = array_merge(
                    array(
                        'project_id' => (int)$projectId,
                        'meta_id' => (int)$metaId,
                        'occurrence' => $occurrenceInstance[$subject[$this->multiMediaHeader[0]]]
                    ),
                    $subject);
            }
        }
        else
        {
            $subjects = array();
            foreach ($multimedia as $key => $subject) {
                $occurrenceId = $subject[$this->multiMediaHeader[0]];
                $subject['id'] = $subject[$this->multiMediaHeader[$this->multiMediaIdentifierColIndex]];
                unset($subject[$this->multiMediaHeader[$this->multiMediaIdentifierColIndex]]);

                $subjects[$key] = array_merge(
                    array('project_id' => (int)$projectId),
                    array('meta_id' => (int)$metaId),
                    $subject,
                    array('occurrence' => $occurrenceInstance[$occurrenceId])
                );
            }
        }

        return $subjects;
    }

    /**
     * Insert docs
     *
     * @param $subjects
     */
    public function insertDocs ($subjects)
    {
        foreach ($subjects as $subject) {
            if (!$this->validateDoc($subject))
            {
                $this->duplicateArray[] = array($subject['id']);
                continue;
            }

            $this->subjectdoc->create($subject);
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
        $rules = array('project_id' => 'unique_with:subjectsdocs,id');
        $values = array('project_id' => $subject['project_id'], 'id' => $subject[$this->multiMediaHeader[0]]);

        $validator = Validator::make($values, $rules);
        $validator->getPresenceVerifier()->setConnection('mongodb');

        return $validator->fails() ? false : true;
    }

    /**
     * Save Meta file and header information
     * @param $xml
     * @param $projectId
     * @return mixed
     */
    public function saveMeta($xml, $projectId)
    {
        $meta = $this->meta->create(array('project_id' => $projectId, 'xml' => $xml, 'header' => json_encode($this->multiMediaHeader)));

        return $meta;
    }

    /**
     * Return meta file data
     *
     * @param $id
     * @return mixed
     */
    public function getMeta($id)
    {
        return $this->meta->find($id);
    }

    /**
     * Return multimedia file
     * @return mixed
     */
    public function getMultiMediaFile()
    {
        return $this->multiMediaFile;
    }

    /**
     * Return occurrence file
     * @return mixed
     */
    public function getOccurrenceFile()
    {
        return $this->occurrenceFile;
    }
}