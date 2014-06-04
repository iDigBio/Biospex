<?php namespace Biospex\Services\SubjectsImport;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SubjectsImport {

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
     * Meta xml search criteria from configuration
     * @var
     */
    protected $metaData;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->metaData = Config::get('config.metaData');
    }

    /**
     * Set Multimedia and Occurrence files from meta.xml
     * Since we do not always know which is the core and which is extension.
     */
    public function setFiles ($metaFilePath)
    {
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->load($metaFilePath);

        $xpath = new \DOMXpath($dom);
        $xpath->registerNamespace('ns', $dom->documentElement->namespaceURI);

        $coreType = $dom->getElementsByTagName('core')->item(0)->getAttribute('rowType');
        $coreFile = $dom->getElementsByTagName('core')->item(0)->nodeValue;

        if (preg_match('/occurrence/i', $coreType))
        {
            $this->mediaIsCore = false;
            $this->occurrenceFile = $coreFile;

            $multiMediaQuery = $xpath->query("//ns:extension[contains(@rowType, {$this->metaData['multimedia']})]")->item(0);
            $this->multiMediaFile = $multiMediaQuery->nodeValue;
            $this->multiMediaIdentifierColIndex = $xpath->query("//ns:field[contains(@term, {$this->metaData['identifier']})]")->item(0)->attributes->getNamedItem("index")->nodeValue;
            //$this->multiMediaIdentifierColIndex = $xpath->query("//ns:field[contains(@term, 'identifier')]")->item(0)->nodeName;
        }
        elseif (preg_match('/multimedia/', $coreType))
        {
            $this->mediaIsCore = true;
            $this->multiMediaFile = $coreFile;

            $occurrenceQuery = $xpath->query("//ns:extension[contains(@rowType, {$this->metaData['occurrence']})]")->item(0);
            $this->occurrenceFile = $occurrenceQuery->nodeValue;
            //$this->occurrenceIdColName = $xpath->query("descendant::*[@index='0']", $occurrenceQuery)->item(0)->nodeName;
        }

        return $dom->saveXML();
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
                        'project_id' => $projectId,
                        'meta_id' => $metaId,
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
                    array('project_id' => $projectId),
                    array('meta_id' => $metaId),
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
                continue;

            DB::connection('mongodb')->collection('subjectsdocs')->insert($subject);
        }
    }

    /**
     * Delete all subjectDocs
     */
    public function deleteDocs()
    {
        DB::connection('mongodb')->collection('subjectsdocs')->delete();
    }

    /**
     * Validate if subject exists using project_id and id
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

    public function getMultiMediaFile()
    {
        return $this->multiMediaFile;
    }

    public function getOccurrenceFile()
    {
        return $this->occurrenceFile;
    }
}