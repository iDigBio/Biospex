<?php namespace Biospex\Services\WorkFlow;
/**
 * NotesFromNature.php
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

class NotesFromNature extends WorkFlow
{
    /**
     * @var array
     */
    protected $states = array();

    protected $defaultState;

    /**
     * Current expedition being processed
     *
     * @var
     */
    protected $record;

    /**
     * User Id of owner used in email reports
     *
     * @var
     */
    protected $userId;

    /**
     * Data Directory
     *
     * @var string
     */
    protected $dataDir;

    /**
     * Full path to temp file director
     * @var
     */
    protected $tmpFileDir;

    /**
     * Meta xml from meta file
     * @var
     */
    protected $metaXml = null;

    /**
     * Image types from Config
     *
     * @var
     */
    protected $imgTypes;

    /**
     * CSV header array associated with meta file
     * @var array
     */
    protected $metaHeader = array();

    /**
     * Remote image column from csv import
     * @var
     */
    protected $remoteImgColumn = null;

    /**
     * Hold original image url
     * @var array
     */
    protected $originalImgUrl = array();

    /**
     * Stores missing urls
     * @var array
     */
    protected $missingImgUrl = array();

    /**
     * Missing image when retrieving via curl
     *
     * @var array
     */
    protected $missingImg = array();

    /**
     * Data array for images
     * @var array
     */
    protected $data = array();

    /**
     * Metadata array for images
     * @var array
     */
    protected $metadata = array();

    /**
     * Array to hold subjects and identifiers
     *
     * @var
     */
    protected $subjectArray;

    /**
     * Large image width for NfN
     *
     * @var int
     */
    private $largeWidth = 1540;

    /**
     * Small image width for NfN
     *
     * @var int
     */
    private $smallWidth  = 580;

    /**
     * Set state variable
     *
     * @return array
     */
    protected function prepareStates()
    {
        $this->states = array(
            'export',
            'getStatus',
            'getResults',
            'completed',
            'analyze',
        );

        return;
    }

    /**
     * Set any configuration options
     */
    protected function setConfig()
    {
        $this->imgTypes = array(
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/tiff' => '.tiff',
        );

        return;
    }

    /**
     * Process current state
     *
     * @param $id
     */
    public function process($id)
    {
        $this->record = $this->expedition->findWith($id, ['project', 'subject.subjectDoc']);

        if (empty($this->record))
        {
            $this->report->addError(trans('errors.error_process', array('id' => $id)));
            $this->report->reportSimpleError();

            return;
        }

        $this->userId = $this->record->project->user_id;

        if ( ! call_user_func(array($this, $this->states[$this->record->state])))
        {
            $this->destroyDir($this->tmpFileDir);
            return;
        }

        $this->record->state = $this->record->state+1;
        $this->expedition->save($this->record);

        $this->report->processComplete($this->record);

        return;
    }

    /**
     * Get results
     */
    public function getResults()
    {
        return;
    }

    public function getStatus()
    {
        return;
    }

    /**
     * Export the expedition
     *
     * @return string
     */
    public function export()
    {
        $title = bin2hex($this->record->id . preg_replace('/[^a-zA-Z0-9]/', '', $this->record->title));
        dd($title);
        $this->tmpFileDir = "{$this->dataDir}/$title";

        if ( ! $this->createDir($this->tmpFileDir))
            return false;

        if ( ! $this->buildImgDir())
            return false;

        if ( ! $this->processImages())
            return false;

        if ( ! $this->saveFile("{$this->tmpFileDir}/details.js", json_encode($this->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)))
            return false;

        $this->executeCommand("tar -czf {$this->dataDir}/$title.tar.gz -C {$this->dataDir} $title");

        if ( ! empty($this->missingImgUrl) || ! empty($this->missingImg))
            $this->report->missingImages($this->record, $this->missingImgUrl, $this->missingImg);

        $this->createDownload($this->record->id, "$title.tar.gz");

        $this->destroyDir($this->tmpFileDir);

        return;
    }

    /**
     * Process expedition for export
     */
    protected function buildImgDir()
    {
        $i = 0;
        foreach ($this->record->subject as $subject)
        {
            $this->subjectArray[$subject->id] = $subject->object_id;

            if ( ! $remoteImgColumn = $this->getRemoteImgColumn($subject->subjectDoc->meta_id))
                continue;

            if (empty($subject->subjectDoc->{$remoteImgColumn}))
            {
                $this->missingImgUrl[] = array($subject->object_id);
                continue;
            }

            list($image, $ext) = $this->getImage($subject->subjectDoc->bestQualityAccessURI);

            if ( ! $image)
            {
                $this->missingImg[] = array($subject->subjectDoc->bestQualityAccessURI);
                continue;
            }

            $this->originalImgUrl[$subject->id.$ext] = $subject->subjectDoc->bestQualityAccessURI;
            $path = "{$this->tmpFileDir}/{$subject->id}{$ext}";

            if ( ! $this->saveFile($path, $image))
                continue;

            $i++;
        }

        return $i == 0 ? false : true;
    }

    /**
     * Retrieve column name for remote image supplied in meta file during import.
     *
     * @param $metaId
     * @return null
     */
    protected function getRemoteImgColumn($metaId)
    {
        if (!is_null($this->remoteImgColumn))
            return $this->remoteImgColumn;

        if ( ! $this->setMetaData($metaId))
            return false;

        if ( ! $this->subject->loadDom($this->metaXml, true))
        {
            $this->report->addError(trans('error_load_dom', array('id' => $metaId)));
            $this->report->reportSimpleError();

            return false;
        }

        if ( ! $node = $this->subject->getXpathQuery("//ns:field[contains(@term, '{$this->metaFile['remoteImgUrl']}')]"))
        {
            $this->report->addError(trans('error_xpath', array('id' => $metaId)));
            $this->report->reportSimpleError();

            return false;
        }
        $index = $node->attributes->getNamedItem("index")->nodeValue;

        $this->remoteImgColumn = $this->metaHeader[$index];

        return $this->remoteImgColumn;
    }

    /**
     * Set xml and header from meta file
     *
     * @param $metaId
     * @return bool
     */
    protected function setMetaData($metaId)
    {
        if (!is_null($this->metaXml))
            return true;

        if ( ! $meta = $this->subject->getMeta($metaId))
        {
            $this->report->addError(trans('error_xml_meta', array('id' => $metaId)));
            $this->report->reportSimpleError();

            return false;
        }

        $this->metaXml = $meta->xml;
        $this->metaHeader = json_decode($meta->header);

        return true;
    }

    /**
     * Retrieve image from url
     *
     * @param $url
     * @return array
     */
    protected function getImage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $image = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return array($image, $this->imgTypes[$contentType]);
    }

    /**
     * Process images for NfN for an expedition
     */
    protected function processImages()
    {
        $data = array();

        $it = new \RecursiveDirectoryIterator($this->tmpFileDir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);

        $lrgPath = "{$this->tmpFileDir}/large";
        if ( ! $this->createDir($lrgPath))
            return false;

        $smPath = "{$this->tmpFileDir}/small";
        if ( ! $this->createDir($smPath))
            return false;

        $this->metadata['sourceDir'] = $this->tmpFileDir;
        $this->metadata['targetDir'] = $this->tmpFileDir;
        $this->metadata['created_at'] = date('l jS F Y', time());
        $this->metadata['highResDir'] = $lrgPath;
        $this->metadata['lowResDir'] = $smPath;
        $this->metadata['highResWidth'] = $this->largeWidth;
        $this->metadata['lowResWidth'] = $this->smallWidth;

        $i = 0;
        foreach($files as $file) {
            $filePath = $file->getRealPath();

            if ( ! $filePath)
            {
                $this->report->addError(trans('error_process_file_path', array('file' => $file)));
                $this->report->reportSimpleError();
                continue;
            }

            list($width, $height, $type, $attr) = getimagesize($filePath); // $width, $height, $type, $attr
            $info = pathinfo($filePath); // $dirname, $basename, $extension, $filename

            $data['identifier'] = $this->subjectArray[$info['filename']];
            $data['original']['path'] = array($info['filename'], ".{$info['extension']}");
            $data['original']['name'] = $info['basename'];
            $data['original']['width'] = $width;
            $data['original']['height'] = $height;

            $lrgHeight = round(($height * $this->largeWidth) / $width);
            $lrgName = "{$info['filename']}.large.png";
            $lrgImgPath = "$lrgPath/$lrgName";
            $data['large']['name'] = "large/$lrgName";
            $data['large']['width'] = $this->largeWidth;
            $data['large']['height'] = $lrgHeight;

            $this->convertImage($file->getRealPath(), $this->largeWidth, $lrgHeight, $lrgImgPath);


            $smallHeight = round(($height * $this->smallWidth) / $width);
            $smName = "{$info['filename']}.small.png";
            $smImgPath = "$smPath/$smName";
            $data['small']['name'] = "small/{$info['filename']}.small.png";
            $data['small']['width'] = $this->smallWidth;
            $data['small']['height'] = $smallHeight;

            $this->convertImage($file->getRealPath(), $this->smallWidth, $smallHeight, $smImgPath);

            $this->metadata['images'][] = $data;

            $i++;
        }
        $this->metadata['total'] = $i * 3;

        return true;
    }

    /**
     * Convert image and resize.
     *
     * @param $file
     * @param $width
     * @param $height
     * @param $newImgPath
     */
    protected function convertImage($file, $width, $height, $newImgPath)
    {
        $this->executeCommand("/usr/bin/convert $file -colorspace RGB -resize {$width}x{$height} $newImgPath");

        return;
    }
}